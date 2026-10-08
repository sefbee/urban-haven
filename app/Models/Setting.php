<?php

namespace App\Models;

use App\Support\TaggedCache;
use ArrayObject;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const CACHE_TAG = 'settings';

    public $timestamps = false;

    protected $fillable = ['group', 'key', 'value', 'cast'];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::forgetCache());
        static::deleted(fn () => self::forgetCache());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::allValues();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /**
     * Resolved once per request or queued job through a scoped container holder.
     *
     * @return array<string, mixed>
     */
    public static function allValues(): array
    {
        /** @var ArrayObject<string, mixed> $holder */
        $holder = app('uh.settings');

        if (! isset($holder['values'])) {
            $holder['values'] = TaggedCache::remember([self::CACHE_TAG], 'all', 3600, function (): array {
                return static::query()->get()
                    ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->castedValue()])
                    ->all();
            });
        }

        return $holder['values'];
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $cast = 'string'): self
    {
        $stored = is_array($value) || is_bool($value) ? json_encode($value) : (string) $value;

        return static::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'value' => $stored,
                'cast' => $cast,
                'updated_at' => now(),
            ],
        );
    }

    public static function forgetCache(): void
    {
        unset(app('uh.settings')['values']);
        TaggedCache::flush([self::CACHE_TAG]);

        if (app()->bound('translator') && method_exists(app('translator'), 'setLoaded')) {
            app('translator')->setLoaded([]);
        }
    }

    public static function purposeEnabled(string $listingType): bool
    {
        return (bool) self::get($listingType === 'rent' ? 'enable_rent' : 'enable_sale', true);
    }

    /**
     * @return list<string>
     */
    public static function enabledPurposes(): array
    {
        return array_values(array_filter(['sale', 'rent'], fn (string $purpose): bool => self::purposeEnabled($purpose)));
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
