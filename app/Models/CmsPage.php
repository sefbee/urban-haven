<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class CmsPage extends Model
{
    use HasMedia, HasSlug, Publishable;

    public const TEMPLATES = ['default', 'about', 'contact', 'campaign'];

    public const MENU_ICONS = [
        'document' => 'Document',
        'home' => 'Home',
        'info' => 'Information',
        'phone' => 'Phone',
        'users' => 'People',
        'building' => 'Building',
        'globe' => 'Globe',
        'image' => 'Image',
        'link' => 'Link',
        'list' => 'List',
        'map' => 'Map',
        'menu' => 'Menu',
        'pin' => 'Location pin',
        'settings' => 'Settings',
        'shield' => 'Shield',
        'tag' => 'Tag',
        'sparkle' => 'Sparkle',
        'calendar' => 'Calendar',
    ];

    protected $fillable = [
        'slug',
        'template',
        'menu_icon',
        'title',
        'body',
        'layout_content',
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
            'layout_content' => 'array',
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
