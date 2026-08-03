<?php
// app/Repositories/Booking/BookingGuestRepository.php

namespace App\Repositories\Booking;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\VehicleAvailability;
use App\Repositories\Booking\Interfaces\BookingGuestRepositoryInterface;
use App\Services\Coupon\CouponGuestService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingGuestRepository implements BookingGuestRepositoryInterface
{
    private const PLATFORM_FEE_PERCENTAGE = 10;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CALCULATE PRICE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function calculatePrice(array $data): array
    {
        $vehicle   = Vehicle::findOrFail($data['vehicle_id']);
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate   = \Carbon\Carbon::parse($data['end_date']);
        $totalDays = $startDate->diffInDays($endDate);

        // ── السعر الحالي (custom أو base) ────────────────
        $pricePerDay = $this->getCurrentPrice($vehicle, $startDate, $endDate);
        $subtotal    = round($pricePerDay * $totalDays, 2);

        // ── التوصيل ───────────────────────────────────────
        $deliveryFee = ($data['delivery_type'] === 'delivery')
            ? (float) ($vehicle->delivery_fee ?? 0)
            : 0;

        // ── الكوبون ───────────────────────────────────────
        $discountAmount = 0;
        $couponData     = null;

        if (!empty($data['coupon_code'])) {
            $couponResult = app(CouponGuestService::class)->validate([
                'code'       => $data['coupon_code'],
                'subtotal'   => $subtotal,
                'total_days' => $totalDays,
            ]);

            if ($couponResult['code'] === 200) {
                $discountAmount = $couponResult['data']->resource->calculateDiscount($subtotal);
                $couponData     = [
                    'code'            => $data['coupon_code'],
                    'discount_amount' => $discountAmount,
                ];
            }
            else
            {
                return [
                    'data'    => [],
                    'code'    => $couponResult['code'],
                    'message' => $couponResult['message'],
                ];
            }
        }

        // ── رسوم المنصة ───────────────────────────────────
        $platformFee = round(($subtotal - $discountAmount) * (self::PLATFORM_FEE_PERCENTAGE / 100), 2);
        $totalAmount = round($subtotal - $discountAmount + $deliveryFee + $platformFee, 2);

        return [
            'vehicle_id'         => $vehicle->id,
            'total_days'         => $totalDays,
            'price_per_day'      => $pricePerDay,
            'subtotal'           => $subtotal,
            'discount_amount'    => $discountAmount,
            'delivery_fee'       => $deliveryFee,
            'platform_fee'       => $platformFee,
            'total_amount'       => $totalAmount,
            'coupon'             => $couponData,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CREATE BOOKING
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {

            $vehicle = Vehicle::where('listing_status', 'listed')
                ->where('admin_review_status', 'approved')
                ->lockForUpdate()
                ->findOrFail($data['vehicle_id']);

            // ── تحقق من التوفر ────────────────────────────
            $this->ensureAvailability($vehicle->id, $data['start_date'], $data['end_date']);

            // ── إنشاء الحجز ───────────────────────────────
            $booking = Booking::create([
                'vehicle_id'         => $vehicle->id,
                'host_id'            => $vehicle->host_id,
                'user_id'            => auth()->id(),
                'start_date'         => $data['start_date'],
                'end_date'           => $data['end_date'],
                'total_days'         => $data['total_days'],
                'base_price_per_day' => $data['price_per_day'],
                'subtotal'           => $data['subtotal'],
                'discount_amount'    => $data['discount_amount'],
                'delivery_fee'       => $data['delivery_fee'],
                'platform_fee'       => $data['platform_fee'],
                'total_amount'       => $data['total_amount'],
                'delivery_type'      => $vehicle['delivery_available'] ? 'delivery' : 'pickup',
                'delivery_address'   => $vehicle['pickup_address'] ?? null,
                'delivery_lat'       => $vehicle['pickup_lat']     ?? null,
                'delivery_lng'       => $vehicle['pickup_lng']     ?? null,
                'status'             => 'pending',
            ]);

            
            return $booking;
        });
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CANCEL BOOKING
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function cancelBooking(int $bookingId, int $userId, string $reason): array
    {
        return DB::transaction(function () use ($bookingId, $userId, $reason) {

            $booking = Booking::where('user_id', $userId)
                ->lockForUpdate()
                ->findOrFail($bookingId);

            if ($booking->status !== 'pending') {
                return ['status' => 'not_cancellable'];
            }

            $booking->update([
                'status'              => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by'        => 'guest',
            ]);

            // ── حذف booking_block من availabilities ───────
            VehicleAvailability::where('vehicle_id', $booking->vehicle_id)
                ->where('type', 'booking_block')
                ->where('available_from', $booking->start_date)
                ->where('available_to', $booking->end_date)
                ->delete();

            return [
                'status'  => 'cancelled',
                'booking' => $booking->fresh(['vehicle', 'host.user', 'payment']),
            ];
        });
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(int $userId, ?string $status, int $perPage): LengthAwarePaginator
    {
        return Booking::with(['vehicle.primaryImage', 'host.user', 'payment'])
            ->where('user_id', $userId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // SHOW
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function show(int $bookingId, int $userId): Booking
    {
        return Booking::with([
            'vehicle.primaryImage',
            'host.user',
            'payment',
            'couponUse.coupon',
        ])
        ->where('user_id', $userId)
        ->findOrFail($bookingId);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // Helpers
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private function getCurrentPrice(Vehicle $vehicle, $startDate, $endDate): float
    {
        $customPricing = $vehicle->customPricings()
            ->where('date_from', '<=', $startDate)
            ->where('date_to', '>=', $endDate)
            ->first();

        return $customPricing
            ? (float) $customPricing->price_per_day
            : (float) $vehicle->base_price_per_day;
    }

    private function ensureAvailability(int $vehicleId, string $from, string $to): void
    {
        // Check normal availability
        $isAvailable = VehicleAvailability::where('vehicle_id', $vehicleId)
            ->where('type', 'available')
            ->where('is_blocked', false)
            ->where('available_from', '<=', $from)
            ->where('available_to', '>=', $to)
            ->exists();

        if (!$isAvailable) {
            throw new \Exception('Vehicle is not available for the selected dates.');
        }


        // Check host blocked periods (snoozed)
        $isSnoozed = VehicleAvailability::where('vehicle_id', $vehicleId)
            ->where('type', 'snoozed')
            ->where('available_from', '<=', $to)
            ->where('available_to', '>=', $from)
            ->exists();

        if ($isSnoozed) {
            throw new \Exception('Vehicle is temporarily unavailable by the host for the selected dates.');
        }


        // Check existing bookings
        $period = $this->findNextAvailablePeriod(
            $vehicleId,
            $from,
            $to
        );


        $message =
            "This vehicle is unavailable from "
            . \Carbon\Carbon::parse($from)->format('M d')
            . " to "
            . \Carbon\Carbon::parse($to)->format('M d')
            . " because it is already booked.";


        if ($period) {

            if ($period['to']) {

                $message .= " The closest available period is "
                    . $period['from']
                    . " to "
                    . $period['to']
                    . " ({$period['days']} days available).";

            } else {

                $message .= " The vehicle may be available from "
                    . $period['from']
                    . " onwards.";

            }
            throw new \Exception($message);
        }




    }

    private function findNextAvailablePeriod(
        int $vehicleId,
        string $from,
        string $to
    ): ?array {

        $requestedDays = \Carbon\Carbon::parse($from)
            ->diffInDays(\Carbon\Carbon::parse($to));


        $bookings = VehicleAvailability::where('vehicle_id', $vehicleId)
            ->where('type', 'booking_block')
            ->where('available_to', '>=', now())
            ->orderBy('available_from')
            ->get();


        if ($bookings->isEmpty()) {
            return null;
        }


        for ($i = 0; $i < $bookings->count() - 1; $i++) {

            $currentEnd = \Carbon\Carbon::parse(
                $bookings[$i]->available_to
            );

            $nextStart = \Carbon\Carbon::parse(
                $bookings[$i + 1]->available_from
            );


            $gapDays = $currentEnd->diffInDays($nextStart);


            if ($gapDays >= $requestedDays) {

                return [
                    'from' => $currentEnd->addDay()->format('M d'),
                    'to'   => $nextStart->subDay()->format('M d'),
                    'days' => $gapDays,
                ];
            }
        }


        // Last booking -> open future availability
        $lastBooking = $bookings->last();

        return [
            'from' => \Carbon\Carbon::parse(
                $lastBooking->available_to
            )
            ->addDay()
            ->format('M d'),

            'to' => null,

            'days' => null,
        ];
    }
}
