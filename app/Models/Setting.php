<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['group', 'key', 'value', 'cast'];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        return $setting->castedValue();
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $cast = 'string'): self
    {
        $stored = is_array($value) || is_bool($value) ? json_encode($value) : (string) $value;

        $setting = static::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $stored,
                'cast' => $cast,
                'updated_at' => now(),
            ],
        );

        return $setting;
    }

    public function castedValue(): mixed
    {
        return match ($this->cast) {
            'int', 'integer' => (int) $this->value,
            'bool', 'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'float' => (float) $this->value,
            'json', 'array' => json_decode((string) $this->value, true),
            default => $this->value,
        };
    }
}
