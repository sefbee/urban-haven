<?php

namespace App\Policies;

use App\Models\CmsBlock;
use App\Models\User;

class CmsBlockPolicy
{
    /**
     * Block images go live as soon as they are public, so only publishers may manage them.
     */
    public function update(User $user, CmsBlock $cmsBlock): bool
    {
        return $cmsBlock->acceptsImage() && $user->hasPermission('cms.publish');
    }
}
