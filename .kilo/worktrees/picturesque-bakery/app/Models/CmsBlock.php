<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsBlock extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'label',
        'content',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'updated_at' => 'datetime',
        ];
    }
}
