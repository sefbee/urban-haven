<?php

namespace App\Services\Lead;

use App\Contracts\AuditLogger;
use App\Contracts\LeadService as LeadServiceContract;
use App\Jobs\AttributeLeadSourceJob;
use App\Jobs\NotifyNewLeadJob;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\LeadNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeadService implements LeadServiceContract
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function capture(array $validated, Request $request): Lead
    {
        $phoneHash = hash('sha256', preg_replace('/\D+/', '', $validated['phone']) ?? $validated['phone']);
        $windowDays = (int) config('urbanhaven.lead.repeat_window_days', 30);

        $existing = Lead::query()
            ->where('phone_hash', $phoneHash)
            ->when(! empty($validated['property_id']), fn ($query) => $query->where('property_id', $validated['property_id']))
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $lead = DB::transaction(function () use ($validated, $request, $phoneHash) {
            return Lead::query()->create([
                ...$validated,
                'phone_hash' => $phoneHash,
                'utm_source' => $validated['utm_source'] ?? $request->query('utm_source', $request->input('utm_source')),
                'utm_medium' => $validated['utm_medium'] ?? $request->query('utm_medium', $request->input('utm_medium')),
                'utm_campaign' => $validated['utm_campaign'] ?? $request->query('utm_campaign', $request->input('utm_campaign')),
                'landing_url' => $request->headers->get('referer') ? $request->fullUrl() : $request->input('landing_url'),
                'referrer' => $request->headers->get('referer'),
                'ip_address' => $request->ip(),
                'status' => 'new',
                'priority' => $validated['priority'] ?? 'normal',
                'source' => $validated['source'] ?? 'inquiry',
            ]);
        });

        DB::afterCommit(function () use ($lead): void {
            NotifyNewLeadJob::dispatch($lead->id);
            AttributeLeadSourceJob::dispatch($lead->id);
        });

        return $lead;
    }

    public function assign(Lead $lead, User $assignee, User $actor): void
    {
        DB::transaction(function () use ($lead, $assignee, $actor): void {
            $old = $lead->assigned_to;
            $lead->forceFill(['assigned_to' => $assignee->id])->save();
            $this->auditLogger->record(
                $actor->id,
                'lead.assigned',
                Lead::class,
                $lead->id,
                ['assigned_to' => $old],
                ['assigned_to' => $assignee->id],
                request()->ip(),
            );
        });
    }

    public function addNote(Lead $lead, string $body, User $actor): LeadNote
    {
        return $lead->notes()->create([
            'user_id' => $actor->id,
            'body' => $body,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleFollowUp(Lead $lead, array $data, User $actor): LeadFollowUp
    {
        if (! in_array($data['action_type'], LeadFollowUp::ACTION_TYPES, true)) {
            throw ValidationException::withMessages(['action_type' => 'That follow-up type is not allowed.']);
        }

        $followUp = $lead->followUps()->create([
            'user_id' => $actor->id,
            'action_type' => $data['action_type'],
            'notes' => $data['notes'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? now()->addDay(),
        ]);

        $lead->forceFill([
            'next_action' => $data['action_type'],
            'next_action_at' => $followUp->scheduled_at,
        ])->save();

        return $followUp;
    }

    public function completeFollowUp(LeadFollowUp $followUp, User $actor): void
    {
        $followUp->forceFill(['completed_at' => now()])->save();
        $this->auditLogger->record($actor->id, 'lead.follow_up_completed', LeadFollowUp::class, $followUp->id, null, null, request()->ip());
    }

    public function updateStatus(Lead $lead, string $status, User $actor): void
    {
        if (! in_array($status, Lead::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Invalid lead status.']);
        }

        $old = $lead->status;
        $lead->forceFill(['status' => $status])->save();
        $this->auditLogger->record($actor->id, 'lead.status_updated', Lead::class, $lead->id, ['status' => $old], ['status' => $status], request()->ip());
    }
}
