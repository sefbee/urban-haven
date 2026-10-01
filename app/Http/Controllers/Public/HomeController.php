<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LocationArea;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyType;
use App\Support\SeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $relations = ['propertyType', 'locationArea', 'media'];
        $apartmentTypeIds = PropertyType::query()
            ->whereIn('key', ['apartment', 'apt'])
            ->pluck('id');

        $saleHomes = $this->publishedListings('sale', $relations)
            ->when($apartmentTypeIds->isNotEmpty(), fn (Builder $query) => $query->whereIn('property_type_id', $apartmentTypeIds))
            ->limit(8)
            ->get();

        $saleHeading = __('Apartments for sale in Dhaka');

        if ($saleHomes->isEmpty()) {
            $saleHomes = $this->publishedListings('sale', $relations)->limit(8)->get();
            $saleHeading = __('Homes for sale in Dhaka');
        }

        $rentHomes = $this->publishedListings('rent', $relations)->limit(8)->get();

        $projects = Project::query()
            ->published()
            ->where('is_featured', true)
            ->with(['locationArea', 'media'])
            ->withCount(['properties' => fn (Builder $query) => $query->published()])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        if ($projects->isEmpty()) {
            $projects = Project::query()
                ->published()
                ->with(['locationArea', 'media'])
                ->withCount(['properties' => fn (Builder $query) => $query->published()])
                ->orderByDesc('id')
                ->limit(8)
                ->get();
        }

        $heroImage = $saleHomes
            ->concat($rentHomes)
            ->first(fn (Property $property) => $property->featuredImage() !== null)
            ?->featuredImage();

        return view('public.home', [
            'projects' => $projects,
            'saleHomes' => $saleHomes,
            'saleHeading' => $saleHeading,
            'rentHomes' => $rentHomes,
            'areas' => LocationArea::query()
                ->active()
                ->withCount(['properties' => fn (Builder $query) => $query->published()])
                ->orderByDesc('properties_count')
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'types' => PropertyType::query()->active()->orderBy('label')->get(),
            'heroImage' => $heroImage,
            'seo' => SeoMeta::for(null, config('app.name'), 'Company-owned apartments, duplexes and project units for sale and rent in Dhaka.'),
        ]);
    }

    /**
     * @param  list<string>  $relations
     * @return Builder<Property>
     */
    private function publishedListings(string $listingType, array $relations): Builder
    {
        return Property::query()
            ->published()
            ->where('listing_type', $listingType)
            ->with($relations)
            ->orderByDesc('id');
    }
}
