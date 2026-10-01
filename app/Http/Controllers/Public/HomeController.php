<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsBlock;
use App\Models\LocationArea;
use App\Models\Project;
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

        $heroImageSource = $featured->flatten()->concat($latest->flatten())->first(fn (Property $property) => $property->featuredImage() !== null);

        return view('public.home', [
            'purposes' => $purposes,
            'hero' => $hero,
            'about' => CmsBlock::contentFor('about'),
            'projects' => $this->featuredProjects(),
            'featuredSale' => $featured->get('sale', collect()),
            'featuredRent' => $featured->get('rent', collect()),
            'latestSale' => $latest->get('sale', collect()),
            'latestRent' => $latest->get('rent', collect()),
            'types' => $types,
            'typeCards' => $types->where('properties_count', '>', 0)->values(),
            'areas' => $areas->where('properties_count', '>', 0)->sortByDesc('properties_count')->take(12)->values(),
            'heroAreas' => $areas,
            'cities' => $areas->where('properties_count', '>', 0)->pluck('city')->filter()->unique()->values(),
            'hasAvailableProjects' => Project::query()->published()->whereIn('development_stage', ['ongoing', 'upcoming'])->exists(),
            'hasCompletedProjects' => Project::query()->published()->where('development_stage', 'completed')->exists(),
            'hasWhatsapp' => filled(Setting::get('whatsapp')),
            'heroImage' => $heroImageSource?->featuredImage(),
            'heroImageAlt' => $heroImageSource?->featuredImage()?->alt(app()->getLocale()) ?: '',
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

    /**
     * @return Collection<int, Project>
     */
    private function featuredProjects(): Collection
    {
        $ids = TaggedCache::remember(['homepage', 'projects'], 'home:projects', self::CACHE_SECONDS, function (): array {
            $featured = Project::query()->published()->where('is_featured', true)->orderByDesc('id')->limit(self::SECTION_LIMIT)->pluck('id')->all();

            return $featured !== [] ? $featured : Project::query()->published()->orderByDesc('id')->limit(self::SECTION_LIMIT)->pluck('id')->all();
        });

        return Project::query()
            ->whereKey($ids)
            ->with(['locationArea', 'media'])
            ->withCount(['properties' => fn (Builder $query) => $query->published()])
            ->addSelect([
                'starting_price' => Property::query()
                    ->selectRaw('min(price)')
                    ->whereColumn('properties.project_id', 'projects.id')
                    ->published()
                    ->where('price_mode', Property::PRICE_FIXED)
                    ->whereNotIn('availability', Property::UNAVAILABLE)
                    ->whereNotNull('price'),
            ])
            ->orderByDesc('id')
            ->get();
    }
}
