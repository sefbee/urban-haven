<?php

namespace App\Support;

/**
 * Turns a stored video URL into an embeddable player URL.
 * Only https links on the configured hosts are accepted.
 */
final class VideoEmbed
{
    public static function embedUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https') {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $allowed = config('urbanhaven.media.allowed_video_hosts', []);

        if (! in_array($host, $allowed, true)) {
            return null;
        }

        $path = (string) ($parts['path'] ?? '');

        if (in_array($host, ['youtube.com', 'www.youtube.com'], true)) {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $id = $query['v'] ?? null;

            if (! is_string($id) || $id === '') {
                if (preg_match('#^/(?:embed|shorts)/([\w-]{6,})$#', $path, $matches) === 1) {
                    $id = $matches[1];
                }
            }

            return self::youtube($id);
        }

        if ($host === 'youtu.be') {
            return self::youtube(trim($path, '/'));
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true) && preg_match('#^/(\d{5,})$#', $path, $matches) === 1) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }

    private static function youtube(mixed $id): ?string
    {
        if (! is_string($id) || preg_match('/^[\w-]{6,}$/', $id) !== 1) {
            return null;
        }

        return 'https://www.youtube-nocookie.com/embed/'.$id;
    }
}
