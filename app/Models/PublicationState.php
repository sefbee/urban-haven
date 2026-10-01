<?php

namespace App\Models;

use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PublicationState extends Model
{
    public const DRAFT = 'draft';

    public const PENDING_REVIEW = 'pending_review';

    public const APPROVED = 'approved';

    public const PUBLISHED = 'published';

    public const UNPUBLISHED = 'unpublished';

    protected $fillable = [
        'publishable_type',
        'publishable_id',
        'status',
        'published_by',
        'published_at',
        'unpublished_at',
        'unpublish_reason',
        'review_note',
        'submitted_by',
        'submitted_at',
    ];

    public const LABELS = [
        self::DRAFT => 'Draft',
        self::PENDING_REVIEW => 'Pending review',
        self::APPROVED => 'Approved',
        self::PUBLISHED => 'Published',
        self::UNPUBLISHED => 'Archived',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $state): void {
            if ($state->wasChanged('status') || $state->wasRecentlyCreated) {
                TaggedCache::flush(['homepage', 'properties', 'projects', 'search', 'sitemap', 'cms']);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'unpublished_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function publishable(): MorphTo
    {
        return $this->morphTo();
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
