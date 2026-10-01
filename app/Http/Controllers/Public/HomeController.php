<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LocationArea;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyType;
use App\Support\SeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * @var list<string>
     */
    private array $listingRelations = ['propertyType', 'locationArea', 'media'];

    public function index(): View
    {
        $types = PropertyType::query()
            ->active()
            ->withCount(['properties' => fn (Builder $query) => $query->published()])
            ->orderBy('label')
            ->get();

        $areas = LocationArea::query()
            ->active()
            ->withCount(['properties' => fn (Builder $query) => $query->published()])
            ->orderBy('name')
            ->get();

        $featuredSale = $this->publishedListings('sale')->where('is_featured', true)->limit(8)->get();
        $featuredRent = $this->publishedListings('rent')->where('is_featured', true)->limit(8)->get();
        $featuredIds = $featuredSale->pluck('id')->merge($featuredRent->pluck('id'));

        $latestSale = $this->latestListings('sale', $featuredIds);
        $latestRent = $this->latestListings('rent', $featuredIds);

        $heroImage = $featuredSale
            ->concat($featuredRent)
            ->concat($latestSale)
            ->concat($latestRent)
            ->first(fn (Property $property) => $property->featuredImage() !== null);

        return view('public.home', [
            'projects' => $this->featuredProjects(),
            'featuredSale' => $featuredSale,
            'featuredRent' => $featuredRent,
            'latestSale' => $latestSale,
            'latestRent' => $latestRent,
            'types' => $types,
            'typeCards' => $types->where('properties_count', '>', 0)->values(),
            'areas' => $areas->where('properties_count', '>', 0)->sortByDesc('properties_count')->take(12)->values(),
            'heroAreas' => $areas,
            'cities' => $areas->where('properties_count', '>', 0)->pluck('city')->filter()->unique()->values(),
            'hasAvailableProjects' => Project::query()->published()->whereIn('development_stage', ['ongoing', 'upcoming'])->exists(),
            'hasCompletedProjects' => Project::query()->published()->where('development_stage', 'completed')->exists(),
            'stats' => [
                'properties' => Property::query()->published()->count(),
                'projects' => Project::query()->published()->count(),
                'locations' => $areas->where('properties_count', '>', 0)->count(),
            ],
            'heroImage' => $heroImage?->featuredImage(),
            'heroImageAlt' => $heroImage?->title,
            'seo' => SeoMeta::for(null, config('app.name'), 'Company-owned apartments, duplexes and project units for sale and rent in Dhaka.'),
        ]);
    }

    /**
     * @return Collection<int, Project>
     */
    private function featuredProjects(): Collection
    {
        $projects = $this->projectQuery()->where('is_featured', true)->limit(8)->get();

        return $projects->isNotEmpty()
            ? $projects
            : $this->projectQuery()->limit(8)->get();
    }

    /**
     * @return Builder<Project>
     */
    private function projectQuery(): Builder
    {
        return Project::query()
            ->published()
            ->with(['locationArea', 'media'])
            ->withCount(['properties' => fn (Builder $query) => $query->published()])
            ->addSelect([
                'starting_price' => Property::query()
                    ->selectRaw('min(price)')
                    ->whereColumn('properties.project_id', 'projects.id')
                    ->published()
                    ->whereNotNull('price'),
            ])
            ->orderByDesc('id');
    }

    /**
     * @param  Collection<int, int>  $excludeIds
     * @return Collection<int, Property>
     */
    private function latestListings(string $listingType, Collection $excludeIds): Collection
    {
        return $this->publishedListings($listingType)
            ->when($excludeIds->isNotEmpty(), fn (Builder $query) => $query->whereNotIn('id', $excludeIds))
            ->limit(8)
            ->get();
    }

    /**
     * @return Builder<Property>
     */
    private function publishedListings(string $listingType): Builder
    {
        return Property::query()
            ->published()
            ->where('listing_type', $listingType)
            ->with($this->listingRelations)
            ->orderByDesc('id');
    }
}
