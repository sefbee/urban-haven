<?php

namespace App\Console\Commands;

use App\Contracts\InventoryService;
use App\Contracts\MediaService;
use App\Models\Media;
use App\Models\Property;
use App\Models\PublicationState;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('properties:publish-eligible {--apply : Publish eligible properties instead of reporting what would change}')]
#[Description('Safely publish complete legacy properties and generate missing image alt text')]
class PublishEligibleProperties extends Command
{
    public function handle(InventoryService $inventory, MediaService $media): int
    {
        $apply = (bool) $this->option('apply');
        $eligible = [PublicationState::DRAFT, PublicationState::PENDING_REVIEW, PublicationState::APPROVED];
        $published = 0;
        $skipped = 0;

        Property::query()
            ->with(['publicationState', 'media', 'propertyType', 'locationArea'])
            ->where(fn (Builder $query) => $query->whereDoesntHave('publicationState')->orWhereHas('publicationState', fn (Builder $state) => $state->whereIn('status', $eligible)))
            ->chunkById(100, function ($properties) use ($apply, $inventory, $media, &$published, &$skipped): void {
                foreach ($properties as $property) {
                    $blockers = $inventory->publishChecklist($property);

                    if (! $property->media->contains(fn (Media $image): bool => $image->collection === 'gallery' && $image->is_public)) {
                        $blockers[] = 'Public cover photograph';
                    }

                    if ($blockers !== []) {
                        $this->warn("Skipped {$property->reference}: ".implode(', ', $blockers));
                        $skipped++;

                        continue;
                    }

                    if (! $apply) {
                        $this->line("Eligible: {$property->reference} — {$property->title}");
                        $published++;

                        continue;
                    }

                    $media->completePropertyAltText($property);
                    $inventory->publishProperty($property, null);
                    $this->info("Published {$property->reference}: {$property->title}");
                    $published++;
                }
            });

        $this->newLine();
        $this->line(($apply ? 'Published' : 'Eligible').": {$published}; skipped: {$skipped}.");

        if (! $apply && $published > 0) {
            $this->comment('Run again with --apply to publish eligible properties.');
        }

        return self::SUCCESS;
    }
}
