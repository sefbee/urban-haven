<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class LeadActivity extends Model
{
    public const TYPES = ['created', 'note', 'stage_change', 'assignment', 'follow_up', 'visit_outcome', 'priority', 'export', 'opened'];

    public $timestamps = false;

    protected $fillable = ['lead_id', 'actor_id', 'type', 'payload', 'created_at'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Lead history is append-only.'));
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function summary(): string
    {
        $payload = $this->payload ?? [];

        return match ($this->type) {
            'created' => 'Lead created from '.(Lead::TYPE_LABELS[$payload['type'] ?? ''] ?? 'the website').(! empty($payload['repeat']) ? ' (repeat contact)' : ''),
            'stage_change' => 'Stage changed from '.(Lead::STATUS_LABELS[$payload['from'] ?? ''] ?? '—').' to '.(Lead::STATUS_LABELS[$payload['to'] ?? ''] ?? '—')
                .(! empty($payload['loss_reason']) ? ' — '.(Lead::LOSS_REASONS[$payload['loss_reason']] ?? $payload['loss_reason']) : ''),
            'assignment' => 'Assigned to '.($payload['to_name'] ?? 'nobody').(! empty($payload['from_name']) ? ' (was '.$payload['from_name'].')' : ''),
            'note' => 'Note added',
            'follow_up' => ucfirst((string) ($payload['action'] ?? 'scheduled')).' follow-up: '.str_replace('_', ' ', (string) ($payload['action_type'] ?? '')),
            'visit_outcome' => 'Visit '.str_replace('_', ' ', (string) ($payload['status'] ?? 'updated')),
            'priority' => 'Priority set to '.($payload['priority'] ?? '—').(! empty($payload['next_action']) ? ', next action: '.$payload['next_action'] : ''),
            'opened' => 'Opened by the assignee',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
