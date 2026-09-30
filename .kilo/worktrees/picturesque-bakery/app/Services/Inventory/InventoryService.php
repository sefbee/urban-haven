<?php

namespace App\Services\Inventory;

use App\Contracts\AuditLogger;
use App\Contracts\InventoryService as InventoryServiceContract;
use App\Models\Project;
use App\Models\Property;
use App\Models\PublicationState;
use App\Models\Redirect;
use App\Models\Unit;
use App\Models\User;
use App\Support\TaggedCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService implements InventoryServiceContract
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createProperty(array $validated, User $actor): Property
    {
        return DB::transaction(function () use ($validated, $actor) {
            $property = Property::query()->create($validated);
            $this->audit($actor, 'property.created', Property::class, $property->id, null, $property->only(['title', 'slug']));

            return $property;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateProperty(Property $property, array $validated, User $actor): Property
    {
        return DB::transaction(function () use ($property, $validated, $actor) {
            $oldSlug = $property->slug;
            $old = $property->only(['title', 'slug', 'price', 'availability']);
            $property->fill($validated);
            $property->save();

            if ($oldSlug !== $property->slug) {
                Redirect::query()->create([
                    'from_path' => '/properties/'.$oldSlug,
                    'to_path' => '/properties/'.$property->slug,
                    'http_code' => 301,
                    'is_active' => true,
                    'created_at' => now(),
                ]);
            }

            $this->audit($actor, 'property.updated', Property::class, $property->id, $old, $property->only(['title', 'slug', 'price', 'availability']));
            TaggedCache::flush(['properties', 'property:'.$property->id, 'homepage', 'sitemap']);

            return $property->refresh();
        });
    }

    public function deleteProperty(Property $property, User $actor): void
    {
        DB::transaction(function () use ($property, $actor): void {
            $this->audit($actor, 'property.deleted', Property::class, $property->id, ['title' => $property->title], null);
            $property->delete();
            TaggedCache::flush(['properties', 'property:'.$property->id, 'homepage', 'sitemap']);
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createProject(array $validated, User $actor): Project
    {
        return DB::transaction(function () use ($validated, $actor) {
            $project = Project::query()->create($validated);
            $this->audit($actor, 'project.created', Project::class, $project->id, null, $project->only(['name', 'slug']));

            return $project;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateProject(Project $project, array $validated, User $actor): Project
    {
        return DB::transaction(function () use ($project, $validated, $actor) {
            $oldSlug = $project->slug;
            $old = $project->only(['name', 'slug']);
            $project->fill($validated)->save();

            if ($oldSlug !== $project->slug) {
                Redirect::query()->create([
                    'from_path' => '/projects/'.$oldSlug,
                    'to_path' => '/projects/'.$project->slug,
                    'http_code' => 301,
                    'is_active' => true,
                    'created_at' => now(),
                ]);
            }

            $this->audit($actor, 'project.updated', Project::class, $project->id, $old, $project->only(['name', 'slug']));
            TaggedCache::flush(['projects', 'project:'.$project->id, 'homepage', 'sitemap']);

            return $project->refresh();
        });
    }

    public function deleteProject(Project $project, User $actor): void
    {
        DB::transaction(function () use ($project, $actor): void {
            $this->audit($actor, 'project.deleted', Project::class, $project->id, ['name' => $project->name], null);
            $project->delete();
            TaggedCache::flush(['projects', 'homepage', 'sitemap']);
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createUnit(array $validated, User $actor): Unit
    {
        return DB::transaction(function () use ($validated, $actor) {
            $unit = Unit::query()->create($validated);
            $this->audit($actor, 'unit.created', Unit::class, $unit->id, null, $unit->only(['unit_number', 'property_id']));

            return $unit;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateUnit(Unit $unit, array $validated, User $actor): Unit
    {
        return DB::transaction(function () use ($unit, $validated, $actor) {
            $old = $unit->only(['unit_number', 'status', 'price']);
            $unit->fill($validated)->save();
            $this->audit($actor, 'unit.updated', Unit::class, $unit->id, $old, $unit->only(['unit_number', 'status', 'price']));

            return $unit;
        });
    }

    public function deleteUnit(Unit $unit, User $actor): void
    {
        DB::transaction(function () use ($unit, $actor): void {
            $this->audit($actor, 'unit.deleted', Unit::class, $unit->id, ['unit_number' => $unit->unit_number], null);
            $unit->delete();
        });
    }

    public function submitForReview(Property|Project $model, User $actor): void
    {
        $this->transition($model, PublicationState::PENDING_REVIEW, $actor, [PublicationState::DRAFT, PublicationState::UNPUBLISHED], 'submitted_for_review');
    }

    public function approve(Property|Project $model, User $actor): void
    {
        $this->transition($model, PublicationState::APPROVED, $actor, [PublicationState::PENDING_REVIEW], 'approved');
    }

    public function publishProperty(Property $property, User $actor): void
    {
        $this->publish($property, $actor);
    }

    public function unpublishProperty(Property $property, string $reason, User $actor): void
    {
        $this->unpublish($property, $reason, $actor);
    }

    public function publishProject(Project $project, User $actor): void
    {
        $this->publish($project, $actor);
    }

    public function unpublishProject(Project $project, string $reason, User $actor): void
    {
        $this->unpublish($project, $reason, $actor);
    }

    private function publish(Property|Project $model, User $actor): void
    {
        $this->transition($model, PublicationState::PUBLISHED, $actor, [PublicationState::APPROVED], 'published', function (PublicationState $state) use ($actor): void {
            $state->published_by = $actor->id;
            $state->published_at = now();
            $state->unpublished_at = null;
            $state->unpublish_reason = null;
        });
    }

    private function unpublish(Property|Project $model, string $reason, User $actor): void
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['unpublish_reason' => 'A reason is required to unpublish.']);
        }

        $this->transition($model, PublicationState::UNPUBLISHED, $actor, [PublicationState::PUBLISHED], 'unpublished', function (PublicationState $state) use ($reason): void {
            $state->unpublished_at = now();
            $state->unpublish_reason = $reason;
        });
    }

    /**
     * @param  list<string>  $from
     * @param  callable(PublicationState):void|null  $mutator
     */
    private function transition(Property|Project $model, string $to, User $actor, array $from, string $action, ?callable $mutator = null): void
    {
        DB::transaction(function () use ($model, $to, $actor, $from, $action, $mutator): void {
            $state = $model->publicationState()->firstOrCreate([], ['status' => PublicationState::DRAFT]);
            $current = $state->status;

            if (! in_array($current, $from, true)) {
                throw ValidationException::withMessages([
                    'status' => "Cannot move from {$current} to {$to}.",
                ]);
            }

            $old = ['status' => $current];
            $state->status = $to;
            if ($mutator) {
                $mutator($state);
            }
            $state->save();

            $this->audit($actor, class_basename($model).'.'.$action, $model::class, $model->id, $old, ['status' => $to]);
        });

        $tag = $model instanceof Property ? 'properties' : 'projects';
        $idTag = $model instanceof Property ? 'property:'.$model->id : 'project:'.$model->id;
        TaggedCache::flush([$tag, $idTag, 'homepage', 'sitemap']);
    }

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    private function audit(User $actor, string $action, string $type, int $id, ?array $old, ?array $new): void
    {
        $this->auditLogger->record($actor->id, $action, $type, $id, $old, $new, request()->ip());
    }
}
