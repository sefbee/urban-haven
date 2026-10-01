<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'from_path',
        'to_path',
        'http_code',
        'reason',
        'created_by',
        'is_active',
        'hits',
        'last_hit_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'http_code' => 'integer',
            'created_at' => 'datetime',
            'last_hit_at' => 'datetime',
            'hits' => 'integer',
        ];
    }

    public static function normalizePath(string $path): string
    {
        $path = '/'.ltrim((string) parse_url($path, PHP_URL_PATH), '/');

        return $path === '/' ? $path : rtrim($path, '/');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
