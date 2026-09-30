<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'unpublished_at' => 'datetime',
        ];
    }

    public function publishable(): MorphTo
    {
        return $this->morphTo();
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
