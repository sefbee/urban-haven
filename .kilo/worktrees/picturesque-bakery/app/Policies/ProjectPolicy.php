<?php

namespace App\Policies;

use App\Models\Project;
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

    public function update(User $user, Project $project): bool
    {
        return $user->hasPermission('project.update');
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
