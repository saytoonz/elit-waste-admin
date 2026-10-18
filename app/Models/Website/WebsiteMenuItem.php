<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteMenuItem extends Model
{
    protected $fillable = [
        'location',
        'label',
        'website_page_id',
        'url',
        'new_tab',
        'sort_order',
    ];

    protected $casts = [
        'new_tab' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(WebsitePage::class, 'website_page_id');
    }

    public function getHrefAttribute(): string
    {
        if ($this->page) {
            return $this->page->url;
        }
        return (string) $this->url;
    }

    /**
     * True when this item points at the page being viewed.
     */
    public function isActive(): bool
    {
        $href = rtrim($this->href, '/');
        return $href !== '' && $href === rtrim(url()->current(), '/');
    }
}
