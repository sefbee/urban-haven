<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

final class TaggedCache
{
    /**
     * @param  list<string>  $tags
     */
    public static function remember(array $tags, string $key, int $seconds, Closure $callback): mixed
    {
        if (self::supportsTags()) {
            return Cache::tags($tags)->remember($key, $seconds, $callback);
        }

        return Cache::remember($key, $seconds, $callback);
    }

    /**
     * @param  list<string>  $tags
     */
    public static function flush(array $tags): void
    {
        if (self::supportsTags()) {
            Cache::tags($tags)->flush();

            return;
        }

        Cache::flush();
    }

    public static function supportsTags(): bool
    {
        return in_array(config('cache.default'), ['redis', 'memcached'], true);
    }
}
