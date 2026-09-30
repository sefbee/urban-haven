<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteVisitRequest extends Model
{
    public const PENDING = 'pending';

    public const CONFIRMED = 'confirmed';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    public const TRANSITIONS = [
        self::PENDING => [self::CONFIRMED, self::CANCELLED],
        self::CONFIRMED => [self::COMPLETED, self::CANCELLED],
    ];

    protected $fillable = [
        'lead_id',
        'property_id',
        'project_id',
        'preferred_at',
        'status',
        'assigned_to',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'preferred_at' => 'datetime',
        ];
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
