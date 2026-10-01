<?php

namespace App\Services\Inventory;

use App\Contracts\AuditLogger;
use App\Contracts\InventoryService as InventoryServiceContract;
use App\Exceptions\StaleRecordException;
use App\Models\Media;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyStatusHistory;
use App\Models\PublicationState;
use App\Models\Unit;
use App\Models\User;
use App\Services\Seo\RedirectResolver;
use App\Support\TaggedCache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService implements InventoryServiceContract
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly RedirectResolver $redirects,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createProperty(array $validated, User $actor): Property
    {
        return DB::transaction(function () use ($validated, $actor) {
            unset($validated['version']);

            if (blank($validated['reference'] ?? null)) {
                $validated['reference'] = $this->nextReference();
            }

            $property = Property::query()->create($validated);
            $this->audit($actor, 'property.created', Property::class, $property->id, null, $property->only(['title', 'slug', 'reference']));
            $this->history($property, 'availability', null, $property->availability, $actor, 'Created');

            return $property;
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateProperty(Property $property, array $validated, User $actor): Property
    {
        $updated = DB::transaction(function () use ($property, $validated, $actor) {
            $locked = Property::query()->lockForUpdate()->findOrFail($property->id);
            $this->guardVersion($locked, $validated['version'] ?? null);
            unset($validated['version']);

            $oldSlug = $locked->slug;
            $tracked = ['title', 'slug', 'reference', 'price', 'price_mode', 'availability', 'listing_type'];
            $old = $locked->only($tracked);

            $locked->fill($validated);
            $locked->version = (int) $locked->version + 1;
            $locked->save();

            if ($oldSlug !== $locked->slug && $locked->isPublished()) {
                $this->redirects->store('/properties/'.$oldSlug, '/properties/'.$locked->slug, 301, 'Slug changed', $actor);
            }

            if ((string) $old['availability'] !== (string) $locked->availability) {
                $this->history($locked, 'availability', $old['availability'], $locked->availability, $actor);
            }

            if ((string) $old['price'] !== (string) $locked->price || $old['price_mode'] !== $locked->price_mode) {
                $this->history($locked, 'price', $old['price_mode'] === Property::PRICE_ON_REQUEST ? 'on request' : $old['price'], $locked->price_mode === Property::PRICE_ON_REQUEST ? 'on request' : $locked->price, $actor);
            }

            $this->audit($actor, 'property.updated', Property::class, $locked->id, $old, $locked->only($tracked));

            return $locked;
        });

        $this->flushProperty($updated);

        return $updated->refresh();
    }

    public function updateAvailability(Property $property, string $availability, ?int $expectedVersion, User $actor, ?string $note = null, mixed $reservationExpiresAt = null): Property
    {
        if (! in_array($availability, Property::AVAILABILITIES, true)) {
            throw ValidationException::withMessages(['availability' => 'Choose a valid availability.']);
        }

        $updated = DB::transaction(function () use ($property, $availability, $expectedVersion, $actor, $note, $reservationExpiresAt) {
            $locked = Property::query()->lockForUpdate()->findOrFail($property->id);
            $this->guardVersion($locked, $expectedVersion);

            $from = $locked->availability;
            $isReserved = $availability === 'reserved';

            $locked->forceFill([
                'availability' => $availability,
                'reserved_at' => $isReserved ? ($from === 'reserved' ? $locked->reserved_at : now()) : null,
                'reserved_by' => $isReserved ? ($from === 'reserved' ? $locked->reserved_by : $actor->id) : null,
                'reservation_expires_at' => $isReserved && $reservationExpiresAt ? Carbon::parse($reservationExpiresAt) : null,
                'version' => (int) $locked->version + 1,
            ])->save();

            if ($from !== $availability) {
                $this->history($locked, 'availability', $from, $availability, $actor, $note);
                $this->audit($actor, 'property.availability_changed', Property::class, $locked->id, ['availability' => $from], ['availability' => $availability, 'note' => $note]);
            }

            return $locked;
        });

        $this->flushProperty($updated);

        return $updated;
    }

    public function releaseExpiredReservations(): int
    {
        $released = 0;

        Property::query()
            ->where('availability', 'reserved')
            ->whereNotNull('reservation_expires_at')
            ->where('reservation_expires_at', '<=', now())
            ->orderBy('id')
            ->each(function (Property $property) use (&$released): void {
                DB::transaction(function () use ($property, &$released): void {
                    $locked = Property::query()->lockForUpdate()->find($property->id);

                    if ($locked === null || $locked->availability !== 'reserved' || $locked->reservation_expires_at?->isFuture()) {
                        return;
                    }

                    $locked->forceFill([
                        'availability' => 'available',
                        'reserved_at' => null,
                        'reserved_by' => null,
                        'reservation_expires_at' => null,
                        'version' => (int) $locked->version + 1,
                    ])->save();

                    $this->history($locked, 'availability', 'reserved', 'available', null, 'Reservation expired');
                    $this->auditLogger->record(null, 'property.reservation_expired', Property::class, $locked->id, ['availability' => 'reserved'], ['availability' => 'available'], null);
                    $released++;
                });

                $this->flushProperty($property);
            });

        return $released;
    }

    public function deleteProperty(Property $property, User $actor): void
    {
        DB::transaction(function () use ($property, $actor): void {
            $this->audit($actor, 'property.deleted', Property::class, $property->id, ['title' => $property->title, 'reference' => $property->reference], null);
            $property->delete();
        });

        $this->flushProperty($property);
    }

    /**
     * @return list<string>
     */
    public function publishChecklist(Property|Project $model): array
    {
        $missing = [];
        $model->loadMissing('media');
        $cover = $model->media->first(fn (Media $media): bool => $media->collection === 'gallery' && $media->is_public);

        if ($model instanceof Property) {
            if (blank($model->reference)) {
                $missing[] = 'Reference number';
            }
            if (! in_array($model->listing_type, ['sale', 'rent'], true)) {
                $missing[] = 'Purpose (sale or rent)';
            }
            if (! $model->property_type_id) {
                $missing[] = 'Property type';
            }
            if (! $model->location_area_id) {
                $missing[] = 'Location';
            }
            if ($model->area_value === null || (float) $model->area_value <= 0) {
                $missing[] = 'Area';
            }
            if ($model->price_mode !== Property::PRICE_ON_REQUEST && ($model->price === null || (float) $model->price <= 0)) {
                $missing[] = 'Price, or set the price to "on request"';
            }
        } else {
            if (blank($model->description)) {
                $missing[] = 'Description';
            }
            if (! $model->location_area_id && blank($model->city)) {
                $missing[] = 'Location';
            }
            if (blank($model->development_stage)) {
                $missing[] = 'Development stage';
            }
        }

        if ($cover === null) {
            $missing[] = 'Cover image';
        } elseif (! $cover->hasAltText()) {
            $missing[] = 'Alt text on the cover image';
        }

        return $missing;
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
        $updated = DB::transaction(function () use ($project, $validated, $actor) {
            $locked = Project::query()->lockForUpdate()->findOrFail($project->id);
            $oldSlug = $locked->slug;
            $old = $locked->only(['name', 'slug', 'development_stage']);
            $locked->fill($validated)->save();

            if ($oldSlug !== $locked->slug && $locked->isPublished()) {
                $this->redirects->store('/projects/'.$oldSlug, '/projects/'.$locked->slug, 301, 'Slug changed', $actor);
            }

            $this->audit($actor, 'project.updated', Project::class, $locked->id, $old, $locked->only(['name', 'slug', 'development_stage']));

            return $locked;
        });

        $this->flushProject($updated);

        return $updated->refresh();
    }

    public function deleteProject(Project $project, User $actor): void
    {
        DB::transaction(function () use ($project, $actor): void {
            $this->audit($actor, 'project.deleted', Project::class, $project->id, ['name' => $project->name], null);
            $project->delete();
        });

        $this->flushProject($project);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createUnit(array $validated, User $actor): Unit
    {
        $unit = DB::transaction(function () use ($validated, $actor) {
            $unit = Unit::query()->create($validated);
            $this->audit($actor, 'unit.created', Unit::class, $unit->id, null, $unit->only(['unit_number', 'property_id', 'status']));

            return $unit;
        });

        $this->flushUnit($unit);

        return $unit;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateUnit(Unit $unit, array $validated, User $actor): Unit
    {
        $unit = DB::transaction(function () use ($unit, $validated, $actor) {
            $locked = Unit::query()->lockForUpdate()->findOrFail($unit->id);
            $old = $locked->only(['unit_number', 'status', 'price']);
            $locked->fill($validated)->save();
            $this->audit($actor, 'unit.updated', Unit::class, $locked->id, $old, $locked->only(['unit_number', 'status', 'price']));

            return $locked;
        });

        $this->flushUnit($unit);

        return $unit;
    }

    public function deleteUnit(Unit $unit, User $actor): void
    {
        DB::transaction(function () use ($unit, $actor): void {
            $this->audit($actor, 'unit.deleted', Unit::class, $unit->id, ['unit_number' => $unit->unit_number], null);
            $unit->delete();
        });

        $this->flushUnit($unit);
    }

    public function submitForReview(Property|Project $model, User $actor): void
    {
        $this->transition($model, PublicationState::PENDING_REVIEW, $actor, [PublicationState::DRAFT, PublicationState::UNPUBLISHED], 'submitted_for_review', function (PublicationState $state) use ($actor): void {
            $state->submitted_by = $actor->id;
            $state->submitted_at = now();
            $state->review_note = null;
        });
    }

    public function approve(Property|Project $model, User $actor): void
    {
        $this->transition($model, PublicationState::APPROVED, $actor, [PublicationState::PENDING_REVIEW], 'approved');
    }

    public function returnToDraft(Property|Project $model, string $note, User $actor): void
    {
        if (trim($note) === '') {
            throw ValidationException::withMessages(['review_note' => 'Explain what needs to change.']);
        }

        $this->transition($model, PublicationState::DRAFT, $actor, [PublicationState::PENDING_REVIEW, PublicationState::APPROVED, PublicationState::UNPUBLISHED], 'returned_to_draft', function (PublicationState $state) use ($note): void {
            $state->review_note = $note;
        });
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
        $missing = $this->publishChecklist($model);

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'publish' => array_map(fn (string $item): string => 'Missing: '.$item, $missing),
            ]);
        }

        $from = [PublicationState::DRAFT, PublicationState::PENDING_REVIEW, PublicationState::APPROVED, PublicationState::UNPUBLISHED];

        $this->transition($model, PublicationState::PUBLISHED, $actor, $from, 'published', function (PublicationState $state) use ($actor): void {
            $state->published_by = $actor->id;
            $state->published_at = now();
            $state->unpublished_at = null;
            $state->unpublish_reason = null;
            $state->review_note = null;
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
            $model->publicationState()->firstOrCreate([], ['status' => PublicationState::DRAFT]);
            $state = $model->publicationState()->lockForUpdate()->firstOrFail();
            $current = $state->status;

            if (! in_array($current, $from, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot move from '.(PublicationState::LABELS[$current] ?? $current).' to '.(PublicationState::LABELS[$to] ?? $to).'.',
                ]);
            }

            $state->status = $to;
            if ($mutator) {
                $mutator($state);
            }
            $state->save();
            $model->setRelation('publicationState', $state);

            if ($model instanceof Property) {
                $this->history($model, 'publication', $current, $to, $actor, $state->unpublish_reason ?? $state->review_note);
            }

            $this->audit($actor, strtolower(class_basename($model)).'.'.$action, $model::class, $model->id, ['status' => $current], ['status' => $to]);
        });

        $model instanceof Property ? $this->flushProperty($model) : $this->flushProject($model);
    }

    private function guardVersion(Property $locked, mixed $expected): void
    {
        if ($expected === null || $expected === '') {
            return;
        }

        if ((int) $expected !== (int) $locked->version) {
            throw StaleRecordException::withCurrent([
                'availability' => $locked->availability,
                'price' => $locked->price_mode === Property::PRICE_ON_REQUEST ? 'on request' : $locked->price,
                'title' => $locked->title,
                'updated' => $locked->updated_at?->toDateTimeString(),
            ]);
        }
    }

    /**
     * Sequential per year; the unique index on reference backs this up under concurrency.
     */
    private function nextReference(): string
    {
        $prefix = config('urbanhaven.inventory.reference_prefix', 'UH').'-'.now()->format('Y').'-';

        $last = Property::query()
            ->where('reference', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('reference')
            ->value('reference');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function history(Property $property, string $field, mixed $from, mixed $to, ?User $actor, ?string $note = null): void
    {
        PropertyStatusHistory::query()->create([
            'property_id' => $property->id,
            'field' => $field,
            'from_value' => $from === null ? null : (string) $from,
            'to_value' => $to === null ? null : (string) $to,
            'actor_id' => $actor?->id,
            'note' => $note ? mb_strimwidth($note, 0, 250, '…') : null,
            'created_at' => now(),
        ]);
    }

    private function flushProperty(Property $property): void
    {
        $tags = ['properties', 'property:'.$property->id, 'homepage', 'sitemap', 'search', 'reports'];

        if ($property->project_id) {
            $tags[] = 'project:'.$property->project_id;
        }

        TaggedCache::flush($tags);
    }

    private function flushProject(Project $project): void
    {
        TaggedCache::flush(['projects', 'project:'.$project->id, 'homepage', 'sitemap', 'reports']);
    }

    private function flushUnit(Unit $unit): void
    {
        $property = Property::query()->find($unit->property_id);

        if ($property) {
            $this->flushProperty($property);
        }
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
