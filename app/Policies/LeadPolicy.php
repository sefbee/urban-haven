<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('lead.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.view') && $this->canReach($user, $lead);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.update') && $this->canReach($user, $lead);
    }

    public function assign(User $user, Lead $lead): bool
    {
        return $user->hasPermission('lead.assign');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('lead.export');
    }

    private function canReach(User $user, Lead $lead): bool
    {
        return $user->canSeeAllLeads() || ($lead->assigned_to !== null && (int) $lead->assigned_to === (int) $user->id);
    }
}
