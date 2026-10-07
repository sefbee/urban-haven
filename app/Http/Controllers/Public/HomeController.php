<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\CmsBlock;
use App\Models\Faq;
use App\Models\LocationArea;
use App\Models\Post;
use App\Models\Property;
use App\Models\PropertyMetricDaily;
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

    private const TRENDING_LIMIT = 6;

    private const TRENDING_DAYS = 30;

    private const ARTICLE_LIMIT = 3;

    private const FAQ_LIMIT = 5;

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
            ->withCount(collect($purposes)->mapWithKeys(fn (string $purpose): array => [
                'properties as '.$purpose.'_listings_count' => fn (Builder $query) => $this->liveListings($query, [$purpose]),
            ])->all())
            ->orderBy('label')
            ->get();

        $areas = LocationArea::query()
            ->active()
            ->withCount(['properties' => fn (Builder $query) => $this->liveListings($query, $purposes)])
            ->orderBy('name')
            ->get();

        $featured = $this->listingsFor('featured', $purposes, fn (Builder $query) => $query->where('is_featured', true));
        $featuredAmenityIds = $featured->flatten()->flatMap(fn (Property $property): array => $property->amenity_ids ?? [])->unique()->values();
        $featuredAmenities = Amenity::query()->active()->whereIn('id', $featuredAmenityIds->all())->get()->keyBy('id');
        $featuredIds = $featured->flatten()->pluck('id');
        $latest = $this->listingsFor('latest', $purposes, fn (Builder $query) => $query->whereNotIn('id', $featuredIds->all() ?: [0]));

        $listings = $featured->flatten()->concat($latest->flatten());
        $types = $this->withCoverImages($types, 'property_type_id', $listings, $purposes);
        $uploadedHeroImages = CmsBlock::imagesFor('hero');
        $heroImageSource = $uploadedHeroImages->isEmpty() ? $listings->first(fn (Property $property) => $property->featuredImage() !== null) : null;
        $heroImages = $uploadedHeroImages->isNotEmpty() ? $uploadedHeroImages : collect([$heroImageSource?->featuredImage()])->filter()->values();
        $typeGroups = collect(PropertyType::CATEGORIES)
            ->mapWithKeys(fn (string $category): array => [$category => $types->where('category', $category)->values()])
            ->filter(fn (Collection $group): bool => $group->isNotEmpty());
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
            'trending' => $this->trendingListings($purposes),
            'featured' => $this->interleave($featured->get('sale', collect()), $featured->get('rent', collect())),
            'featuredAmenities' => $featuredAmenities,
            'latestSale' => $latest->get('sale', collect()),
            'latestRent' => $latest->get('rent', collect()),
            'types' => $types,
            'typeGroups' => $typeGroups,
            'areas' => $listedAreas,
            'faqs' => Faq::cachedVisible()->take(self::FAQ_LIMIT)->values(),
            'articles' => Post::query()->published()->with(['category', 'media', 'publicationState'])->latest('id')->limit(self::ARTICLE_LIMIT)->get(),
            'heroImages' => $heroImages,
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
     * Alternate sale and rent listings so one carousel shows both without a purpose toggle.
     *
     * @param  Collection<int, Property>  $first
     * @param  Collection<int, Property>  $second
     * @return Collection<int, Property>
     */
    private function interleave(Collection $first, Collection $second): Collection
    {
        $mixed = collect();

        foreach (range(0, max($first->count(), $second->count())) as $position) {
            $mixed->push($first->get($position), $second->get($position));
        }

        return $mixed->filter()->values();
    }

    /**
     * Most viewed live listings over the recent window; listings without views fall back to
     * display priority so the section is never empty on a quiet site.
     *
     * @param  list<string>  $purposes
     * @return Collection<int, Property>
     */
    private function trendingListings(array $purposes): Collection
    {
        $ids = TaggedCache::remember(['homepage', 'properties'], 'home:trending:'.implode(',', $purposes), self::CACHE_SECONDS, fn (): array => $this->liveListings(Property::query(), $purposes)
            ->leftJoinSub(
                PropertyMetricDaily::query()
                    ->selectRaw('property_id, sum(views) as recent_views')
                    ->where('date', '>=', now()->subDays(self::TRENDING_DAYS)->toDateString())
                    ->groupBy('property_id'),
                'recent_metrics',
                'recent_metrics.property_id',
                '=',
                'properties.id',
            )
            ->orderByRaw('coalesce(recent_metrics.recent_views, 0) desc')
            ->orderByRaw('display_priority is null, display_priority asc')
            ->orderByDesc('properties.id')
            ->limit(self::TRENDING_LIMIT)
            ->pluck('properties.id')
            ->all());

        return Property::query()->with($this->listingRelations)->whereKey($ids)->get()->sortBy(fn (Property $property) => array_search($property->id, $ids, true))->values();
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
