<?php

namespace Tests\Unit\Support;

use App\Support\VideoEmbed;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VideoEmbedTest extends TestCase
{
    #[DataProvider('embeddableUrls')]
    public function test_builds_an_embed_url_for_an_allowed_host(string $url, string $expected): void
    {
        $this->assertSame($expected, VideoEmbed::embedUrl($url));
    }

    #[DataProvider('rejectedUrls')]
    public function test_rejects_urls_that_must_not_be_embedded(?string $url): void
    {
        $this->assertNull(VideoEmbed::embedUrl($url));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function embeddableUrls(): array
    {
        return [
            'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
            'youtube short' => ['https://youtu.be/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
            'vimeo' => ['https://vimeo.com/123456789', 'https://player.vimeo.com/video/123456789'],
        ];
    }

    /**
     * @return array<string, array{0: string|null}>
     */
    public static function rejectedUrls(): array
    {
        return [
            'empty' => [null],
            'http' => ['http://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'other host' => ['https://evil.example/watch?v=dQw4w9WgXcQ'],
            'youtube without an id' => ['https://www.youtube.com/watch'],
        ];
    }
}
