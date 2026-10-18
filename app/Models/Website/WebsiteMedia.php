<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteMedia extends Model
{
    protected $table = 'website_media';

    protected $fillable = [
        'path',
        'original_name',
        'mime',
        'size',
        'width',
        'height',
        'alt',
        'uploaded_by',
    ];

    public function getUrlAttribute(): string
    {
        return asset($this->path);
    }

    /**
     * Files we uploaded live on the public disk; seeded defaults live in public/images
     * and ship with the code, so they are never deleted from disk.
     */
    public function isUploaded(): bool
    {
        return str_starts_with($this->path, 'storage/');
    }
}
