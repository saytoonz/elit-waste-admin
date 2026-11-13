<?php

namespace Tests\Feature;

use App\Jobs\SendSmsJob;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Platform\SmsBundle;
use App\Models\Subscription;
use App\Support\BillingConfig;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SmsReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_days_are_day_one_due_date_and_every_third_overdue_day(): void
    {
        $billed = Carbon::parse('2026-11-01');
        $due    = Carbon::parse('2026-11-05');

        $days = collect(range(0, 14))
            ->filter(fn($d) => BillingConfig::isReminderDay($billed, $due, $billed->copy()->addDays($d)))
            ->values()->all();

        // day 1 (11-02), due date (day 4), then overdue by 3/6/9 (days 7, 10, 13)
        $this->assertSame([1, 4, 7, 10, 13], $days);
    }

    public function test_reminders_skip_off_days_and_send_one_sms_per_customer(): void
    {
        Queue::fake();
        BillingConfig::setSince('2026-01-01');
        $this->travelTo(Carbon::parse('2026-11-13 08:00'));

        $customer = Customer::factory()->create(['phone' => '0240000001']);
        $sub = Subscription::factory()->create(['customer_id' => $customer->id, 'billing_cycle' => 'Weekly']);

        // Billed yesterday (day 1 → reminder day) and 6 days ago, due 3 days ago (overdue by 3 → reminder day)
        $this->makeInvoice($customer, $sub, '2026-11-12', '2026-11-19', 50);
        $this->makeInvoice($customer, $sub, '2026-11-07', '2026-11-10', 50);

        // Another customer on an off-day only: billed 2 days ago, due in 3 days
        $quiet = Customer::factory()->create(['phone' => '0240000002']);
        $this->makeInvoice($quiet, Subscription::factory()->create(['customer_id' => $quiet->id]), '2026-11-11', '2026-11-16', 80);

        $this->artisan('billing:remind')->assertSuccessful();

        Queue::assertPushed(SendSmsJob::class, 1);
        Queue::assertPushed(SendSmsJob::class, fn($job) => $job->phone === '0240000001'
            && $job->profile === SendSmsJob::PROFILE_REMINDER
            && str_contains($job->message, 'GHS 100.00')
            && str_contains($job->message, '+ 1 more'));
    }

    public function test_reminders_cannot_spend_the_reserve_but_transactional_sms_can(): void
    {
        $serviceId = DB::table('platform_services')->insertGetId([
            'name' => 'SMS', 'slug' => 'sms', 'type' => 'SMS', 'unit_price' => 1, 'currency' => 'GHS',
        ]);
        $subscriptionId = DB::table('platform_subscriptions')->insertGetId([
            'platform_service_id' => $serviceId, 'unit_price' => 1, 'currency' => 'GHS',
            'start_date' => now()->toDateString(), 'next_billing_date' => now()->addMonth()->toDateString(),
        ]);

        $bundle = SmsBundle::create([
            'platform_subscription_id' => $subscriptionId,
            'quantity_total' => 100,
            'quantity_used'  => 79, // 21 left; reserve is 20
            'period_start'   => now()->subDay(),
            'period_end'     => now()->addMonth(),
            'status'         => 'Active',
        ]);

        $this->assertSame(20, $bundle->reminderReserve());
        $this->assertTrue($bundle->consume(1, $bundle->reminderReserve()));   // 20 left
        $this->assertFalse($bundle->consume(1, $bundle->reminderReserve()));  // would dip into reserve
        $this->assertTrue($bundle->consume(1));                               // transactional still sends
        $this->assertSame(81, $bundle->fresh()->quantity_used);
    }

    private function makeInvoice(Customer $customer, Subscription $sub, string $billedOn, string $due, float $amount): Invoice
    {
        $invoice = Invoice::factory()->create([
            'customer_id'     => $customer->id,
            'subscription_id' => $sub->id,
            'amount'          => $amount,
            'balance_due'     => $amount,
            'due_date'        => $due,
            'status'          => 'Pending',
        ]);
        $invoice->forceFill(['created_at' => Carbon::parse($billedOn . ' 00:05')])->save();

        return $invoice;
    }
}
