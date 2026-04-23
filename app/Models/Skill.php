<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name', 'category', 'proficiency', 'sort_order', 'is_published'];

    protected $casts = [
        'proficiency'  => 'integer',
        'sort_order'   => 'integer',
        'is_published' => 'boolean',
    ];

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }
}
