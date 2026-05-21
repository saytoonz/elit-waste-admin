<?php

namespace App\Services;

use App\Models\Platform\PendingUserProvision;
use App\Models\Platform\PlatformInvoice;
use App\Models\Platform\PlatformInvoiceItem;
use App\Models\Platform\PlatformPayment;
use App\Models\Platform\PlatformSubscription;
use App\Models\Platform\SmsBundle;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlatformBillingService
{
    /**
     * Generate a single-cycle invoice for a subscription (used by scheduled billing).
     */
    public function generateCycleInvoice(PlatformSubscription $subscription): PlatformInvoice
    {
        return $this->generateInvoice($subscription, 1, 'Cycle');
    }

    /**
     * Generate a prepay invoice covering N upcoming cycles. The next_billing_date
     * is only advanced when the invoice is paid.
     */
    public function generatePrepayInvoice(PlatformSubscription $subscription, int $cycles): PlatformInvoice
    {
        if ($cycles < 1) {
            throw new \InvalidArgumentException('Cycles must be at least 1');
        }
        return $this->generateInvoice($subscription, $cycles, 'Prepay');
    }

    protected function generateInvoice(PlatformSubscription $subscription, int $cycles, string $kind): PlatformInvoice
    {
        return DB::transaction(function () use ($subscription, $cycles, $kind) {
            $periodStart = Carbon::parse($subscription->next_billing_date)->toDateString();
            $tempStartForCalc = Carbon::parse($subscription->next_billing_date);
            $periodEnd = match ($subscription->billing_cycle) {
                'Monthly' => $tempStartForCalc->copy()->addMonths($cycles)->subDay()->toDateString(),
                'Quarterly' => $tempStartForCalc->copy()->addMonths(3 * $cycles)->subDay()->toDateString(),
                'Yearly' => $tempStartForCalc->copy()->addYears($cycles)->subDay()->toDateString(),
                default => $tempStartForCalc->copy()->addMonths($cycles)->subDay()->toDateString(),
            };

            $lineTotal = $subscription->unit_price * $subscription->quantity * $cycles;

            $invoice = PlatformInvoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'currency'       => $subscription->currency,
                'subtotal'       => $lineTotal,
                'tax'            => 0,
                'discount'       => 0,
                'total'          => $lineTotal,
                'amount_paid'    => 0,
                'status'         => 'Pending',
                'kind'           => $kind,
                'cycles_covered' => $cycles,
                'period_start'   => $periodStart,
                'period_end'     => $periodEnd,
                'issued_at'      => now()->toDateString(),
                'due_date'       => now()->addDays($subscription->grace_days)->toDateString(),
                'created_by'     => auth()->id(),
            ]);

            $serviceName = $subscription->service?->name ?? 'Platform Service';
            $description = $subscription->quantity > 1
                ? "{$serviceName} ({$subscription->quantity} × " . $subscription->cycleLabelFor($cycles) . ")"
                : "{$serviceName} ({$subscription->cycleLabelFor($cycles)})";

            PlatformInvoiceItem::create([
                'platform_invoice_id'      => $invoice->id,
                'platform_subscription_id' => $subscription->id,
                'platform_service_id'      => $subscription->platform_service_id,
                'description'              => $description,
                'quantity'                 => $subscription->quantity * $cycles,
                'unit_price'               => $subscription->unit_price,
                'line_total'               => $lineTotal,
            ]);

            return $invoice;
        });
    }

    /**
     * Run scheduled billing — generates cycle invoices for all due subscriptions.
     * Returns array of created invoice IDs.
     */
    public function runCycle(?Carbon $on = null): array
    {
        $on = $on ?: Carbon::today();
        $created = [];

        $due = PlatformSubscription::dueForBilling($on)->where('auto_renew', true)->get();

        foreach ($due as $sub) {
            // Catch-up loop: keep billing while still past due (handles multiple missed cycles)
            $safety = 0;
            while (Carbon::parse($sub->next_billing_date)->lte($on) && $safety < 60) {
                $invoice = $this->generateCycleInvoice($sub);
                $sub->advanceBillingDate(1);
                $sub->last_billed_date = $on->toDateString();
                $sub->save();
                $created[] = $invoice->id;
                $safety++;
            }
        }

        return $created;
    }

    /**
     * Mark overdue invoices and suspend subscriptions where applicable.
     */
    public function enforceOverdueAndSuspensions(?Carbon $on = null): array
    {
        $on = $on ?: Carbon::today();
        $stats = ['overdue' => 0, 'suspended' => 0];

        // Mark Pending invoices Overdue past due date
        PlatformInvoice::where('status', 'Pending')
            ->whereDate('due_date', '<', $on)
            ->each(function ($inv) use (&$stats) {
                $inv->update(['status' => 'Overdue']);
                $stats['overdue']++;
            });

        // Suspend subscriptions: force_payment + past grace + has unpaid invoice
        $candidates = PlatformSubscription::where('force_payment', true)
            ->whereIn('status', ['Active'])
            ->get();

        foreach ($candidates as $sub) {
            if ($sub->shouldBlockAccess()) {
                if ($sub->status !== 'Suspended') {
                    $sub->update([
                        'status'        => 'Suspended',
                        'suspended_at'  => now(),
                        'suspension_reason' => 'Overdue past grace period',
                    ]);
                    $stats['suspended']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Apply a successful payment to an invoice — updates totals, marks paid, advances subscription dates if prepay.
     */
    public function applyPayment(PlatformInvoice $invoice, float $amount, string $reference, string $channel = 'Paystack', array $metadata = []): PlatformPayment
    {
        return DB::transaction(function () use ($invoice, $amount, $reference, $channel, $metadata) {
            $payment = PlatformPayment::create([
                'platform_invoice_id' => $invoice->id,
                'reference'           => $reference,
                'amount'              => $amount,
                'currency'            => $invoice->currency,
                'status'              => 'Success',
                'channel'             => $channel,
                'paid_at'             => now(),
                'metadata'            => $metadata,
                'recorded_by'         => auth()->id(),
            ]);

            $invoice->amount_paid = (float) $invoice->amount_paid + $amount;
            if ($invoice->amount_paid >= (float) $invoice->total) {
                $invoice->status = 'Paid';
                $invoice->paid_at = now();
                $invoice->paystack_reference = $invoice->paystack_reference ?? $reference;

                // Advance subscription's billing date if this was a prepay invoice
                if ($invoice->kind === 'Prepay') {
                    $subIds = $invoice->items()->pluck('platform_subscription_id')->filter()->unique();
                    foreach ($subIds as $sid) {
                        $sub = PlatformSubscription::find($sid);
                        if ($sub) {
                            $sub->advanceBillingDate($invoice->cycles_covered);
                            $sub->save();
                        }
                    }
                }

                // Lift suspension if all unpaid invoices for the related subs are resolved
                $this->reactivateSubscriptionsIfClear($invoice);

                // Fulfill any pending user provisions tied to this invoice
                $this->fulfillPendingProvisions($invoice);

                // Fulfill SMS bundles for any SMS-type subscriptions on this invoice
                $this->fulfillSmsBundles($invoice);
            } else {
                $invoice->status = 'Partial';
            }
            $invoice->save();

            return $payment;
        });
    }

    /**
     * For each Pending provision row on this invoice, create the User and bump the
     * email subscription's quantity. Idempotent — already-provisioned rows are skipped.
     */
    protected function fulfillPendingProvisions(PlatformInvoice $invoice): void
    {
        $pendings = PendingUserProvision::where('platform_invoice_id', $invoice->id)
            ->where('status', 'Pending')
            ->get();

        foreach ($pendings as $pending) {
            try {
                // Race-safety: ensure email is still free at provisioning time
                if (User::where('email', $pending->email)->exists()) {
                    $pending->update(['status' => 'Failed', 'error_message' => 'Email already exists in users table at provisioning time.']);
                    AuditService::log('Email Provisioning Skipped', "{$pending->email} (already exists)");
                    continue;
                }

                $user = User::create([
                    'name'     => $pending->name,
                    'email'    => $pending->email,
                    'password' => Hash::make($pending->password), // decrypted via cast above
                ]);
                $user->assignRole($pending->role);

                if ($pending->subscription) {
                    $pending->subscription->increment('quantity');
                    $pending->subscription->update(['updated_by' => $pending->requested_by]);
                }

                $pending->update([
                    'status'              => 'Provisioned',
                    'provisioned_user_id' => $user->id,
                    'password'            => '__provisioned__', // wipe stored password
                ]);

                AuditService::log('Provisioned Email User', "{$user->email} as {$pending->role}");

                // Welcome SMS isn't applicable (no phone) but a future-proof hook can go here.
            } catch (\Throwable $e) {
                Log::error('Pending user provisioning failed: ' . $e->getMessage(), ['pending_id' => $pending->id]);
                $pending->update(['status' => 'Failed', 'error_message' => $e->getMessage()]);
            }
        }
    }

    protected function reactivateSubscriptionsIfClear(PlatformInvoice $paidInvoice): void
    {
        $subIds = $paidInvoice->items()->pluck('platform_subscription_id')->filter()->unique();
        foreach ($subIds as $sid) {
            $sub = PlatformSubscription::find($sid);
            if (!$sub || $sub->status !== 'Suspended') continue;

            $hasUnpaid = PlatformInvoice::whereHas('items', fn($q) => $q->where('platform_subscription_id', $sid))
                ->whereIn('status', ['Pending', 'Overdue', 'Partial'])
                ->exists();

            if (!$hasUnpaid) {
                $sub->update([
                    'status'            => 'Active',
                    'suspended_at'      => null,
                    'suspension_reason' => null,
                ]);
            }
        }
    }

    /**
     * For any SMS-type subscription on this paid invoice, create the SMS bundle
     * (quantity = subscription qty × service.sms_messages_per_unit × cycles_covered).
     * Idempotent — won't create a duplicate bundle for the same invoice+subscription.
     */
    protected function fulfillSmsBundles(PlatformInvoice $invoice): void
    {
        $invoice->load('items.subscription.service');

        foreach ($invoice->items as $item) {
            $sub = $item->subscription;
            if (!$sub) continue;
            $service = $sub->service;
            if (!$service || $service->type !== 'SMS') continue;

            // Idempotency
            $exists = SmsBundle::where('platform_invoice_id', $invoice->id)
                ->where('platform_subscription_id', $sub->id)
                ->exists();
            if ($exists) continue;

            $messagesPerUnit = (int) ($service->sms_messages_per_unit ?? 1000);
            if ($messagesPerUnit <= 0) continue;

            $total = $sub->quantity * $messagesPerUnit * max(1, (int) $invoice->cycles_covered);

            SmsBundle::create([
                'platform_subscription_id' => $sub->id,
                'platform_invoice_id'      => $invoice->id,
                'quantity_total'           => $total,
                'quantity_used'            => 0,
                'period_start'             => $invoice->period_start,
                'period_end'               => $invoice->period_end,
                'status'                   => 'Active',
                'notes'                    => "Fulfilled by invoice #{$invoice->invoice_number}",
            ]);

            AuditService::log('SMS Bundle Fulfilled', "{$total} messages valid {$invoice->period_start->format('Y-m-d')} → {$invoice->period_end->format('Y-m-d')}");
        }
    }

    protected function generateInvoiceNumber(): string
    {
        do {
            $n = 'PLT-' . date('Ym') . '-' . strtoupper(Str::random(5));
        } while (PlatformInvoice::where('invoice_number', $n)->exists());
        return $n;
    }

    /**
     * Convert an invoice amount to the currency the provider Paystack can charge in.
     *
     * Returns:
     *   - charge_currency   : the currency Paystack will actually charge (e.g. GHS)
     *   - charge_amount     : the amount in that charge currency
     *   - original_currency : the invoice's native currency (e.g. USD)
     *   - original_amount   : the invoice amount in its native currency
     *   - rate              : multiplier used (1.0 if no conversion)
     */
    public function convertForProviderCharge(float $amount, string $fromCurrency): array
    {
        $providerCurrency = strtoupper((string) config('platform.provider_charge_currency', 'GHS'));
        $fromCurrency = strtoupper($fromCurrency);

        $rate = 1.0;
        $chargeAmount = $amount;
        $chargeCurrency = $fromCurrency;

        if ($fromCurrency !== $providerCurrency) {
            if ($fromCurrency === 'USD' && $providerCurrency === 'GHS') {
                $rate = (float) config('platform.usd_to_ghs_rate', 15.5);
                $chargeAmount = round($amount * $rate, 2);
                $chargeCurrency = 'GHS';
            } else {
                throw new \RuntimeException("Conversion from {$fromCurrency} to {$providerCurrency} is not supported. Configure PLATFORM_USD_TO_GHS_RATE or change PLATFORM_PROVIDER_CHARGE_CURRENCY.");
            }
        }

        return [
            'charge_currency'   => $chargeCurrency,
            'charge_amount'     => $chargeAmount,
            'original_currency' => $fromCurrency,
            'original_amount'   => $amount,
            'rate'              => $rate,
        ];
    }
}
