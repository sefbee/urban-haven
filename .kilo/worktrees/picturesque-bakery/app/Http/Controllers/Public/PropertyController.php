<?php

namespace App\Http\Controllers\Public;

use App\Contracts\SimilarPropertiesService;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Redirect;
use App\Support\SeoMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropertyController extends Controller
{
    public function show(string $slug, SimilarPropertiesService $similar): View|RedirectResponse
    {
        $property = Property::query()
            ->published()
            ->with(['propertyType', 'locationArea', 'project', 'media', 'units', 'seoOverride', 'publicationState'])
            ->where('slug', $slug)
            ->first();

        if ($property === null) {
            $redirect = Redirect::query()->active()->where('from_path', '/properties/'.$slug)->first();
            if ($redirect) {
                return redirect($redirect->to_path, $redirect->http_code);
            }

            throw new NotFoundHttpException;
        }

        return view('public.properties.show', [
            'property' => $property,
            'similar' => $similar->similar($property),
            'seo' => SeoMeta::for($property, $property->title, Str::limit(strip_tags((string) $property->description), 150)),
            'whatsapp' => $this->whatsappUrl($property),
        ]);
    }

    private function whatsappUrl(Property $property): string
    {
        $number = preg_replace('/\D+/', '', (string) config('urbanhaven.whatsapp.number'));
        $text = rawurlencode('Hello Urban Haven, I am interested in '.$property->title.' ('.($property->reference ?? $property->slug).').');

        return 'https://wa.me/'.$number.'?text='.$text;
    }
}
