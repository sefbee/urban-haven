<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    public const STATUSES = ['new', 'qualified', 'negotiation', 'won', 'lost'];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    protected $fillable = [
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
        'status',
        'priority',
        'next_action',
        'next_action_at',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'next_action_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Lead $lead): void {
            if (filled($lead->phone)) {
                $lead->phone_hash = hash('sha256', preg_replace('/\D+/', '', $lead->phone) ?? $lead->phone);
            }
        });
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

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class)->latest('scheduled_at');
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisitRequest::class);
    }
}
