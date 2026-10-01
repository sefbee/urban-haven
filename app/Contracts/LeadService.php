<?php

namespace App\Contracts;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\LeadNote;
use App\Models\User;
use Illuminate\Http\Request;

interface LeadService
{
    /**
     * Idempotent per submission token; accidental double submits return the original lead,
     * while a genuine repeat contact creates a new lead flagged for review.
     *
     * @param  array<string, mixed>  $validated
     */
    public function capture(array $validated, Request $request, bool $notifyStaff = true): Lead;

    public function assign(Lead $lead, User $assignee, User $actor): void;

    public function addNote(Lead $lead, string $body, User $actor): LeadNote;

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleFollowUp(Lead $lead, array $data, User $actor): LeadFollowUp;

    public function completeFollowUp(LeadFollowUp $followUp, User $actor): void;

    public function cancelFollowUp(LeadFollowUp $followUp, User $actor): void;

    public function updateStatus(Lead $lead, string $status, User $actor, ?string $lossReason = null): void;

    public function updatePriority(Lead $lead, string $priority, ?string $nextAction, mixed $nextActionAt, User $actor): void;

    public function markOpened(Lead $lead, User $viewer): void;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordActivity(Lead $lead, string $type, ?User $actor, array $payload = []): void;
}
