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

    /**
     * Details of the most recent send(): ['status' => ?int HTTP status,
     * 'body' => ?string response body, 'error' => ?string reason when not accepted].
     */
    public array $lastResult = [];

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
        $this->lastResult = ['status' => null, 'body' => null, 'error' => null];

        if (empty($this->apiKey)) {
            Log::warning('MyCSMS: API Key is missing. Message not sent.');
            $this->lastResult['error'] = 'SMS API key is not configured.';
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
            $this->lastResult['error'] = 'No valid phone number.';
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

            $this->lastResult['status'] = $response->status();
            $this->lastResult['body']   = $response->body();

            // HTTP 2xx can still carry {"status":"error","message":"..."} (e.g. low
            // balance, unapproved sender ID) — that message was NOT sent.
            $bodyStatus = strtolower((string) ($response->json('status') ?? ''));
            if ($response->successful() && in_array($bodyStatus, ['error', 'failed', 'fail'], true)) {
                $reason = (string) ($response->json('message') ?? 'unknown reason');
                Log::error('MyCSMS: gateway error for ' . implode(', ', $phones) . ' — ' . $response->body());
                $this->lastResult['error'] = 'Gateway error: ' . $reason;
                return false;
            }

            if ($response->successful()) {
                Log::info('MyCSMS: SMS sent to ' . implode(', ', $phones) . ' — ' . $response->body());
                return true;
            }

            Log::error('MyCSMS: HTTP ' . $response->status() . ' for ' . implode(', ', $phones) . ' — ' . $response->body());
            $this->lastResult['error'] = 'Gateway rejected the message (HTTP ' . $response->status() . ').';
            return false;
        } catch (\Throwable $e) {
            Log::error('MyCSMS: Exception while sending to ' . implode(', ', $phones) . ' — ' . $e->getMessage());
            $this->lastResult['error'] = 'Could not reach the gateway: ' . $e->getMessage();
            return false;
        }
    }
}
