<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteSection extends Model
{
    protected $fillable = [
        'website_page_id',
        'type',
        'data',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'data'       => 'array',
        'is_visible' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(WebsitePage::class, 'website_page_id');
    }

    public function definition(): array
    {
        return config("website.sections.{$this->type}", ['label' => $this->type, 'fields' => []]);
    }

    /**
     * Short text used to tell sections apart in the page editor.
     */
    public function summary(): string
    {
        $d = $this->data ?? [];
        return (string) ($d['heading'] ?? $d['title'] ?? $d['eyebrow'] ?? '');
    }
}
