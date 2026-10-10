<?php

namespace App\Actions;

use App\Models\Media;
use App\Models\Property;

class CanPublishProperty
{
    /**
     * Check all publish prerequisites in a single pass and return all failures.
     *
     * @return array{canPublish: bool, failures: list<string>}
     */
    public function __invoke(Property $property): array
    {
        $property->loadMissing('media');

        $failures = [];

        if (blank($property->reference)) {
            $failures[] = 'Missing reference';
        }

        if ($property->price === null && $property->price_mode === Property::PRICE_FIXED) {
            $failures[] = 'Missing price';
        }

        if ($property->area_value === null) {
            $failures[] = 'Missing size';
        }

        $gallery = $property->galleryImages();

        if ($gallery->isEmpty()) {
            $failures[] = 'No photos';
        } else {
            $locale = app()->getLocale();

            /** @var Media $photo */
            foreach ($gallery as $photo) {
                if ($photo->alt($locale) === '') {
                    $failures[] = 'Photo missing alt text';
                    break;
                }
            }
        }

        return [
            'canPublish' => $failures === [],
            'failures' => $failures,
        ];
    }
}
