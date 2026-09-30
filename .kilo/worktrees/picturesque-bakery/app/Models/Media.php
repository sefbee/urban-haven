<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'collection',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'sort_order',
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
        ];
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(?int $width = null): string
    {
        $path = $width ? $this->derivativePath($width) : $this->path;

        if (! Storage::disk($this->disk)->exists($path)) {
            $path = $this->path;
        }

        return Storage::disk($this->disk)->url($path);
    }

    public function thumbUrl(): string
    {
        return $this->url(480);
    }

    public function derivativePath(int $width): string
    {
        $directory = dirname($this->path);
        $filename = pathinfo($this->path, PATHINFO_FILENAME);

        return $directory.'/'.$filename.'-'.$width.'.webp';
    }

    public function alt(string $locale = 'en'): string
    {
        return $this->alt_texts[$locale] ?? $this->alt_texts['en'] ?? $this->original_filename;
    }
}
