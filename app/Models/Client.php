<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'logo', 'website', 'sort_order', 'is_published'];

    protected $casts = [
        'sort_order'   => 'integer',
        'is_published' => 'boolean',
    ];

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }
}
