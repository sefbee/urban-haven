<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\Media;
use App\Models\Property;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for showcase-slide.blade.php rendering edge cases.
 *
 * Validates: Requirements 3.1, 3.2
 */
class PropertyCardRenderingTest extends TestCase
{
    use LazilyRefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Render the showcase-slide partial with sensible defaults, merging any
     * additional data passed by the caller.
     *
     * @param  array<string, mixed>  $data
     */
    private function renderSlide(array $data = []): string
    {
        $property = $data['property'] ?? $this->makeProperty(['title' => 'Test flat'], published: true);

        $defaults = [
            'property' => $property,
            'index' => 0,
            'total' => 1,
            'listingLayout' => true,
        ];

        return view('public.partials.showcase-slide', array_merge($defaults, $data))->render();
    }

    /**
     * Attach a gallery image to a property and return the property with its
     * media relation freshly loaded.
     */
    private function attachPhoto(Property $property): Property
    {
        Media::query()->forceCreate([
            'mediable_type' => $property->getMorphClass(),
            'mediable_id' => $property->id,
            'collection' => 'gallery',
            'disk' => 'public',
            'path' => 'media/test/photo.jpg',
            'original_filename' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1024,
            'is_public' => true,
            'alt_texts' => ['en' => 'Test photo'],
        ]);

        return $property->fresh(['media']);
    }

    // -----------------------------------------------------------------------
    // Requirement 3.1 — null $amenityCatalog guard
    // -----------------------------------------------------------------------

    /**
     * showcase-slide must render without exceptions when $amenityCatalog is
     * not passed (homepage carousel context).
     *
     * Validates: Requirement 3.1
     */
    public function test_showcase_slide_renders_without_errors_when_amenity_catalog_is_null(): void
    {
        $property = $this->makeProperty(['title' => 'No catalog flat', 'amenity_ids' => [1, 2, 3]], published: true);

        // No $amenityCatalog key — the partial must not throw or emit notices.
        $html = $this->renderSlide(['property' => $property]);

        $this->assertStringContainsString('No catalog flat', $html);
    }

    /**
     * showcase-slide renders without errors when $amenityCatalog is explicitly
     * null (e.g. a caller passes null intentionally).
     *
     * Validates: Requirement 3.1
     */
    public function test_showcase_slide_renders_without_errors_when_amenity_catalog_is_explicitly_null(): void
    {
        $property = $this->makeProperty(['title' => 'Explicit null catalog'], published: true);

        $html = $this->renderSlide([
            'property' => $property,
            'amenityCatalog' => null,
        ]);

        $this->assertStringContainsString('Explicit null catalog', $html);
    }

    /**
     * When $amenityCatalog is null and the property has amenity IDs, no
     * amenity chips are rendered (graceful degradation).
     *
     * Validates: Requirement 3.1
     */
    public function test_showcase_slide_omits_amenity_chips_when_catalog_is_null(): void
    {
        $amenity = Amenity::query()->create(['key' => 'pool', 'label' => 'Swimming Pool', 'is_active' => true]);
        $property = $this->makeProperty(['amenity_ids' => [$amenity->id]], published: true);

        // With catalog: chip should be visible.
        $catalog = Amenity::query()->get()->keyBy('id');
        $htmlWithCatalog = $this->renderSlide([
            'property' => $property,
            'amenityCatalog' => $catalog,
        ]);
        $this->assertStringContainsString('Swimming Pool', $htmlWithCatalog);

        // Without catalog: chip must not appear, but no exception either.
        $htmlWithoutCatalog = $this->renderSlide([
            'property' => $property,
        ]);
        $this->assertStringNotContainsString('Swimming Pool', $htmlWithoutCatalog);
    }

    // -----------------------------------------------------------------------
    // Requirement 3.2 — is-on class appears exactly once on the active photo
    // -----------------------------------------------------------------------

    /**
     * In listingLayout mode, the first (active) photo must carry the is-on
     * class exactly once — not duplicated via both static @class and dynamic
     * :class bindings.
     *
     * The regex anchors to a space or opening tag so that `:class` (Alpine
     * attribute) is not accidentally matched as a static `class` attribute.
     *
     * Validates: Requirement 3.2
     */
    public function test_is_on_class_appears_exactly_once_on_active_photo_in_listing_layout(): void
    {
        $property = $this->attachPhoto($this->makeProperty([], published: true));

        $html = $this->renderSlide([
            'property' => $property,
            'listingLayout' => true,
        ]);

        // Match only static class="..." attributes on <img> tags (not :class Alpine bindings).
        // The look-behind ensures we don't accidentally match `:class`.
        preg_match_all('/<img\b[^>]*(?<![:\w])class="([^"]*)"[^>]*>/i', $html, $matches, PREG_SET_ORDER);

        $isOnImgCount = 0;
        foreach ($matches as $match) {
            $fullTag = $match[0];
            $classValue = $match[1];
            if (preg_match('/\bis-on\b/', $classValue)) {
                $isOnImgCount++;
            }
        }

        $this->assertSame(
            1,
            $isOnImgCount,
            "Expected exactly 1 <img> with static class 'is-on' in listingLayout mode, found {$isOnImgCount}.",
        );
    }

    /**
     * In listingLayout mode with multiple photos, only the first photo has
     * the is-on class; subsequent photos do not.
     *
     * Validates: Requirement 3.2
     */
    public function test_only_first_photo_has_is_on_class_in_listing_layout(): void
    {
        $property = $this->makeProperty([], published: true);

        foreach (['first.jpg', 'second.jpg', 'third.jpg'] as $filename) {
            Media::query()->forceCreate([
                'mediable_type' => $property->getMorphClass(),
                'mediable_id' => $property->id,
                'collection' => 'gallery',
                'disk' => 'public',
                'path' => "media/test/{$filename}",
                'original_filename' => $filename,
                'mime_type' => 'image/jpeg',
                'size_bytes' => 1024,
                'is_public' => true,
                'alt_texts' => ['en' => "Alt for {$filename}"],
            ]);
        }

        $property = $property->fresh(['media']);

        $html = $this->renderSlide([
            'property' => $property,
            'listingLayout' => true,
        ]);

        // Extract all static class values on <img> tags (exclude Alpine :class bindings).
        preg_match_all('/<img\b[^>]*(?<![:\w])class="([^"]*)"[^>]*>/i', $html, $matches, PREG_SET_ORDER);
        $classValues = array_column($matches, 1);

        $this->assertGreaterThanOrEqual(3, count($classValues), 'Expected at least 3 <img> tags with static class attributes.');

        // First img must have is-on.
        $this->assertMatchesRegularExpression(
            '/\bis-on\b/',
            $classValues[0],
            'First <img> should have the is-on class.',
        );

        // Subsequent imgs must NOT have is-on.
        foreach (array_slice($classValues, 1) as $i => $classAttr) {
            $this->assertDoesNotMatchRegularExpression(
                '/\bis-on\b/',
                $classAttr,
                'Photo at index '.($i + 1).' should NOT have the is-on class.',
            );
        }
    }

    /**
     * In carousel (non-listingLayout) mode, the static @class is not used for
     * photos. Alpine's :class binding handles the active state at runtime, so
     * no server-rendered static is-on should appear on <img> elements.
     *
     * Validates: Requirement 3.2
     */
    public function test_no_static_is_on_class_on_photos_in_carousel_mode(): void
    {
        $property = $this->attachPhoto($this->makeProperty([], published: true));

        $html = $this->renderSlide([
            'property' => $property,
            'listingLayout' => false,
            'index' => 0,
            'total' => 1,
        ]);

        // Collect static class values from all <img> tags (not :class Alpine bindings).
        preg_match_all('/<img\b[^>]*(?<![:\w])class="([^"]*)"[^>]*>/i', $html, $matches, PREG_SET_ORDER);

        $isOnImgCount = 0;
        foreach ($matches as $match) {
            if (preg_match('/\bis-on\b/', $match[1])) {
                $isOnImgCount++;
            }
        }

        $this->assertSame(
            0,
            $isOnImgCount,
            "Expected 0 <img> elements with static is-on in carousel mode, found {$isOnImgCount}.",
        );
    }
}
