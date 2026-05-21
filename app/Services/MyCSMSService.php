<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MyCSMS v3 client.
 * Docs reference: POST https://app.mycsms.com/api/v3/sms/send
 * Auth: Bearer <apiKey> in Authorization header.
 * Body: { phone: [...], sender_id, message, message_type }
 */
class MyCSMSService
{
    protected ?string $apiKey;
    protected string $senderId;
    protected string $baseUrl;
    protected string $messageType;

    public function __construct()
    {
        $this->apiKey      = config('services.mycsms.api_key');
        $this->senderId    = config('services.mycsms.sender_id', 'EliteWaste');
        $this->baseUrl     = rtrim(config('services.mycsms.url', 'https://app.mycsms.com/api/v3/sms/send'), '/');
        $this->messageType = config('services.mycsms.message_type', 'text');
    }

    /**
     * Send an SMS message.
     *
     * @param string|array $phone Recipient phone number(s). Accepts a single string or an array.
     * @param string $message The message body
     * @return bool True on HTTP 2xx, false otherwise
     */
    public function send(string|array $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('MyCSMS: API Key is missing. Message not sent.');
            return false;
        }

        // Normalize phone numbers to an array of strings, stripped of whitespace
        $phones = is_array($phone) ? $phone : [$phone];
        $phones = array_values(array_filter(array_map(
            fn($p) => preg_replace('/\s+/', '', (string) $p),
            $phones
        )));

        if (empty($phones)) {
            Log::warning('MyCSMS: No valid phone numbers provided.');
            return false;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($this->baseUrl, [
                    'phone'        => $phones,
                    'sender_id'    => $this->senderId,
                    'message'      => $message,
                    'message_type' => $this->messageType,
                ]);

            if ($response->successful()) {
                Log::info('MyCSMS: SMS sent to ' . implode(', ', $phones) . ' — ' . $response->body());
                return true;
            }

            Log::error('MyCSMS: HTTP ' . $response->status() . ' for ' . implode(', ', $phones) . ' — ' . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error('MyCSMS: Exception while sending to ' . implode(', ', $phones) . ' — ' . $e->getMessage());
            return false;
        }
    }
}
