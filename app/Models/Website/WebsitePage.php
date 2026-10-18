<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsitePage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'status',
        'is_home',
        'meta_title',
        'meta_description',
        'og_image',
        'updated_by',
    ];

    protected $casts = [
        'is_home' => 'boolean',
    ];

    public function sections()
    {
        return $this->hasMany(WebsiteSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function visibleSections()
    {
        return $this->sections()->where('is_visible', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }

    public function getUrlAttribute(): string
    {
        return $this->is_home ? route('site.home') : route('site.page', $this->slug);
    }
}
