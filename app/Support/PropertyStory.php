<?php

namespace App\Support;

use App\Models\Property;
use App\Models\PropertyType;

/**
 * Translates a listing's recorded facts into plain, human phrasing for the public site.
 *
 * Every line is derived from a stored field. Nothing here may add a claim the data does not
 * support: a missing field produces no phrase rather than a guess.
 */
final class PropertyStory
{
    public const KIND_HOME = 'home';

    public const KIND_SPACE = 'space';

    public const KIND_PLOT = 'plot';

    /**
     * Daylight each recorded facing receives at Dhaka's latitude (about 23.8° N).
     *
     * @var array<string, string>
     */
    private const LIGHT = [
        'north' => 'Soft, even daylight',
        'north-east' => 'Gentle morning light',
        'east' => 'Morning light',
        'south-east' => 'Morning sun, bright through the day',
        'south' => 'Sunlight through most of the day',
        'south-west' => 'Bright afternoons',
        'west' => 'Afternoon and evening light',
        'north-west' => 'Soft afternoon light',
    ];

    private function __construct(private Property $property) {}

    public static function for(Property $property): self
    {
        return new self($property);
    }

    public function kind(): string
    {
        return match ($this->property->fieldProfile()) {
            PropertyType::PROFILE_PLOT => self::KIND_PLOT,
            PropertyType::PROFILE_COMMERCIAL => self::KIND_SPACE,
            default => self::KIND_HOME,
        };
    }

    public function exploreLabel(): string
    {
        return match ($this->kind()) {
            self::KIND_PLOT => __('Explore this plot'),
            self::KIND_SPACE => __('Explore this space'),
            default => __('Explore this home'),
        };
    }

    public function lifeTitle(): string
    {
        return match ($this->kind()) {
            self::KIND_PLOT => __('The land'),
            self::KIND_SPACE => __('Working here'),
            default => __('Living here'),
        };
    }

    /**
     * Short size, e.g. "1,850 sq ft" or "5 Katha".
     */
    public function size(): ?string
    {
        if (blank($this->property->area_value) || (float) $this->property->area_value <= 0) {
            return null;
        }

        $unit = strtolower(trim((string) $this->property->area_unit));

        if ($unit !== 'sqft') {
            return AreaConverter::format($this->property->area_value, $unit);
        }

        return rtrim(rtrim(number_format((float) $this->property->area_value, 2, '.', ','), '0'), '.').' '.__('sq ft');
    }

    /**
     * Size framed by what the space is for, e.g. "1,850 sq ft of living space".
     */
    public function sizePhrase(): ?string
    {
        $size = $this->size();

        if ($size === null) {
            return null;
        }

        return match ($this->kind()) {
            self::KIND_PLOT => __(':size of land', ['size' => $size]),
            self::KIND_SPACE => __(':size of floor space', ['size' => $size]),
            default => __(':size of living space', ['size' => $size]),
        };
    }

    /**
     * The essential facts a card needs, in scanning order: rooms first, then size.
     *
     * @return list<string>
     */
    public function facts(): array
    {
        $isPlot = $this->kind() === self::KIND_PLOT;

        return array_values(array_filter([
            ! $isPlot && $this->property->bedrooms ? __(':count bed', ['count' => $this->property->bedrooms]) : null,
            ! $isPlot && $this->property->bathrooms ? __(':count bath', ['count' => $this->property->bathrooms]) : null,
            $this->size(),
        ]));
    }

    /**
     * Moments that help a visitor picture the place, each backed by a recorded field.
     *
     * @return list<array{title: string, detail: string|null}>
     */
    public function anchors(): array
    {
        $property = $this->property;
        $kind = $this->kind();
        $anchors = [];

        if ($facing = $this->facingLabel()) {
            $anchors[] = [
                'title' => __(':facing-facing', ['facing' => $facing]),
                'detail' => $kind === self::KIND_PLOT ? null : __(self::LIGHT[$property->facing]),
            ];
        }

        if ($kind === self::KIND_HOME && (int) $property->bedrooms >= 3) {
            $anchors[] = [
                'title' => trans_choice(':count bedroom|:count bedrooms', (int) $property->bedrooms, ['count' => $property->bedrooms]),
                'detail' => __('Room for family, guests or a study'),
            ];
        }

        if ($kind !== self::KIND_PLOT && (int) $property->balconies > 0) {
            $anchors[] = [
                'title' => trans_choice(':count balcony|:count balconies', (int) $property->balconies, ['count' => $property->balconies]),
                'detail' => __('Private outdoor space'),
            ];
        }

        if ($kind !== self::KIND_PLOT && $property->is_furnished) {
            $anchors[] = [
                'title' => __('Furnished'),
                'detail' => __('Move in without starting from scratch'),
            ];
        }

        if ($floor = $this->floorAnchor()) {
            $anchors[] = $floor;
        }

        if ((int) $property->parking_spaces > 0) {
            $anchors[] = [
                'title' => trans_choice('Parking for :count car|Parking for :count cars', (int) $property->parking_spaces, ['count' => $property->parking_spaces]),
                'detail' => null,
            ];
        }

        if ((int) $property->road_width_ft > 0) {
            $anchors[] = [
                'title' => __('On a :feet ft road', ['feet' => $property->road_width_ft]),
                'detail' => null,
            ];
        }

        return $anchors;
    }

    /**
     * Up to three short anchors for the reveal on a listing photograph.
     *
     * @return list<string>
     */
    public function glance(int $limit = 3): array
    {
        $property = $this->property;
        $isPlot = $this->kind() === self::KIND_PLOT;
        $floor = $property->floor_number;

        return array_slice(array_values(array_filter([
            ($facing = $this->facingLabel()) ? __(':facing-facing', ['facing' => $facing]) : null,
            ! $isPlot && (int) $property->balconies > 0
                ? trans_choice(':count balcony|:count balconies', (int) $property->balconies, ['count' => $property->balconies])
                : null,
            ! $isPlot && $property->is_furnished ? __('Furnished') : null,
            ! $isPlot && $floor !== null && (int) $floor >= 6 ? __('Floor :number', ['number' => $floor]) : null,
            (int) $property->parking_spaces > 0
                ? trans_choice('Parking for :count car|Parking for :count cars', (int) $property->parking_spaces, ['count' => $property->parking_spaces])
                : null,
            (int) $property->road_width_ft > 0 ? __('On a :feet ft road', ['feet' => $property->road_width_ft]) : null,
        ])), 0, $limit);
    }

    private function facingLabel(): ?string
    {
        $facing = $this->property->facing;

        if (blank($facing) || ! isset(self::LIGHT[$facing])) {
            return null;
        }

        return __(config('urbanhaven.facings.'.$facing, ucfirst($facing)));
    }

    /**
     * @return array{title: string, detail: string|null}|null
     */
    private function floorAnchor(): ?array
    {
        $floor = $this->property->floor_number;

        if ($this->kind() === self::KIND_PLOT || $floor === null || $floor === '') {
            return null;
        }

        return match (true) {
            (int) $floor === 0 => ['title' => __('Ground floor'), 'detail' => __('At street level')],
            (int) $floor >= 6 => ['title' => __('Floor :number', ['number' => $floor]), 'detail' => __('Raised well above the street')],
            default => null,
        };
    }
}
