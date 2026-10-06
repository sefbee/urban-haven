<?php

namespace Tests\Unit\Support;

use App\Models\Property;
use App\Models\PropertyType;
use App\Support\PropertyStory;
use Tests\TestCase;

class PropertyStoryTest extends TestCase
{
    public function test_home_facts_and_size_are_phrased_as_living_space(): void
    {
        $story = PropertyStory::for($this->property(['bedrooms' => 3, 'bathrooms' => 2, 'area_value' => 1850, 'area_unit' => 'sqft']));

        $this->assertSame(['3 bed', '2 bath', '1,850 sq ft'], $story->facts());
        $this->assertSame('1,850 sq ft of living space', $story->sizePhrase());
        $this->assertSame('Explore this home', $story->exploreLabel());
        $this->assertSame('Living here', $story->lifeTitle());
    }

    public function test_commercial_and_plot_listings_use_their_own_language(): void
    {
        $space = PropertyStory::for($this->property(['bathrooms' => 2, 'area_value' => 3200, 'area_unit' => 'sqft'], PropertyType::PROFILE_COMMERCIAL));
        $plot = PropertyStory::for($this->property(['bedrooms' => 2, 'area_value' => 5, 'area_unit' => 'katha', 'road_width_ft' => 30], PropertyType::PROFILE_PLOT));

        $this->assertSame('3,200 sq ft of floor space', $space->sizePhrase());
        $this->assertSame('Explore this space', $space->exploreLabel());
        $this->assertSame(['5 Katha'], $plot->facts());
        $this->assertSame('5 Katha of land', $plot->sizePhrase());
        $this->assertSame(['On a 30 ft road'], $plot->glance());
    }

    public function test_anchors_only_come_from_recorded_fields(): void
    {
        $this->assertSame([], PropertyStory::for($this->property(['bedrooms' => 2]))->anchors());

        $property = $this->property([
            'facing' => 'east',
            'bedrooms' => 3,
            'balconies' => 2,
            'is_furnished' => true,
            'floor_number' => 7,
            'parking_spaces' => 1,
        ]);
        $anchors = PropertyStory::for($property)->anchors();

        $this->assertSame([
            ['title' => 'East-facing', 'detail' => 'Morning light'],
            ['title' => '3 bedrooms', 'detail' => 'Room for family, guests or a study'],
            ['title' => '2 balconies', 'detail' => 'Private outdoor space'],
            ['title' => 'Furnished', 'detail' => 'Move in without starting from scratch'],
            ['title' => 'Floor 7', 'detail' => 'Raised well above the street'],
            ['title' => 'Parking for 1 car', 'detail' => null],
        ], $anchors);
        $this->assertSame(['East-facing', '2 balconies', 'Furnished'], PropertyStory::for($property)->glance());
    }

    public function test_unknown_facing_and_missing_size_produce_nothing(): void
    {
        $story = PropertyStory::for($this->property(['facing' => 'sideways', 'area_value' => null]));

        $this->assertNull($story->size());
        $this->assertNull($story->sizePhrase());
        $this->assertSame([], $story->glance());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function property(array $attributes, string $profile = PropertyType::PROFILE_APARTMENT): Property
    {
        $property = new Property($attributes);
        $property->setRelation('propertyType', new PropertyType(['field_profile' => $profile]));

        return $property;
    }
}
