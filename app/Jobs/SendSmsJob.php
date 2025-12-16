<?php

namespace App\Jobs;

use App\Services\MyCSMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(MyCSMSService $smsService): void
    {
        Log::info("Processing SMS Job for {$this->phone}");
        
        $success = $smsService->send($this->phone, $this->message);

        if (!$success) {
            // Optionally release back to queue with delay if it's a transient issue
            // $this->release(10); 
            Log::warning("SMS Job failed for {$this->phone}");
        }
    }
}
