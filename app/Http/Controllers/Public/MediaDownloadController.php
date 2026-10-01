<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Concerns\Publishable;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaDownloadController extends Controller
{
    /**
     * Brochures stay on the private disk and are served only while their listing is published.
     */
    public function __invoke(Media $media): StreamedResponse
    {
        $owner = $media->mediable;

        abort_unless(
            $media->isDocument()
            && $media->is_public
            && $owner !== null
            && in_array(Publishable::class, class_uses_recursive($owner), true)
            && $owner->isPublished(),
            404,
        );

        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        $name = Str::slug(($owner->title ?? $owner->name ?? 'brochure').' '.$media->alt()).'.pdf';

        return Storage::disk($media->disk)->download($media->path, $name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
