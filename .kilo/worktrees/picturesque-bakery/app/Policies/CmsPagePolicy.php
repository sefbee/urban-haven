<?php

namespace App\Policies;

use App\Models\CmsPage;
use App\Models\User;

class CmsPagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('cms.view');
    }

    public function view(User $user, CmsPage $cmsPage): bool
    {
        return $user->hasPermission('cms.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('cms.create');
    }

    public function update(User $user, CmsPage $cmsPage): bool
    {
        return $user->hasPermission('cms.update');
    }

    public function delete(User $user, CmsPage $cmsPage): bool
    {
        return $user->hasPermission('cms.delete');
    }
}
