<?php
// app/Services/Booking/BookingGuestService.php

namespace App\Services\Booking;

use App\Http\Resources\Booking\BookingGuestResource;
use App\Jobs\BroadcastNewBookingConfirmedJob;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\VehicleAvailability;
use App\Repositories\Booking\Interfaces\BookingGuestRepositoryInterface;
use App\Services\Coupon\CouponGuestService;
use App\Services\Notification\NotificationService;
use App\Services\Payment\PaymeraService;
use Illuminate\Support\Facades\DB;

class BookingGuestService
{
    public function __construct(
        protected BookingGuestRepositoryInterface $_bookingGuestRepository,
        protected PaymeraService                  $_paymeraService,
        protected CouponGuestService              $_couponGuestService,
        protected NotificationService             $_notificationService,
    ) {}

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CALCULATE PRICE
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function calculatePrice(array $data): array
    {
        $breakdown = $this->_bookingGuestRepository->calculatePrice($data);

        if (isset($breakdown['code']) && $breakdown['code'] !== 200) {
            return $breakdown;
        }

        return [
            'data'    => $breakdown,
            'message' => 'Price calculated successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CREATE BOOKING
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function createBooking(array $data): array
    {
        // ── 3. إنشاء الحجز ────────────────────────────────
        $booking = $this->_bookingGuestRepository->createBooking($data);

        // ── 4. إنشاء الدفع مع Paymera ────────────────────
        $paymentResult = $this->_paymeraService->createPayment(
            amount    : $data['total_amount'],
            bookingId : $booking->id,
        );

        if ($paymentResult['status'] === 'failed') {
            // ── فشل الدفع → إلغاء الحجز ──────────────────
            $booking->update(['status' => 'cancelled']);
            return [
                'data'    => [],
                'message' => 'Payment creation failed. Please try again.',
                'code'    => 500,
            ];
        }

        if ($booking->discount_amount > 0) {
                $couponUse = Coupon::query()->where('code', $data['coupon_code'])->first();
                if ($couponUse) {
                    $this->_couponGuestService->apply(
                        couponId  : $couponUse->id,
                        userId    : $booking->user_id,
                        bookingId : $booking->id,
                        subtotal  : (float) $booking->subtotal,
                    );
                }
            }

        // ── 5. حفظ بيانات الدفع ───────────────────────────
        Payment::create([
            'booking_id'       => $booking->id,
            'payment_id'       => $paymentResult['payment_id'],
            'amount'           => $data['total_amount'],
            'status'           => 'pending',
            'payment_url'      => $paymentResult['payment_url'],
            'gateway_response' => $paymentResult['response'],
        ]);

        return [
            'data' => [
                'booking_id'  => $booking->id,
                'payment_url' => $paymentResult['payment_url'],
                'total_amount'=> $data['total_amount'],
            ],
            'message' => 'Booking created. Please complete your payment.',
            'code'    => 201,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // HANDLE WEBHOOK (Paymera → triggerURL)
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function handleWebhook(array $payload): array
    {
        return DB::transaction(function () use ($payload) {

            $payment = Payment::where('booking_id', $payload['booking_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // ── تجنب المعالجة المزدوجة ────────────────────
            if ($payment->status === 'paid') {
                return ['status' => 'already_processed'];
            }

            $booking = $payment->booking;

            // ── تحديث الدفع ───────────────────────────────
            $payment->update([
                'status'           => 'paid',
                'paid_at'          => now(),
                'gateway_response' => $payload,
            ]);

            // ── تحديث الحجز ───────────────────────────────
            $booking->update(['status' => 'confirmed']);

            // ── إضافة booking_block في availabilities ─────
            VehicleAvailability::create([
                'vehicle_id'     => $booking->vehicle_id,
                'type'           => 'booking_block',
                'available_from' => $booking->start_date,
                'available_to'   => $booking->end_date,
                'is_blocked'     => true,
                'blocked_by'     => 'system',
                'block_reason'   => "booking_id:{$booking->id}",
            ]);



            // ── تحديث أرباح الهوست ────────────────────────
            $netAmount = $booking->total_amount - $booking->platform_fee;
            $booking->host->increment('total_earnings', $netAmount);
            $booking->host->increment('available_balance', $netAmount);
            $booking->host->increment('total_trips');

            // ── إشعار الغيست ──────────────────────────────
            $this->_notificationService->send(
                userId         : $booking->user_id,
                type           : 'booking_confirmed',
                title          : 'Booking Confirmed!',
                body           : "Your booking has been confirmed. Enjoy your trip!",

            );

            // ── إشعار الهوست ──────────────────────────────
            $this->_notificationService->send(
                userId         : $booking->host->user_id,
                type           : 'new_booking',
                title          : 'New Booking!',
                body           : "You have a new confirmed booking.",

            );

            // ── Broadcast للهوست لحظياً ───────────────────────────
            BroadcastNewBookingConfirmedJob::dispatch(
                hostUserId  : $booking->host->user_id,
                bookingId   : $booking->id,
                vehicleName : "{$booking->vehicle->make} {$booking->vehicle->model}",
                startDate   : $booking->start_date->toDateString(),
                endDate     : $booking->end_date->toDateString(),
                totalAmount : (float) $booking->total_amount,
            );

            return ['status' => 'success', 'booking' => $booking->fresh()];
        });
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CANCEL BOOKING
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function cancelBooking(int $bookingId): array
    {
        $result = $this->_bookingGuestRepository->cancelBooking(
            $bookingId,
            auth()->id(),
            'No reason provided'
        );

        if ($result['status'] === 'not_cancellable') {
            return [
                'data'    => [],
                'message' => 'Only pending bookings can be cancelled.',
                'code'    => 422,
            ];
        }

        $booking = $result['booking'];

        // ── إلغاء الدفع في Paymera ────────────────────────
        if ($booking->payment && $booking->payment->status === 'pending') {
            $this->_paymeraService->cancelPayment($booking->payment->payment_id);
            $booking->payment->update(['status' => 'cancelled']);
        }

        // ── إشعار الهوست ──────────────────────────────────
        $this->_notificationService->send(
            userId         : $booking->host->user_id,
            type           : 'booking_cancelled',
            title          : 'Booking Cancelled',
            body           : "A booking has been cancelled by the guest.",

        );

        return [
            'data'    => [],
            'message' => 'Booking cancelled successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // INDEX
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function index(array $filters): array
    {
        $result = $this->_bookingGuestRepository->index(
            userId  : auth()->id(),
            status  : $filters['status'] ?? null,
            perPage : $filters['per_page'] ?? 10,
        );

        return [
            'data'    => $result,
            'message' => 'Bookings retrieved successfully.',
            'code'    => 200,
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CURRENT BOOKING ← جديد
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getActiveBooking(): array
    {
        $booking = $this->_bookingGuestRepository->getActiveBooking(auth()->id());

        if (!$booking) {
            return [
                'data'    => null,
                'message' => 'No current booking right now.',
                'code'    => 200,
            ];
        }

        return [
            'data'    => new BookingGuestResource($booking),
            'message' => 'Current booking retrieved successfully.',
            'code'    => 200,
        ];
    }

    public function getConfirmedBooking(): array
    {
        $booking = $this->_bookingGuestRepository->getConfirmedBooking(auth()->id());

        if(!$booking) {
            return [
                'data'    => null,
                'message' => 'No confirmed booking right now.',
                'code'    => 200,
            ];
        }

        return [
            'data'    => new BookingGuestResource($booking),
            'message' => 'Confirmed bookings retrieved successfully.',
            'code'    => 200,
        ];
    }

}
