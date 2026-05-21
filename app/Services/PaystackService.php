<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    public const PROFILE_CUSTOMER = 'customer';
    public const PROFILE_PROVIDER = 'provider';

    protected string $baseUrl = 'https://api.paystack.co';
    protected ?string $secretKey;
    protected ?string $publicKey;
    protected string $profile;

    public function __construct(string $profile = self::PROFILE_CUSTOMER)
    {
        $this->profile = $profile;

        if ($profile === self::PROFILE_PROVIDER) {
            // Developer/platform-owner Paystack — env-only, never customer-editable
            $this->secretKey = config('services.paystack.provider_secret');
            $this->publicKey = config('services.paystack.provider_public');
        } else {
            // Customer Paystack — Settings table is source of truth, env is fallback
            $this->secretKey = Setting::safeValue('paystack_secret_key') ?: config('services.paystack.secret');
            $this->publicKey = Setting::safeValue('paystack_public_key') ?: config('services.paystack.publicKey');
        }
    }

    /**
     * Construct a service instance bound to the provider (developer) Paystack account.
     */
    public static function forProvider(): self
    {
        return new self(self::PROFILE_PROVIDER);
    }

    /**
     * Construct a service instance bound to the customer's Paystack account.
     */
    public static function forCustomer(): self
    {
        return new self(self::PROFILE_CUSTOMER);
    }

    public function profile(): string
    {
        return $this->profile;
    }

    public function isConfigured(): bool
    {
        return !empty($this->secretKey);
    }

    /**
     * Initialize a transaction on Paystack
     *
     * @param string $email Customer email (Paystack requires one)
     * @param float $amount Amount in major units (GHS, USD, etc.)
     * @param string $callbackUrl URL to redirect to after payment
     * @param array $metadata Extra data carried through to the callback
     * @param string $currency 3-letter currency code (default GHS)
     * @param array|null $channels Paystack channels; auto-defaults to card-only for non-GHS
     */
    public function initializeTransaction($email, $amount, $callbackUrl, $metadata = [], string $currency = 'GHS', ?array $channels = null)
    {
        if (!$this->isConfigured()) {
            Log::error('Paystack ' . $this->profile . ' profile has no secret key configured.');
            return ['status' => false, 'message' => 'Paystack is not configured for the ' . $this->profile . ' profile.'];
        }

        $subunitAmount = (int) round($amount * 100);
        $currency = strtoupper($currency);
        if ($channels === null) {
            $channels = $currency === 'GHS' ? ['card', 'mobile_money'] : ['card'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type'  => 'application/json',
            ])->post($this->baseUrl . '/transaction/initialize', [
                'email'        => $email,
                'amount'       => $subunitAmount,
                'callback_url' => $callbackUrl,
                'metadata'     => $metadata,
                'channels'     => $channels,
                'currency'     => $currency,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paystack Init Error [' . $this->profile . ']: ' . $response->body());

            // Surface the actual Paystack error so the user can act on it
            $body = $response->json();
            $msg = $body['message'] ?? 'Paystack rejected the request.';
            if (!empty($body['meta']['nextStep'])) {
                $msg .= ' (' . $body['meta']['nextStep'] . ')';
            }
            return ['status' => false, 'message' => $msg, 'paystack_error' => $body];
        } catch (\Exception $e) {
            Log::error('Paystack Connection Error [' . $this->profile . ']: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Connection error: ' . $e->getMessage()];
        }
    }

    /**
     * Verify a transaction on Paystack
     */
    public function verifyTransaction($reference)
    {
        if (!$this->isConfigured()) {
            return ['status' => false, 'message' => 'Paystack is not configured for the ' . $this->profile . ' profile.'];
        }

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
