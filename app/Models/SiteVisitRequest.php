<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteVisitRequest extends Model
{
    public const REQUESTED = 'requested';

    public const CONFIRMED = 'confirmed';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    public const NO_SHOW = 'no_show';

    public const STATUSES = [self::REQUESTED, self::CONFIRMED, self::COMPLETED, self::CANCELLED, self::NO_SHOW];

    public const STATUS_LABELS = [
        self::REQUESTED => 'Requested',
        self::CONFIRMED => 'Confirmed',
        self::COMPLETED => 'Completed',
        self::CANCELLED => 'Cancelled',
        self::NO_SHOW => 'No-show',
    ];

    public const TRANSITIONS = [
        self::REQUESTED => [self::CONFIRMED, self::CANCELLED],
        self::CONFIRMED => [self::COMPLETED, self::NO_SHOW, self::CANCELLED],
    ];

    protected $fillable = [
        'lead_id',
        'property_id',
        'project_id',
        'preferred_at',
        'confirmed_at',
        'confirmed_by',
        'status',
        'assigned_to',
        'notes',
        'outcome_note',
    ];

    protected function casts(): array
    {
        return [
            'preferred_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canSeeAllLeads()) {
            return $query;
        }

        return $query->whereHas('lead', fn (Builder $lead) => $lead->where('assigned_to', $user->id));
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
