<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyCSMSService
{
    protected $apiKey;
    protected $senderId;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.mycsms.api_key');
        $this->senderId = config('services.mycsms.sender_id', 'EliteWaste');
        // Update default URL based on documentation
        $this->baseUrl = config('services.mycsms.url', 'https://apiv2.mycsms.com');
    }

    /**
     * Send an SMS message.
     *
     * @param string $phone The recipient phone number (international format preferred)
     * @param string $message The message content
     * @return bool True if queued/sent successfully, False otherwise
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('SMS Service: API Key is missing. Message not sent.');
            return false;
        }

        // Clean phone number
        $phone = preg_replace('/\s+/', '', $phone);

        try {
            // Documentation requires JSON payload with specific keys
            $payload = [
                'apiKey' => $this->apiKey,
                'phone' => [$phone], // API expects an array
                'sender' => $this->senderId,
                'message' => $message,
            ];

            // Http::post automatically sends as JSON with 'Content-Type: application/json'
            $response = Http::post($this->baseUrl, $payload);

            if ($response->successful()) {
                // Check inner response code if available in documentation example
                // Example response: {"result": [{"responseCode": 10000, "responseMessage": "Message Sent"}]}
                $result = $response->json();
                
                if (isset($result['result'][0]['responseCode']) && $result['result'][0]['responseCode'] == 10000) {
                     Log::info("SMS Sent to {$phone}: " . $response->body());
                     return true;
                } else {
                     Log::warning("SMS API Response Error: " . $response->body());
                     // If the API returns 200 but the inner result is not 10000, it's a soft failure.
                     // But strictly speaking, the request was "successful" (200 OK).
                     // We'll log it and return true if we just care about dispatching, 
                     // or false if we strictly need delivery confirmation. 
                     // Given it's a background job usually, we might not retry if it's a logic error (e.g. bad number).
                     return false; 
                }
            } else {
                Log::error("SMS Failed to {$phone}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("SMS Exception to {$phone}: " . $e->getMessage());
            return false;
        }
    }
}
