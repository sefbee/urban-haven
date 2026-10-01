<?php

namespace App\Policies;

use App\Models\SiteVisitRequest;
use App\Models\User;

class SiteVisitRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('visit.view');
    }

    public function view(User $user, SiteVisitRequest $visit): bool
    {
        return $user->hasPermission('visit.view') && $this->canReach($user, $visit);
    }

    public function update(User $user, SiteVisitRequest $visit): bool
    {
        return $user->hasPermission('visit.update') && $this->canReach($user, $visit);
    }

    private function canReach(User $user, SiteVisitRequest $visit): bool
    {
        if ($user->canSeeAllLeads()) {
            return true;
        }

        $assignee = $visit->lead?->assigned_to ?? $visit->assigned_to;

        return $assignee !== null && (int) $assignee === (int) $user->id;
    }
}
