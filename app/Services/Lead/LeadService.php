<?php

namespace App\Services\Lead;

use App\Contracts\AuditLogger;
use App\Contracts\LeadService as LeadServiceContract;
use App\Jobs\AttributeLeadSourceJob;
use App\Jobs\NotifyNewLeadJob;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadFollowUp;
use App\Models\LeadNote;
use App\Models\User;
use App\Notifications\LeadAcknowledgementNotification;
use App\Support\LeadAttribution;
use App\Support\PhoneNumber;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LeadService implements LeadServiceContract
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function capture(array $validated, Request $request, bool $notifyStaff = true): Lead
    {
        $token = $validated['submission_token'] ?? null;

        if ($token && ($original = Lead::query()->where('submission_token', $token)->first())) {
            return $original;
        }

        $phone = PhoneNumber::normalize($validated['phone']) ?? trim((string) $validated['phone']);
        $phoneHash = Lead::hashPhone($phone);
        $type = $validated['type'] ?? $this->inferType($validated);
        $email = filled($validated['email'] ?? null) ? mb_strtolower(trim($validated['email'])) : null;

        $duplicate = Lead::query()
            ->where('phone_hash', $phoneHash)
            ->where('type', $type)
            ->where('property_id', $validated['property_id'] ?? null)
            ->where('project_id', $validated['project_id'] ?? null)
            ->where('message', $validated['message'] ?? null)
            ->where('created_at', '>=', now()->subSeconds((int) config('urbanhaven.lead.duplicate_window_seconds', 60)))
            ->latest('id')
            ->first();

        if ($duplicate) {
            return $duplicate;
        }

        $this->throttlePhone($phoneHash);

        $previous = Lead::query()
            ->where(function ($query) use ($phoneHash, $email): void {
                $query->where('phone_hash', $phoneHash);
                if ($email !== null) {
                    $query->orWhere('email', $email);
                }
            })
            ->where('created_at', '>=', now()->subDays((int) config('urbanhaven.lead.repeat_window_days', 30)))
            ->latest('id')
            ->first();

        $attribution = LeadAttribution::fromRequest($request, $validated);

        try {
            $lead = DB::transaction(function () use ($validated, $request, $phone, $type, $email, $token, $previous, $attribution): Lead {
                $lead = Lead::query()->create([
                    'type' => $type,
                    'name' => trim((string) $validated['name']),
                    'phone' => $phone,
                    'email' => $email,
                    'property_id' => $validated['property_id'] ?? null,
                    'project_id' => $validated['project_id'] ?? null,
                    'source' => $attribution['utm_source'] ? 'campaign' : ($validated['source'] ?? 'website'),
                    'preferred_contact' => $validated['preferred_contact'] ?? null,
                    'utm_source' => $attribution['utm_source'],
                    'utm_medium' => $attribution['utm_medium'],
                    'utm_campaign' => $attribution['utm_campaign'],
                    'landing_url' => $attribution['landing_url'],
                    'referrer' => $attribution['referrer'],
                    'ip_address' => $request->ip(),
                    'message' => filled($validated['message'] ?? null) ? trim($validated['message']) : null,
                    'consent_given' => (bool) ($validated['consent_given'] ?? false),
                    'submission_token' => $token,
                    'is_repeat_contact' => $previous !== null,
                    'repeat_of_lead_id' => $previous?->id,
                    'is_unread' => true,
                    'status' => 'new',
                    'priority' => 'medium',
                    'assigned_to' => $previous?->assigned_to,
                ]);

                $this->recordActivity($lead, 'created', null, [
                    'type' => $type,
                    'repeat' => $previous !== null,
                    'repeat_of' => $previous?->id,
                ]);

                return $lead;
            });
        } catch (UniqueConstraintViolationException $exception) {
            if ($token && ($original = Lead::query()->where('submission_token', $token)->first())) {
                return $original;
            }

            throw $exception;
        }

        DB::afterCommit(function () use ($lead, $notifyStaff): void {
            if ($notifyStaff) {
                NotifyNewLeadJob::dispatch($lead->id);
            }
            AttributeLeadSourceJob::dispatch($lead->id);

            if ($lead->email) {
                Notification::route('mail', $lead->email)->notify(new LeadAcknowledgementNotification($lead->id));
            }
        });

        return $lead;
    }

    public function assign(Lead $lead, User $assignee, User $actor): void
    {
        DB::transaction(function () use ($lead, $assignee, $actor): void {
            $lead = Lead::query()->lockForUpdate()->findOrFail($lead->id);
            $old = $lead->assignee;
            $lead->forceFill(['assigned_to' => $assignee->id, 'is_unread' => true])->save();

            $this->recordActivity($lead, 'assignment', $actor, [
                'from' => $old?->id,
                'from_name' => $old?->name,
                'to' => $assignee->id,
                'to_name' => $assignee->name,
            ]);
            $this->auditLogger->record($actor->id, 'lead.assigned', Lead::class, $lead->id, ['assigned_to' => $old?->id], ['assigned_to' => $assignee->id], request()->ip());
        });
    }

    public function addNote(Lead $lead, string $body, User $actor): LeadNote
    {
        return DB::transaction(function () use ($lead, $body, $actor): LeadNote {
            $note = $lead->notes()->create([
                'user_id' => $actor->id,
                'body' => $body,
                'created_at' => now(),
            ]);

            $this->recordActivity($lead, 'note', $actor, ['note_id' => $note->id]);

            return $note;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleFollowUp(Lead $lead, array $data, User $actor): LeadFollowUp
    {
        if (! in_array($data['action_type'], LeadFollowUp::ACTION_TYPES, true)) {
            throw ValidationException::withMessages(['action_type' => 'That follow-up type is not allowed.']);
        }

        return DB::transaction(function () use ($lead, $data, $actor): LeadFollowUp {
            $followUp = $lead->followUps()->create([
                'user_id' => $data['user_id'] ?? $lead->assigned_to ?? $actor->id,
                'action_type' => $data['action_type'],
                'priority' => $data['priority'] ?? $lead->priority ?? 'medium',
                'status' => LeadFollowUp::OPEN,
                'notes' => $data['notes'] ?? null,
                'scheduled_at' => $data['scheduled_at'] ?? now()->addDay(),
            ]);

            $this->recordActivity($lead, 'follow_up', $actor, [
                'action' => 'scheduled',
                'action_type' => $followUp->action_type,
                'due' => $followUp->scheduled_at?->toIso8601String(),
            ]);
            $this->syncNextAction($lead);

            return $followUp;
        });
    }

    public function completeFollowUp(LeadFollowUp $followUp, User $actor): void
    {
        $this->closeFollowUp($followUp, $actor, LeadFollowUp::DONE);
    }

    public function cancelFollowUp(LeadFollowUp $followUp, User $actor): void
    {
        $this->closeFollowUp($followUp, $actor, LeadFollowUp::CANCELLED);
    }

    public function updateStatus(Lead $lead, string $status, User $actor, ?string $lossReason = null): void
    {
        if (! in_array($status, Lead::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Invalid lead stage.']);
        }

        if ($status === 'lost' && blank($lossReason)) {
            throw ValidationException::withMessages(['loss_reason' => 'Choose why this lead was lost.']);
        }

        DB::transaction(function () use ($lead, $status, $actor, $lossReason): void {
            $locked = Lead::query()->lockForUpdate()->findOrFail($lead->id);
            $old = $locked->status;

            if ($old === $status && $status !== 'lost') {
                return;
            }

            $locked->forceFill([
                'status' => $status,
                'loss_reason' => $status === 'lost' ? $lossReason : null,
            ])->save();
            $lead->setRawAttributes($locked->getAttributes(), true);

            $this->recordActivity($locked, 'stage_change', $actor, [
                'from' => $old,
                'to' => $status,
                'loss_reason' => $status === 'lost' ? $lossReason : null,
            ]);
            $this->auditLogger->record($actor->id, 'lead.status_updated', Lead::class, $locked->id, ['status' => $old], ['status' => $status, 'loss_reason' => $locked->loss_reason], request()->ip());
        });
    }

    public function updatePriority(Lead $lead, string $priority, ?string $nextAction, mixed $nextActionAt, User $actor): void
    {
        if (! in_array($priority, Lead::PRIORITIES, true)) {
            throw ValidationException::withMessages(['priority' => 'Invalid priority.']);
        }

        DB::transaction(function () use ($lead, $priority, $nextAction, $nextActionAt, $actor): void {
            $lead->forceFill([
                'priority' => $priority,
                'next_action' => filled($nextAction) ? $nextAction : null,
                'next_action_at' => $nextActionAt ? Carbon::parse($nextActionAt) : null,
            ])->save();

            $this->recordActivity($lead, 'priority', $actor, [
                'priority' => $priority,
                'next_action' => $lead->next_action,
                'due' => $lead->next_action_at?->toIso8601String(),
            ]);
        });
    }

    public function markOpened(Lead $lead, User $viewer): void
    {
        if (! $lead->is_unread || (int) $lead->assigned_to !== (int) $viewer->id) {
            return;
        }

        $lead->forceFill(['is_unread' => false])->save();
        $this->recordActivity($lead, 'opened', $viewer);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordActivity(Lead $lead, string $type, ?User $actor, array $payload = []): void
    {
        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'actor_id' => $actor?->id,
            'type' => $type,
            'payload' => $payload === [] ? null : $payload,
            'created_at' => now(),
        ]);
    }

    private function closeFollowUp(LeadFollowUp $followUp, User $actor, string $status): void
    {
        if (! $followUp->isOpen()) {
            return;
        }

        DB::transaction(function () use ($followUp, $actor, $status): void {
            $followUp->forceFill([
                'status' => $status,
                'completed_at' => $status === LeadFollowUp::DONE ? now() : null,
            ])->save();

            $this->recordActivity($followUp->lead, 'follow_up', $actor, [
                'action' => $status === LeadFollowUp::DONE ? 'completed' : 'cancelled',
                'action_type' => $followUp->action_type,
            ]);
            $this->auditLogger->record($actor->id, 'lead.follow_up_'.$status, LeadFollowUp::class, $followUp->id, null, ['status' => $status], request()->ip());
            $this->syncNextAction($followUp->lead);
        });
    }

    /**
     * Keeps the lead's next action pointing at its soonest open follow-up so the queue sort stays truthful.
     */
    private function syncNextAction(Lead $lead): void
    {
        $next = $lead->followUps()->getQuery()
            ->reorder()
            ->where('status', LeadFollowUp::OPEN)
            ->orderBy('scheduled_at')
            ->first();

        $lead->forceFill([
            'next_action' => $next ? str_replace('_', ' ', $next->action_type).($next->notes ? ': '.mb_strimwidth($next->notes, 0, 120, '…') : '') : null,
            'next_action_at' => $next?->scheduled_at,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function inferType(array $validated): string
    {
        return match (true) {
            ($validated['source'] ?? null) === 'visit_request' => 'visit_request',
            ($validated['source'] ?? null) === 'campaign' => 'campaign',
            filled($validated['property_id'] ?? null) || filled($validated['project_id'] ?? null) => 'property_inquiry',
            default => 'general_contact',
        };
    }

    private function throttlePhone(string $phoneHash): void
    {
        $key = 'lead-phone:'.$phoneHash;
        $max = (int) config('urbanhaven.lead.per_phone_per_hour', 5);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw new ThrottleRequestsException('Too many requests from this phone number. Please wait '.ceil(RateLimiter::availableIn($key) / 60).' minutes or call us.');
        }

        RateLimiter::hit($key, 3600);
    }
}
