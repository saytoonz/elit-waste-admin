<?php

namespace App\Models\Website;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WebsitePost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'cover_image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'Published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function getUrlAttribute(): string
    {
        return route('site.posts.show', $this->slug);
    }
}
