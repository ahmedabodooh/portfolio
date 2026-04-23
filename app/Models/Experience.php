<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $fillable = [
        'company', 'role', 'location', 'period',
        'started_at', 'ended_at',
        'summary', 'highlights',
        'sort_order', 'is_published',
    ];

    protected $casts = [
        'started_at'   => 'date',
        'ended_at'     => 'date',
        'highlights'   => 'array',
        'sort_order'   => 'integer',
        'is_published' => 'boolean',
    ];

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    public function getIsCurrentAttribute(): bool
    {
        return is_null($this->ended_at);
    }
}
