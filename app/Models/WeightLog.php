<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeightLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Fixed set of trash categories. Single source of truth for
     * the migration enum, form select, and validation.
     */
    public const TRASH_TYPES = ['General', 'Organic', 'Recyclable', 'Hazardous'];

    protected $fillable = [
        'logged_at',
        'vehicle_label',
        'trash_type',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'gross_weight' => 'decimal:2',
        'tare_weight' => 'decimal:2',
        'net_weight' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Keep net weight consistent regardless of how the record is saved.
        static::saving(function (WeightLog $log) {
            $log->net_weight = max(0, (float) $log->gross_weight - (float) $log->tare_weight);
        });
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('logged_at', [$start, $end]);
    }

    public function getTrashTypeBadgeClassesAttribute(): string
    {
        return match ($this->trash_type) {
            'General'    => 'bg-gray-50 text-gray-700 ring-gray-600/20',
            'Organic'    => 'bg-green-50 text-green-700 ring-green-600/20',
            'Recyclable' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            'Hazardous'  => 'bg-red-50 text-red-700 ring-red-600/20',
            default      => 'bg-gray-50 text-gray-700 ring-gray-600/20',
        };
    }
}
