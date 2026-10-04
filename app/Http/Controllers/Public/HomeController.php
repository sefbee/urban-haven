<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsBlock;
use App\Models\LocationArea;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Setting;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use App\Support\TaggedCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const SECTION_LIMIT = 8;

    private const CACHE_SECONDS = 600;

    /**
     * @var list<string>
     */
    private array $listingRelations = ['propertyType', 'locationArea', 'media'];

    public function index(): View
    {
        $purposes = Setting::enabledPurposes();
        $hero = CmsBlock::contentFor('hero');

        $types = PropertyType::query()
            ->active()
            ->withCount(['properties' => fn (Builder $query) => $this->liveListings($query, $purposes)])
            ->orderBy('label')
            ->get();

        $areas = LocationArea::query()
            ->active()
            ->withCount(['properties' => fn (Builder $query) => $this->liveListings($query, $purposes)])
            ->orderBy('name')
            ->get();

        $featured = $this->listingsFor('featured', $purposes, fn (Builder $query) => $query->where('is_featured', true));
        $featuredIds = $featured->flatten()->pluck('id');
        $latest = $this->listingsFor('latest', $purposes, fn (Builder $query) => $query->whereNotIn('id', $featuredIds->all() ?: [0]));

        $listings = $featured->flatten()->concat($latest->flatten());
        $heroImageSource = $listings->first(fn (Property $property) => $property->featuredImage() !== null);
        $typeCards = $this->withCoverImages(
            $types->where('properties_count', '>', 0)->values(),
            'property_type_id',
            $listings,
            $purposes,
        );
        $listedAreas = $this->withCoverImages(
            $areas->where('properties_count', '>', 0)->sortByDesc('properties_count')->take(12)->values(),
            'location_area_id',
            $listings,
            $purposes,
        );

        return view('public.home', [
            'purposes' => $purposes,
            'hero' => $hero,
            'about' => CmsBlock::contentFor('about'),
            'featuredSale' => $featured->get('sale', collect()),
            'featuredRent' => $featured->get('rent', collect()),
            'latestSale' => $latest->get('sale', collect()),
            'latestRent' => $latest->get('rent', collect()),
            'types' => $types,
            'typeCards' => $typeCards,
            'areas' => $listedAreas,
            'heroAreas' => $areas,
            'hasWhatsapp' => filled(Setting::get('whatsapp')),
            'heroImage' => $heroImageSource?->featuredImage(),
            'heroImageAlt' => $heroImageSource?->featuredImage()?->alt(app()->getLocale()) ?: '',
            'heroListing' => $heroImageSource,
            'liveListingCount' => $this->liveListings(Property::query(), $purposes)->count(),
            'seo' => SeoMeta::for(null, config('app.name'), $hero['body'] ?? 'Apartments, homes, land and commercial space for sale and rent in Dhaka, published directly by Urban Haven.', [
                'json_ld' => [StructuredData::website()],
            ]),
        ]);
    }

    /**
     * IDs are cached per purpose and flushed whenever inventory or media changes; models are then
     * loaded fresh with their relations so the cache never holds stale prices.
     *
     * @param  list<string>  $purposes
     * @param  callable(Builder<Property>): Builder<Property>  $scope
     * @return Collection<string, Collection<int, Property>>
     */
    private function listingsFor(string $section, array $purposes, callable $scope): Collection
    {
        return collect($purposes)->mapWithKeys(function (string $purpose) use ($section, $scope): array {
            $ids = TaggedCache::remember(['homepage', 'properties'], 'home:'.$section.':'.$purpose, self::CACHE_SECONDS, fn (): array => $scope(
                $this->liveListings(Property::query(), [$purpose])
            )->orderByRaw('display_priority is null, display_priority asc')->orderByDesc('id')->limit(self::SECTION_LIMIT)->pluck('id')->all());

            $models = Property::query()->with($this->listingRelations)->whereKey($ids)->get()->sortBy(fn (Property $property) => array_search($property->id, $ids, true))->values();

            return [$purpose => $models];
        });
    }

    /**
     * Attach one public listing photograph per type or area, reusing homepage listings first.
     *
     * @param  Collection<int, PropertyType|LocationArea>  $items
     * @param  Collection<int, Property>  $listings
     * @param  list<string>  $purposes
     * @return Collection<int, PropertyType|LocationArea>
     */
    private function withCoverImages(Collection $items, string $foreignKey, Collection $listings, array $purposes): Collection
    {
        $covers = $listings
            ->filter(fn (Property $property): bool => $property->featuredImage() !== null)
            ->unique($foreignKey)
            ->keyBy($foreignKey);

        $missing = $items->pluck('id')->diff($covers->keys());

        if ($missing->isNotEmpty()) {
            $covers = $covers->union(
                $this->liveListings(Property::query(), $purposes)
                    ->with('media')
                    ->whereIn($foreignKey, $missing->all())
                    ->whereHas('media', fn (Builder $query) => $query->where('collection', 'gallery')->where('is_public', true))
                    ->orderByDesc('id')
                    ->get()
                    ->unique($foreignKey)
                    ->keyBy($foreignKey)
            );
        }

        return $items->map(function (PropertyType|LocationArea $item) use ($covers): PropertyType|LocationArea {
            $item->setRelation('coverSource', $covers->get($item->id));

            return $item;
        });
    }

    /**
     * @param  Builder<Property>  $query
     * @param  list<string>  $purposes
     * @return Builder<Property>
     */
    private function liveListings(Builder $query, array $purposes): Builder
    {
        return $query->published()
            ->whereIn('listing_type', $purposes ?: ['__none__'])
            ->whereNotIn('availability', Property::UNAVAILABLE);
    }
}
