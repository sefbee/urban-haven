<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Property;
use Illuminate\Support\Str;

/**
 * Compact payload for the listing quick-view dialog.
 */
final class PropertyPreview
{
    /**
     * @return array{
     *     id: int,
     *     title: string,
     *     url: string,
     *     price: string,
     *     exact: string|null,
     *     location: string,
     *     listing: string,
     *     type: string|null,
     *     reference: string|null,
     *     excerpt: string|null,
     *     whatsapp: string|null,
     *     saved: bool,
     *     removeUrl: string,
     *     updated: string|null,
     *     specs: list<string>,
     *     images: list<array{url: string, alt: string}>
     * }
     */
    public static function for(Property $property): array
    {
        $compact = MoneyFormatter::compactBdt($property->price);
        $exact = MoneyFormatter::formatBdt($property->price, $property->price_basis);
        $headline = $property->listing_type !== 'rent' && $compact !== null
            ? 'BDT '.$compact
            : $exact;

        $location = trim(implode(', ', array_filter([
            $property->locationArea?->name,
            $property->locationArea?->city,
        ])));

        $specs = array_values(array_filter([
            $property->bedrooms ? $property->bedrooms.' '.__('bed') : null,
            $property->bathrooms ? $property->bathrooms.' '.__('bath') : null,
            $property->area_value ? AreaConverter::format($property->area_value, $property->area_unit) : null,
            $property->floor_number ? __('Floor :number', ['number' => $property->floor_number]) : null,
            $property->is_furnished ? __('Furnished') : null,
        ]));

        $images = [];

        if ($property->relationLoaded('media')) {
            $images = $property->galleryImages()->take(12)->map(fn (Media $image): array => [
                'url' => $image->url(1280),
                'alt' => $image->alt(app()->getLocale()),
            ])->values()->all();
        }

        return [
            'id' => $property->id,
            'title' => $property->title,
            'url' => route('properties.show', $property->slug),
            'price' => $headline,
            'exact' => $headline === $exact ? null : $exact,
            'location' => $location,
            'listing' => $property->listing_type === 'rent' ? __('For rent') : __('For sale'),
            'type' => $property->propertyType?->label,
            'reference' => $property->reference,
            'excerpt' => filled($property->description)
                ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $property->description)) ?? ''), 220)
                : null,
            'whatsapp' => $property->whatsappEnquiryUrl(),
            'updated' => $property->last_updated_at
                ? __('Updated :time', ['time' => $property->last_updated_at->diffForHumans()])
                : null,
            'specs' => $specs,
            'images' => $images,
        ];
    }
}
