<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('property.view');
    }

    public function view(User $user, Property $property): bool
    {
        return $user->hasPermission('property.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('property.create');
    }

    public function update(User $user, Property $property): bool
    {
        return $user->hasPermission('property.update');
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->hasPermission('property.delete');
    }

    public function publish(User $user, Property $property): bool
    {
        return $user->hasPermission('property.publish');
    }
}
