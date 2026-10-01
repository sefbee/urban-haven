<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    public const STATUSES = ['new', 'contacted', 'qualified', 'visit_scheduled', 'negotiation', 'won', 'lost'];

    public const STATUS_LABELS = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'visit_scheduled' => 'Visit Scheduled',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    public const CLOSED_STATUSES = ['won', 'lost'];

    public const PRIORITIES = ['high', 'medium', 'low'];

    public const TYPES = ['property_inquiry', 'visit_request', 'general_contact', 'campaign'];

    public const TYPE_LABELS = [
        'property_inquiry' => 'Property inquiry',
        'visit_request' => 'Visit request',
        'general_contact' => 'General contact',
        'campaign' => 'Campaign inquiry',
    ];

    public const LOSS_REASONS = [
        'price' => 'Price or budget mismatch',
        'location' => 'Location did not suit',
        'bought_elsewhere' => 'Bought or rented elsewhere',
        'no_response' => 'No response after follow-up',
        'not_ready' => 'Not ready to decide',
        'financing' => 'Financing not available',
        'other' => 'Other',
    ];

    protected $fillable = [
        'type',
        'name',
        'phone',
        'phone_hash',
        'email',
        'property_id',
        'project_id',
        'source',
        'preferred_contact',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'landing_url',
        'referrer',
        'ip_address',
        'message',
        'consent_given',
        'submission_token',
        'is_repeat_contact',
        'repeat_of_lead_id',
        'is_unread',
        'status',
        'loss_reason',
        'priority',
        'next_action',
        'next_action_at',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'next_action_at' => 'datetime',
            'consent_given' => 'boolean',
            'is_repeat_contact' => 'boolean',
            'is_unread' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Lead $lead): void {
            if (filled($lead->phone)) {
                $lead->phone_hash = self::hashPhone($lead->phone);
            }
        });
    }

    public static function hashPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? $phone;

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        }

        return hash('sha256', $digits);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public function isOverdue(): bool
    {
        return $this->next_action_at !== null
            && $this->next_action_at->isPast()
            && ! in_array($this->status, self::CLOSED_STATUSES, true);
    }

    public function hasAttribution(): bool
    {
        return filled($this->utm_source) || filled($this->utm_medium) || filled($this->utm_campaign) || filled($this->referrer);
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

        return $query->where('assigned_to', $user->id);
    }

    /**
     * Overdue first, then high priority, then soonest due, then newest.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeSalesQueueOrder(Builder $query): Builder
    {
        $closed = "'".implode("','", self::CLOSED_STATUSES)."'";

        return $query
            ->orderByRaw("case when next_action_at is not null and next_action_at < ? and status not in ({$closed}) then 0 else 1 end", [now()])
            ->orderByRaw("case priority when 'high' then 0 when 'medium' then 1 else 2 end")
            ->orderByRaw('next_action_at is null, next_action_at asc')
            ->orderByDesc('id');
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

    public function repeatOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'repeat_of_lead_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class)->latest('scheduled_at');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('id');
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisitRequest::class);
    }
}
