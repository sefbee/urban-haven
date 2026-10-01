<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Tag-style invalidation that works on every cache store. Each tag owns a version
 * counter baked into the key, so flushing a tag never touches unrelated entries
 * such as rate limiter counters or sessions.
 */
final class TaggedCache
{
    /**
     * @param  list<string>  $tags
     */
    public static function remember(array $tags, string $key, int $seconds, Closure $callback): mixed
    {
        return Cache::remember(self::versionedKey($tags, $key), $seconds, $callback);
    }

    /**
     * @param  list<string>  $tags
     */
    public static function flush(array $tags): void
    {
        foreach ($tags as $tag) {
            Cache::forever(self::versionKey($tag), self::version($tag) + 1);
        }
    }

    /**
     * @param  list<string>  $tags
     */
    private static function versionedKey(array $tags, string $key): string
    {
        $versions = array_map(fn (string $tag): string => $tag.'.'.self::version($tag), $tags);

        return 'uh-tc:'.implode('|', $versions).':'.$key;
    }

    private static function version(string $tag): int
    {
        return (int) Cache::get(self::versionKey($tag), 1);
    }

    private static function versionKey(string $tag): string
    {
        return 'uh-tc-version:'.$tag;
    }
}
