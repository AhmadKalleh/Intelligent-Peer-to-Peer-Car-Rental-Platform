<?php
// app/Services/Payment/PaymeraService.php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymeraService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $terminalId;

    public function __construct()
    {
        $this->baseUrl    = config('paymera.base_url');
        $this->username   = config('paymera.username');
        $this->password   = config('paymera.password');
        $this->terminalId = config('paymera.terminal_id');
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CREATE PAYMENT
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function createPayment(float $amount, int $bookingId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/api/create-payment", [
                    'lang'        => 'ar',
                    'terminalId'  => $this->terminalId,
                    'amount'      => $amount,
                    'callbackURL' => config('paymera.callback_url'),
                    'triggerURL' => config('paymera.trigger_url') . '?' . http_build_query([
                        'booking_id' => $bookingId
                    ]),
                ]);

            if ($response->successful()) {
                return [
                    'status'      => 'success',
                    'payment_id'  => $response->json('Data.paymentId'),
                    'payment_url' => $response->json('Data.url'),
                    'response'    => $response->json(),
                ];
            }

            Log::error('Paymera createPayment failed', [
                'booking_id' => $bookingId,
                'response'   => $response->json(),
            ]);

            return ['status' => 'failed', 'message' => 'Payment creation failed.'];

        } catch (\Throwable $e) {
            Log::error('Paymera createPayment exception', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CANCEL PAYMENT
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function cancelPayment(string $paymentId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/api/cancel-payment", [
                    'lang'       => 'ar',
                    'payment_id' => $paymentId,
                ]);

            if ($response->successful()) {
                return ['status' => 'success', 'response' => $response->json()];
            }

            return ['status' => 'failed', 'message' => 'Payment cancellation failed.'];

        } catch (\Throwable $e) {
            Log::error('Paymera cancelPayment exception', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // CHECK PAYMENT STATUS
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function checkPaymentStatus(string $paymentId): array
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->get("{$this->baseUrl}/api/get-payment-status/{$paymentId}");

            if ($response->successful()) {
                return [
                    'status'   => 'success',
                    'response' => $response->json(),
                ];
            }

            return ['status' => 'failed', 'message' => 'Could not retrieve payment status.'];

        } catch (\Throwable $e) {
            Log::error('Paymera checkStatus exception', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }
}
