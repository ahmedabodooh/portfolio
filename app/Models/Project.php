<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'title', 'slug', 'category', 'client', 'role', 'year',
        'tagline', 'summary', 'description',
        'highlights', 'tech_stack',
        'cover_image', 'gallery',
        'live_url', 'repo_url',
        'is_featured', 'is_published', 'sort_order',
    ];

    protected $casts = [
        'highlights'   => 'array',
        'tech_stack'   => 'array',
        'gallery'      => 'array',
        'is_featured'  => 'boolean',
        'is_published' => 'boolean',
        'year'         => 'integer',
        'sort_order'   => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (empty($project->slug) && !empty($project->title)) {
                $project->slug = Str::slug($project->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }
}
