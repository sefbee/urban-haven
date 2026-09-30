<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowUp extends Model
{
    public const ACTION_TYPES = ['call', 'email', 'whatsapp', 'meeting', 'site_visit', 'other'];

    protected $fillable = [
        'lead_id',
        'user_id',
        'action_type',
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

    public function isOverdue(): bool
    {
        return $this->completed_at === null
            && $this->scheduled_at !== null
            && $this->scheduled_at->isPast();
    }
}
