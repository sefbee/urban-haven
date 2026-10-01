<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowUp extends Model
{
    public const ACTION_TYPES = ['call', 'email', 'whatsapp', 'meeting', 'site_visit', 'other'];

    public const OPEN = 'open';

    public const DONE = 'done';

    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'lead_id',
        'user_id',
        'action_type',
        'priority',
        'status',
        'notes',
        'scheduled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): bool
    {
        return $this->status === self::OPEN;
    }

    public function isOverdue(): bool
    {
        return $this->isOpen()
            && $this->scheduled_at !== null
            && $this->scheduled_at->isPast();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', self::OPEN)->where('scheduled_at', '<', now());
    }
}
