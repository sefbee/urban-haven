<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\PublicationState;
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

    /**
     * Live listings can only be changed by staff who can publish properties.
     */
    public function update(User $user, Property $property): bool
    {
        if (! $user->hasPermission('property.update')) {
            return false;
        }

        return $user->hasPermission('property.publish') || $property->editorialStatus() !== PublicationState::PUBLISHED;
    }

    /**
     * Availability is managed independently from publication so stock stays accurate on live listings.
     */
    public function updateAvailability(User $user, Property $property): bool
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

    public function editReference(User $user, Property $property): bool
    {
        return $user->hasPermission('property.reference');
    }
}
