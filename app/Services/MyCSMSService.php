<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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
     * 'body' => ?string response body, 'error' => ?string reason when not accepted,
     * 'message_id' => ?string gateway message id, 'state' => ?string per-recipient
     * status (e.g. "processed"), 'cost' => ?float].
     */
    public array $lastResult = [];

    public const BALANCE_CACHE_KEY = 'mycsms.balance';

    /**
     * Gateway (MyCSMS wallet) balance, cached for a few minutes. Null when it
     * can't be fetched. This is the provider account balance, separate from the
     * app's SMS bundle credits; when it runs out, every SMS fails at the gateway.
     */
    public function balance(bool $fresh = false): ?float
    {
        if (!$fresh && Cache::has(self::BALANCE_CACHE_KEY)) {
            return Cache::get(self::BALANCE_CACHE_KEY);
        }
        if (empty($this->apiKey)) return null;
        // Recent failed lookup: don't make every page load wait on a down gateway.
        if (!$fresh && Cache::has(self::BALANCE_CACHE_KEY . '.failed')) return null;

        try {
            $url = preg_replace('#/send$#', '/balance', $this->baseUrl);
            $response = Http::withToken($this->apiKey)->acceptJson()->timeout(5)->get($url);
            $balance = $response->successful() ? $response->json('data.balance') : null;
            if (!is_numeric($balance)) {
                Cache::put(self::BALANCE_CACHE_KEY . '.failed', true, now()->addMinutes(2));
                return null;
            }

            Cache::put(self::BALANCE_CACHE_KEY, (float) $balance, now()->addMinutes(10));
            return (float) $balance;
        } catch (\Throwable $e) {
            Log::warning('MyCSMS: balance lookup failed — ' . $e->getMessage());
            Cache::put(self::BALANCE_CACHE_KEY . '.failed', true, now()->addMinutes(2));
            return null;
        }
    }

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
        $this->lastResult = ['status' => null, 'body' => null, 'error' => null, 'message_id' => null, 'state' => null, 'cost' => null];

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
                // Per-recipient result (single-recipient sends use the first entry).
                $result = $response->json('data.results.0') ?? [];
                $this->lastResult['message_id'] = $result['message_id'] ?? null;
                $this->lastResult['state']      = $result['status'] ?? null;
                $this->lastResult['cost']       = $response->json('data.cost_info.total_cost');
                if (is_numeric($balance = $response->json('data.cost_info.balance_after'))) {
                    Cache::put(self::BALANCE_CACHE_KEY, (float) $balance, now()->addMinutes(10));
                }

                $state = strtolower((string) ($result['status'] ?? ''));
                if (in_array($state, ['failed', 'fail', 'rejected', 'error', 'undelivered'], true)) {
                    Log::error('MyCSMS: recipient rejected ' . implode(', ', $phones) . ' — ' . $response->body());
                    $this->lastResult['error'] = 'Gateway did not accept the recipient (' . $state . ').';
                    return false;
                }

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
