<?php

namespace App\Services\Lead;

use App\Contracts\AuditLogger;
use App\Contracts\LeadService;
use App\Models\Lead;
use App\Models\Role;
use App\Models\SiteVisitRequest;
use App\Models\User;
use App\Notifications\SiteVisitNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SiteVisitService
{
    public function __construct(
        private readonly LeadService $leads,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * A request never confirms a visit or changes property availability; staff confirm it later.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, Request $request): SiteVisitRequest
    {
        return DB::transaction(function () use ($validated, $request): SiteVisitRequest {
            $lead = $this->leads->capture([
                'type' => 'visit_request',
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'property_id' => $validated['property_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'message' => $validated['notes'] ?? null,
                'preferred_contact' => $validated['preferred_contact'] ?? null,
                'consent_given' => $validated['consent_given'] ?? false,
                'submission_token' => $validated['submission_token'] ?? null,
                'source' => 'visit_request',
            ], $request, notifyStaff: false);

            if (! $lead->wasRecentlyCreated && ($existing = $lead->siteVisits()->latest('id')->first())) {
                return $existing;
            }

            $visit = SiteVisitRequest::query()->create([
                'lead_id' => $lead->id,
                'property_id' => $validated['property_id'] ?? $lead->property_id,
                'project_id' => $validated['project_id'] ?? $lead->project_id,
                'preferred_at' => isset($validated['preferred_at']) ? Carbon::parse($validated['preferred_at'], config('urbanhaven.display_timezone'))->utc() : null,
                'status' => SiteVisitRequest::REQUESTED,
                'assigned_to' => $lead->assigned_to,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::afterCommit(function () use ($visit, $lead): void {
                $this->recipients($lead)->each(function (User $user) use ($visit): void {
                    $user->notifyNow(new SiteVisitNotification($visit, databaseOnly: true));
                    $user->notify(new SiteVisitNotification($visit, mailOnly: true));
                });
            });

            return $visit;
        });
    }

    /**
     * @param  array{confirmed_at?: mixed, outcome_note?: ?string}  $data
     */
    public function updateStatus(SiteVisitRequest $visit, string $status, User $actor, array $data = []): void
    {
        DB::transaction(function () use ($visit, $status, $actor, $data): void {
            $locked = SiteVisitRequest::query()->lockForUpdate()->findOrFail($visit->id);
            $allowed = SiteVisitRequest::TRANSITIONS[$locked->status] ?? [];

            if (! in_array($status, $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => 'A '.strtolower($locked->statusLabel()).' visit cannot be marked '.strtolower(SiteVisitRequest::STATUS_LABELS[$status] ?? $status).'.',
                ]);
            }

            $changes = ['status' => $status];

            if ($status === SiteVisitRequest::CONFIRMED) {
                if (empty($data['confirmed_at'])) {
                    throw ValidationException::withMessages(['confirmed_at' => 'Enter the confirmed visit time.']);
                }

                $changes['confirmed_at'] = Carbon::parse($data['confirmed_at'], config('urbanhaven.display_timezone'))->utc();
                $changes['confirmed_by'] = $actor->id;
            }

            if ($status === SiteVisitRequest::COMPLETED && blank($data['outcome_note'] ?? null)) {
                throw ValidationException::withMessages(['outcome_note' => 'Record the visit outcome.']);
            }

            if (filled($data['outcome_note'] ?? null)) {
                $changes['outcome_note'] = $data['outcome_note'];
            }

            $old = $locked->status;
            $locked->forceFill($changes)->save();
            $visit->setRawAttributes($locked->getAttributes(), true);

            $lead = $locked->lead;
            $this->leads->recordActivity($lead, 'visit_outcome', $actor, [
                'visit_id' => $locked->id,
                'status' => $status,
                'confirmed_at' => $locked->confirmed_at?->toIso8601String(),
                'outcome' => $locked->outcome_note,
            ]);
            $this->auditLogger->record($actor->id, 'visit.'.$status, SiteVisitRequest::class, $locked->id, ['status' => $old], $changes, request()->ip());

            if ($status === SiteVisitRequest::CONFIRMED && in_array($lead->status, ['new', 'contacted', 'qualified'], true)) {
                $this->leads->updateStatus($lead, 'visit_scheduled', $actor);
            }
        });
    }

    /**
     * @return Collection<int, User>
     */
    private function recipients(Lead $lead)
    {
        if ($lead->assigned_to && ($assignee = User::query()->where('is_active', true)->find($lead->assigned_to))) {
            return collect([$assignee]);
        }

        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->where('key', Role::OWNER_ADMIN))
            ->get();
    }
}
