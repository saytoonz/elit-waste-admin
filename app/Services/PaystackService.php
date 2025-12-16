<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected $baseUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.paystack.co';
        $this->secretKey = config('services.paystack.secret');
    }

    /**
     * Initialize a transaction on Paystack
     *
     * @param string $email Customer email (or default one if none)
     * @param float $amount Amount in GHS
     * @param string $callbackUrl URL to redirect to after payment
     * @param array $metadata Extra data like invoice_id, customer_id
     */
    public function initializeTransaction($email, $amount, $callbackUrl, $metadata = [])
    {
        // Paystack expects amount in kobo (multiply by 100)
        $koboAmount = $amount * 100;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/transaction/initialize', [
                'email' => $email,
                'amount' => $koboAmount,
                'callback_url' => $callbackUrl,
                'metadata' => $metadata,
                'channels' => ['card', 'mobile_money'],
                'currency' => 'GHS'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paystack Init Error: ' . $response->body());
            return ['status' => false, 'message' => 'Failed to initialize payment'];
        } catch (\Exception $e) {
            Log::error('Paystack Connection Error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Connection error'];
        }
    }

    /**
     * Verify a transaction on Paystack
     *
     * @param string $reference Transaction reference
     */
    public function verifyTransaction($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->get($this->baseUrl . '/transaction/verify/' . $reference);

            if ($response->successful()) {
                return $response->json();
            }
            
            return ['status' => false, 'message' => 'Verification failed'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
