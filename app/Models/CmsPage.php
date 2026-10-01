<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class CmsPage extends Model
{
    use HasSlug, Publishable;

    public const TEMPLATES = ['default', 'contact', 'campaign'];

    protected $fillable = [
        'slug',
        'template',
        'title',
        'body',
        'pending_changes',
        'meta_title',
        'meta_description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'pending_changes' => 'array',
        ];
    }

    public function hasPendingChanges(): bool
    {
        return ! empty($this->pending_changes);
    }

    protected static function booted(): void
    {
        static::created(function (CmsPage $page): void {
            $page->publicationState()->create(['status' => PublicationState::DRAFT]);
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->preventOverwrite();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
