<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Support\SeoFields;
use App\Support\TaggedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class AmenityController extends Controller
{
    public function index(): View
    {
        $this->authorize('reference.manage');

        return view('admin.catalogue.amenities', [
            'amenities' => Amenity::query()->orderBy('label')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('reference.manage');

        if (! $request->filled('key') && $request->filled('label')) {
            $request->merge(['key' => SeoFields::uniqueSlug('amenities', 'key', (string) $request->input('label'))]);
        }

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', 'unique:amenities,key'],
            'label' => ['required', 'string', 'max:80'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'dimensions:max_width=512,max_height=512', 'max:2048'],
        ]);
        $iconPath = isset($validated['icon']) ? $this->storeIcon($validated['icon']) : null;
        unset($validated['icon']);

        $amenity = Amenity::query()->create([...$validated, 'icon_path' => $iconPath, 'is_active' => true]);
        TaggedCache::flush(['search', 'homepage']);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $amenity->id,
                'label' => $amenity->label,
            ], 201);
        }

        return back()->with('status', 'Amenity added.');
    }

    public function update(Request $request, Amenity $amenity): RedirectResponse
    {
        $this->authorize('reference.manage');
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'dimensions:max_width=512,max_height=512', 'max:2048'],
            'remove_icon' => ['sometimes', 'boolean'],
        ]);
        $oldIconPath = $amenity->icon_path;
        $newIconPath = isset($validated['icon'])
            ? $this->storeIcon($validated['icon'])
            : ($request->boolean('remove_icon') ? null : $oldIconPath);
        unset($validated['icon'], $validated['remove_icon']);

        $amenity->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', $amenity->is_active),
            'icon_path' => $newIconPath,
        ]);

        if ($oldIconPath && $oldIconPath !== $newIconPath) {
            Storage::disk('public')->delete($oldIconPath);
        }

        TaggedCache::flush(['search', 'homepage']);

        return back()->with('status', $amenity->label.' updated.');
    }

    public function deactivate(Amenity $amenity): RedirectResponse
    {
        $this->authorize('reference.manage');
        $amenity->update(['is_active' => false]);

        return back()->with('status', 'Amenity deactivated.');
    }

    private function storeIcon(UploadedFile $file): string
    {
        $path = 'amenities/icons/'.Str::uuid().'.webp';

        try {
            $image = (new ImageManager(new Driver))
                ->read($file->getRealPath())
                ->scaleDown(width: 256, height: 256)
                ->toWebp(quality: 85);

            if (! Storage::disk('public')->put($path, (string) $image)) {
                throw new RuntimeException('Unable to store the processed icon.');
            }
        } catch (Throwable) {
            throw ValidationException::withMessages(['icon' => 'The icon could not be processed. Upload a PNG, JPEG or WebP image.']);
        }

        return $path;
    }
}
