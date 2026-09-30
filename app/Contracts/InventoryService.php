<?php

namespace App\Contracts;

use App\Models\Project;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;

interface InventoryService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function createProperty(array $validated, User $actor): Property;

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateProperty(Property $property, array $validated, User $actor): Property;

    public function deleteProperty(Property $property, User $actor): void;

    public function publishProperty(Property $property, User $actor): void;

    public function unpublishProperty(Property $property, string $reason, User $actor): void;

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createProject(array $validated, User $actor): Project;

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateProject(Project $project, array $validated, User $actor): Project;

    public function deleteProject(Project $project, User $actor): void;

    public function publishProject(Project $project, User $actor): void;

    public function unpublishProject(Project $project, string $reason, User $actor): void;

    public function submitForReview(Property|Project $model, User $actor): void;

    public function approve(Property|Project $model, User $actor): void;

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createUnit(array $validated, User $actor): Unit;

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateUnit(Unit $unit, array $validated, User $actor): Unit;

    public function deleteUnit(Unit $unit, User $actor): void;
}
