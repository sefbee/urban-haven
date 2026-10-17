<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Property;
use Illuminate\Support\Str;

class PropertyMediaAltTextGenerator
{
    public function forUpload(Property $property, string $collection, int $sortOrder): string
    {
        return $this->generate($property, $collection, $sortOrder);
    }

    public function forMedia(Property $property, Media $media): string
    {
        return $this->generate($property, $media->collection, max(1, (int) $media->sort_order));
    }

    private function generate(Property $property, string $collection, int $sortOrder): string
    {
        $property->loadMissing(['propertyType', 'locationArea']);

        $title = trim((string) $property->title);
        $propertyType = trim((string) $property->propertyType?->label);
        $subject = $title !== '' ? $title : ($propertyType !== '' ? $propertyType.' property' : 'Property');

        if ($propertyType !== '' && ! Str::contains(Str::lower($subject), Str::lower($propertyType))) {
            $subject .= ', '.$propertyType;
        }

        $locationParts = array_values(array_unique(array_filter([
            $property->locationArea?->name,
            $property->locationArea?->city,
            $property->locationArea?->country,
        ], fn (?string $part): bool => filled($part)
            && ! Str::contains(Str::lower($subject), Str::lower(trim($part))))));

        $description = $subject;
        if ($locationParts !== []) {
            $description .= ' — '.implode(', ', $locationParts);
        }

        $context = match ($collection) {
            'floor_plan' => 'Floor plan '.max(1, $sortOrder),
            default => 'Property photo '.max(1, $sortOrder),
        };
        $suffix = ' — '.$context;
        $availableLength = max(1, 200 - mb_strlen($suffix));

        return mb_strimwidth($description, 0, $availableLength, '').$suffix;
    }
}
