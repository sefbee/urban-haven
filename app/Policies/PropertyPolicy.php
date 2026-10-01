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
     * Editors work on drafts only; live listings change through the owner.
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

    public function submit(User $user, Property $property): bool
    {
        return $user->hasPermission('property.update')
            && in_array($property->editorialStatus(), [PublicationState::DRAFT, PublicationState::UNPUBLISHED], true);
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
