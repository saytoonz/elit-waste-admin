<?php

namespace Tests\Feature;

use App\Jobs\SendSmsJob;
use App\Models\Customer;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmsLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.mycsms.api_key' => 'test-key']);
    }

    public function test_accepted_sms_is_logged_as_sent_with_gateway_details(): void
    {
        // Shape from the MyCSMS v3 docs (Send SMS sample response)
        Http::fake(['*' => Http::response([
            'status' => 'success', 'code' => 200, 'message' => 'All messages processed successfully',
            'data' => [
                'results'   => [['phone' => '233240000001', 'message_id' => 'abc123', 'status' => 'processed', 'scheduled_at' => null]],
                'cost_info' => ['per_message' => 0.05, 'total_cost' => 0.05, 'balance_before' => 10.05, 'balance_after' => 10.0],
            ],
        ], 200)]);
        $customer = Customer::factory()->create();

        SendSmsJob::send('0240000001', 'Payment Received: GHS 50.00', 'payment', $customer->id);

        $log = SmsMessage::sole();
        $this->assertSame('Sent', $log->status);
        $this->assertSame('payment', $log->type);
        $this->assertSame($customer->id, $log->customer_id);
        $this->assertSame(200, $log->gateway_status);
        $this->assertSame('abc123', $log->gateway_message_id);
        $this->assertSame('processed', $log->gateway_state);
        $this->assertEquals(0.05, (float) $log->gateway_cost);
        $this->assertSame(10.0, app(\App\Services\MyCSMSService::class)->balance()); // cached from balance_after
        $this->assertNotNull($log->sent_at);
        $this->assertSame(1, $log->attempts);
    }

    public function test_http_200_with_error_body_is_logged_as_failed(): void
    {
        Http::fake(['*' => Http::response(['status' => 'error', 'message' => 'Sender ID not approved'], 200)]);

        SendSmsJob::send('0240000001', 'Hello', 'invoice');

        $log = SmsMessage::sole();
        $this->assertSame('Failed', $log->status);
        $this->assertStringContainsString('Sender ID not approved', $log->error);
        $this->assertNull($log->sent_at);
    }

    public function test_jobs_queued_before_logging_still_get_a_log_row(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success'], 200)]);

        SendSmsJob::dispatch('0240000001', 'Legacy job');

        $this->assertSame('Sent', SmsMessage::sole()->status);
    }

    public function test_admin_can_view_log_and_resend_failed_sms(): void
    {
        Role::findOrCreate('Owner');
        $owner = tap(User::factory()->create())->assignRole('Owner');

        $failed = SmsMessage::create(['phone' => '0240000009', 'message' => 'Your invoice', 'type' => 'invoice', 'status' => 'Failed', 'error' => 'Gateway rejected']);
        SmsMessage::create(['phone' => '0240000008', 'message' => 'Thanks for paying', 'type' => 'payment', 'status' => 'Sent']);

        $this->actingAs($owner)->get(route('sms_logs.index'))
            ->assertOk()->assertSee('0240000009')->assertSee('Thanks for paying')->assertSee('Gateway rejected');

        $this->actingAs($owner)->get(route('sms_logs.index', ['status' => 'Failed']))
            ->assertOk()->assertSee('0240000009')->assertDontSee('Thanks for paying');

        Http::fake(['*' => Http::response(['status' => 'success'], 200)]);
        $this->actingAs($owner)->post(route('sms_logs.retry', $failed))->assertRedirect();

        $this->assertSame(1, SmsMessage::where('phone', '0240000009')->where('status', 'Sent')->count());
    }

    public function test_rejected_recipient_is_failed_even_on_http_200(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success', 'data' => ['results' => [['phone' => '233240000001', 'message_id' => 'x1', 'status' => 'rejected']]]], 200)]);

        SendSmsJob::send('0240000001', 'Hello', 'invoice');

        $log = SmsMessage::sole();
        $this->assertSame('Failed', $log->status);
        $this->assertSame('x1', $log->gateway_message_id);
    }
}
