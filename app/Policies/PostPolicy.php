<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('cms.view');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->hasPermission('cms.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('cms.create');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->hasPermission('cms.update');
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->hasPermission('cms.delete');
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->hasPermission('cms.publish');
    }
}
