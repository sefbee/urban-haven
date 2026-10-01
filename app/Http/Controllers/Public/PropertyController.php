<?php

namespace App\Http\Controllers\Public;

use App\Contracts\SimilarPropertiesService;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Setting;
use App\Support\PhoneNumber;
use App\Support\SeoMeta;
use App\Support\StructuredData;
use App\Support\ViewCounter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropertyController extends Controller
{
    /**
     * Archived and draft listings 404 (the redirect middleware may then resolve a replacement);
     * sold and rented listings stay reachable with a clear status and alternatives.
     */
    public function show(Request $request, string $slug, SimilarPropertiesService $similar): View
    {
        $property = Property::query()
            ->published()
            ->with(['propertyType', 'locationArea', 'project', 'media', 'units', 'seoOverride', 'publicationState', 'assignedContact'])
            ->where('slug', $slug)
            ->first();

        if ($property === null || ! Setting::purposeEnabled($property->listing_type)) {
            throw new NotFoundHttpException;
        }

        ViewCounter::recordPropertyView($request, $property->id);

        $callNumber = $property->assignedContact?->phone ?: Setting::get('sales_phone') ?: Setting::get('phone');
        $breadcrumbs = [
            [__('Home'), route('home')],
            [$property->listing_type === 'rent' ? __('Rent') : __('Buy'), route('properties.index', ['listing_type' => $property->listing_type])],
            [$property->title, route('properties.show', $property->slug)],
        ];

        return view('public.properties.show', [
            'property' => $property,
            'similar' => $similar->similar($property),
            'isUnavailable' => $property->isUnavailable(),
            'callHref' => PhoneNumber::telHref($callNumber),
            'callNumber' => $callNumber,
            'publicAddress' => $property->publicAddress(),
            'coordinates' => $property->publicCoordinates(),
            'seo' => SeoMeta::for($property, $property->title, Str::limit(strip_tags((string) $property->description), 150), [
                'json_ld' => [StructuredData::property($property), StructuredData::breadcrumbs($breadcrumbs)],
            ]),
            'whatsapp' => $property->whatsappEnquiryUrl(),
            'analyticsEvents' => [[
                'event' => 'property_view',
                'property_id' => $property->id,
                'property_reference' => $property->reference,
                'listing_type' => $property->listing_type,
                'property_type' => $property->propertyType?->key,
                'location' => $property->locationArea?->slug,
            ]],
        ]);
    }
}
