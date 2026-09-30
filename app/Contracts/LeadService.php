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
     * @param  array<string, mixed>  $validated
     */
    public function capture(array $validated, Request $request): Lead;

    public function assign(Lead $lead, User $assignee, User $actor): void;

    public function addNote(Lead $lead, string $body, User $actor): LeadNote;

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleFollowUp(Lead $lead, array $data, User $actor): LeadFollowUp;

    public function completeFollowUp(LeadFollowUp $followUp, User $actor): void;

    public function updateStatus(Lead $lead, string $status, User $actor): void;
}
