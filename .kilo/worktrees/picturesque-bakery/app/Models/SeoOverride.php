<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoOverride extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'og_image_path',
        'noindex',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'noindex' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
