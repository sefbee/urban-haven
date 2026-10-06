<?php

namespace App\Support;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Property;
use App\Models\Setting;
use Illuminate\Support\Collection;

/**
 * Schema.org payloads built only from published, factual fields.
 */
final class StructuredData
{
    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $phone = Setting::get('phone');
        $social = array_values(array_filter((array) Setting::get('social_links', [])));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => Setting::get('company_name', config('app.name')),
            'url' => url('/'),
            'telephone' => $phone ?: null,
            'email' => Setting::get('email') ?: null,
            'address' => filled(Setting::get('address')) ? ['@type' => 'PostalAddress', 'streetAddress' => Setting::get('address'), 'addressCountry' => 'BD'] : null,
            'sameAs' => $social ?: null,
        ]);
    }

    /**
     * @param  list<array{0: string, 1: string}>  $crumbs  label and URL pairs
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => Setting::get('company_name', config('app.name')),
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('properties.index').'?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public static function breadcrumbs(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $crumb, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb[0],
                'item' => $crumb[1],
            ], $crumbs, array_keys($crumbs)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function property(Property $property): array
    {
        $image = $property->featuredImage();
        $availability = match ($property->availability) {
            'available' => 'https://schema.org/InStock',
            'reserved' => 'https://schema.org/LimitedAvailability',
            default => 'https://schema.org/SoldOut',
        };

        $offer = $property->isPriceOnRequest() || $property->isUnavailable() ? null : [
            '@type' => 'Offer',
            'price' => (string) round((float) $property->price),
            'priceCurrency' => $property->currency ?: config('urbanhaven.currency.code', 'BDT'),
            'availability' => $availability,
            'businessFunction' => $property->listing_type === 'rent' ? 'https://purl.org/goodrelations/v1#LeaseOut' : 'https://purl.org/goodrelations/v1#Sell',
        ];

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateListing',
            'name' => $property->title,
            'url' => route('properties.show', $property->slug),
            'description' => $property->description ? mb_strimwidth(strip_tags($property->description), 0, 300, '…') : null,
            'image' => $image?->url(1280),
            'datePosted' => $property->publicationState?->published_at?->toDateString(),
            'dateModified' => $property->last_updated_at?->toIso8601String(),
            'identifier' => $property->reference,
            'offers' => $offer,
            'about' => array_filter([
                '@type' => $property->fieldProfile() === 'plot' ? 'Landform' : 'Accommodation',
                'numberOfRooms' => $property->bedrooms,
                'numberOfBathroomsTotal' => $property->bathrooms,
                'floorSize' => $property->area_sqft ? ['@type' => 'QuantitativeValue', 'value' => (float) $property->area_sqft, 'unitCode' => 'FTK'] : null,
                'address' => $property->locationArea ? ['@type' => 'PostalAddress', 'addressLocality' => $property->locationArea->name, 'addressRegion' => $property->locationArea->city, 'addressCountry' => 'BD'] : null,
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function article(Post $post): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => mb_strimwidth($post->title, 0, 110, ''),
            'description' => $post->excerpt,
            'image' => $post->featuredImage()?->url(1280),
            'datePublished' => $post->publicationState?->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => ['@type' => 'Organization', 'name' => $post->author_label ?: Setting::get('company_name', config('app.name'))],
            'publisher' => ['@type' => 'Organization', 'name' => Setting::get('company_name', config('app.name'))],
            'mainEntityOfPage' => route('articles.show', $post->slug),
        ]);
    }

    /**
     * @param  Collection<int, Faq>  $faqs
     * @return array<string, mixed>|null
     */
    public static function faq(Collection $faqs): ?array
    {
        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn (Faq $faq): array => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($faq->answer)],
            ])->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public static function encode(array $schema): string
    {
        return (string) json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
