<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    public const IMAGE_COLLECTIONS = ['gallery', 'floor_plan'];

    public const DOCUMENT_COLLECTIONS = ['brochure'];

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'collection',
        'disk',
        'original_disk',
        'original_path',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'checksum',
        'width',
        'height',
        'sort_order',
        'is_public',
        'alt_texts',
    ];

    protected function casts(): array
    {
        return [
            'alt_texts' => 'array',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'sort_order' => 'integer',
            'is_public' => 'boolean',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isDocument(): bool
    {
        return in_array($this->collection, self::DOCUMENT_COLLECTIONS, true);
    }

    /**
     * @var array<string, bool>
     */
    private static array $derivativeExists = [];

    /**
     * Public URL of a processed display copy; uploaded originals are never exposed.
     */
    public function url(?int $width = null): string
    {
        if ($this->isDocument()) {
            return route('media.download', $this);
        }

        $path = $width ? $this->derivativePath($width) : $this->path;

        if ($width) {
            $cacheKey = $this->disk.':'.$path;
            if (! isset(self::$derivativeExists[$cacheKey])) {
                self::$derivativeExists[$cacheKey] = Storage::disk($this->disk)->exists($path);
            }

            if (! self::$derivativeExists[$cacheKey]) {
                $path = $this->path;
            }
        }

        $url = Storage::disk($this->disk)->url($path);

        if (
            ! app()->runningInConsole()
            && $this->disk === 'public'
            && config('filesystems.disks.public.driver') === 'local'
        ) {
            $publicDiskUrl = config('filesystems.disks.public.url');
            $applicationUrl = config('app.url');
            $configuredHost = parse_url($publicDiskUrl, PHP_URL_HOST);
            $applicationHost = parse_url($applicationUrl, PHP_URL_HOST);

            if ($configuredHost !== null && $configuredHost === $applicationHost && $configuredHost !== request()->getHost()) {
                return url('/storage/'.ltrim($path, '/'));
            }
        }

        return $url;
    }

    public function thumbUrl(): string
    {
        return $this->url(480);
    }

    public function srcset(): string
    {
        return collect(config('urbanhaven.media.derivative_widths', [480, 768, 1280, 1920]))
            ->map(fn (int $width): string => $this->url($width).' '.$width.'w')
            ->implode(', ');
    }

    public function derivativePath(int $width): string
    {
        $directory = dirname($this->path);
        $filename = pathinfo($this->path, PATHINFO_FILENAME);

        return $directory.'/'.$filename.'-'.$width.'.webp';
    }

    public function alt(string $locale = 'en'): string
    {
        return $this->alt_texts[$locale] ?? $this->alt_texts['en'] ?? '';
    }

    public function hasAltText(): bool
    {
        return filled($this->alt_texts['en'] ?? null);
    }
}
