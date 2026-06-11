<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsBroadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'filters',
        'recipients_count',
        'sent_count',
        'failed_count',
        'skipped_count',
        'credits_used',
        'credits_estimated',
        'status',
        'scheduled_at',
        'failure_reason',
        'created_by',
    ];

    protected $casts = [
        'filters'           => 'array',
        'recipients_count'  => 'integer',
        'sent_count'        => 'integer',
        'failed_count'      => 'integer',
        'skipped_count'     => 'integer',
        'credits_used'      => 'integer',
        'credits_estimated' => 'integer',
        'scheduled_at'      => 'datetime',
    ];

    /** Statuses: Draft | Scheduled | Processing | Completed | Failed */
    public function isEditable(): bool
    {
        return in_array($this->status, ['Draft', 'Scheduled', 'Failed'], true);
    }

    public function scopeDueForDispatch($query)
    {
        return $query->where('status', 'Scheduled')
                     ->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '<=', now());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProcessedCountAttribute(): int
    {
        return $this->sent_count + $this->failed_count + $this->skipped_count;
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->recipients_count <= 0) return 100;
        return round(($this->processed_count / $this->recipients_count) * 100, 1);
    }

    /**
     * Human summary of the audience filters, e.g. "Zones: Ahafo, Mim · Debtors only".
     */
    public function getAudienceSummaryAttribute(): string
    {
        $f = $this->filters ?? [];
        $parts = [];

        $parts[] = match ($f['audience'] ?? 'all') {
            'zones'     => 'Zones: ' . implode(', ', $f['zone_names'] ?? []),
            'customers' => 'Selected customers (' . count($f['customer_ids'] ?? []) . ')',
            default     => 'All customers',
        };

        if (!empty($f['types'])) $parts[] = implode(' & ', $f['types']);
        if (!empty($f['debtors_only'])) $parts[] = 'Debtors only';
        if (!empty($f['include_inactive'])) $parts[] = 'Incl. inactive';

        return implode(' · ', $parts);
    }
}
