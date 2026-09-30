<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShortlistController extends Controller
{
    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(['property_id' => ['required', 'integer', 'exists:properties,id']]);
        $list = $request->session()->get('shortlist', []);
        if (count($list) >= 4 && ! in_array((int) $validated['property_id'], $list, true)) {
            $message = 'You can compare up to 4 properties.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['shortlist' => $message]);
        }
        $list[] = (int) $validated['property_id'];
        $request->session()->put('shortlist', array_values(array_unique($list)));

        return $request->expectsJson()
            ? response()->json(['shortlist' => $request->session()->get('shortlist')])
            : back()->with('status', 'Added to shortlist.');
    }

    public function remove(Request $request, int $property): RedirectResponse
    {
        $list = collect($request->session()->get('shortlist', []))->reject(fn ($id) => (int) $id === $property)->values()->all();
        $request->session()->put('shortlist', $list);

        return back()->with('status', 'Removed from shortlist.');
    }

    public function index(Request $request): View
    {
        $ids = $request->session()->get('shortlist', []);
        $properties = Property::query()->published()->with(['propertyType', 'locationArea', 'media'])->whereIn('id', $ids)->get();

        return view('public.compare', ['properties' => $properties]);
    }
}
