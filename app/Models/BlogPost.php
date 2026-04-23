<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'excerpt', 'body',
        'cover_image', 'tags', 'reading_minutes',
        'is_published', 'published_at',
    ];

    protected $casts = [
        'tags'            => 'array',
        'is_published'    => 'boolean',
        'published_at'    => 'datetime',
        'reading_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (empty($post->slug) && !empty($post->title)) {
                $post->slug = Str::slug($post->title);
            }
            if (!$post->reading_minutes && $post->body) {
                $words = str_word_count(strip_tags($post->body));
                $post->reading_minutes = max(1, (int) ceil($words / 220));
            }
            if ($post->is_published && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true)->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
    }
}
