<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\PublicationState;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('project.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasPermission('project.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('project.create');
    }

    /**
     * Editors work on drafts only; live projects change through the owner.
     */
    public function update(User $user, Project $project): bool
    {
        if (! $user->hasPermission('project.update')) {
            return false;
        }

        return $user->hasPermission('project.publish') || $project->editorialStatus() !== PublicationState::PUBLISHED;
    }

    public function submit(User $user, Project $project): bool
    {
        return $user->hasPermission('project.update')
            && in_array($project->editorialStatus(), [PublicationState::DRAFT, PublicationState::UNPUBLISHED], true);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasPermission('project.delete');
    }

    public function publish(User $user, Project $project): bool
    {
        return $user->hasPermission('project.publish');
    }
}
