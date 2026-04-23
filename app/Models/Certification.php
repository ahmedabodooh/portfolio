<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = ['title', 'issuer', 'image', 'credential_url', 'year', 'sort_order', 'is_published'];

    protected $casts = [
        'year'         => 'integer',
        'sort_order'   => 'integer',
        'is_published' => 'boolean',
    ];

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }
}
