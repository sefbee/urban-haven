<?php

namespace App\Support;

use App\Models\CmsBlock;
use Illuminate\Database\Eloquent\Collection;

final class AboutPageSections
{
    public const ICONS = HomeSections::ICONS;

    public static function definitions(): array
    {
        return [
            'hero' => [
                'block' => 'about_hero',
                'label' => 'Hero',
                'summary' => 'Top of the about page: headline, intro, quote, and background photos.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Small label above the headline', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Headline', 'type' => 'textarea', 'max' => 200, 'default' => ''],
                    'intro' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'quote' => ['label' => 'Quote', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'quote_kicker' => ['label' => 'Quote eyebrow', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'quote_label' => ['label' => 'Quote attribution', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'badge' => ['label' => 'Corner label', 'type' => 'text', 'max' => 100, 'default' => ''],
                    'primary_cta' => ['label' => 'Primary button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'primary_url' => ['label' => 'Primary button link', 'type' => 'path', 'max' => 255, 'default' => ''],
                    'secondary_cta' => ['label' => 'Secondary button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'secondary_url' => ['label' => 'Secondary button link', 'type' => 'path', 'max' => 255, 'default' => ''],
                ],
            ],
            'story' => [
                'block' => 'about_story',
                'label' => 'Our Story',
                'summary' => 'The story section.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Story label', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Story heading', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'quote' => ['label' => 'Story quote', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'quote_kicker' => ['label' => 'Story quote eyebrow', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'quote_label' => ['label' => 'Story quote attribution', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'cta_label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'cta_url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => ''],
                    'stat_one_value' => ['label' => 'Stat 1 Value', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'stat_one_label' => ['label' => 'Stat 1 Label', 'type' => 'text', 'max' => 100, 'default' => ''],
                    'stat_two_value' => ['label' => 'Stat 2 Value', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'stat_two_label' => ['label' => 'Stat 2 Label', 'type' => 'text', 'max' => 100, 'default' => ''],
                    'stat_three_value' => ['label' => 'Stat 3 Value', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'stat_three_label' => ['label' => 'Stat 3 Label', 'type' => 'text', 'max' => 100, 'default' => ''],
                    'stat_four_value' => ['label' => 'Stat 4 Value', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'stat_four_label' => ['label' => 'Stat 4 Label', 'type' => 'text', 'max' => 100, 'default' => ''],
                ],
            ],
            'purpose' => [
                'block' => 'about_purpose',
                'label' => 'Our Purpose',
                'summary' => 'Mission and Vision statement.',
                'fields' => [
                    'eyebrow' => ['label' => 'Purpose label', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Purpose heading', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'mission_title' => ['label' => 'Mission title', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'mission_text' => ['label' => 'Mission text', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'vision_title' => ['label' => 'Vision title', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'vision_text' => ['label' => 'Vision text', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                ],
            ],
            'values' => [
                'block' => 'about_values',
                'label' => 'Values',
                'summary' => 'The four core values.',
                'fields' => [
                    'eyebrow' => ['label' => 'Values label', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Values heading', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'intro' => ['label' => 'Values introduction', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'value_one_icon' => ['label' => 'First value icon', 'type' => 'select', 'options' => array_combine(self::ICONS, self::ICONS), 'default' => ''],
                    'value_one_title' => ['label' => 'First value heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'value_one_text' => ['label' => 'First value text', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'value_two_icon' => ['label' => 'Second value icon', 'type' => 'select', 'options' => array_combine(self::ICONS, self::ICONS), 'default' => ''],
                    'value_two_title' => ['label' => 'Second value heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'value_two_text' => ['label' => 'Second value text', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'value_three_icon' => ['label' => 'Third value icon', 'type' => 'select', 'options' => array_combine(self::ICONS, self::ICONS), 'default' => ''],
                    'value_three_title' => ['label' => 'Third value heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'value_three_text' => ['label' => 'Third value text', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'value_four_icon' => ['label' => 'Fourth value icon', 'type' => 'select', 'options' => array_combine(self::ICONS, self::ICONS), 'default' => ''],
                    'value_four_title' => ['label' => 'Fourth value heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'value_four_text' => ['label' => 'Fourth value text', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                ],
            ],
            'choose' => [
                'block' => 'about_choose',
                'label' => 'Why Choose Us',
                'summary' => 'Reasons to choose us.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Why Choose Us label', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Why Choose Us heading', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'list' => ['label' => 'Why Choose Us list (one per line)', 'type' => 'textarea', 'max' => 500, 'default' => ''],
                    'cta_label' => ['label' => 'Why Choose button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'cta_url' => ['label' => 'Why Choose button link', 'type' => 'path', 'max' => 255, 'default' => ''],
                ],
            ],
            'approach' => [
                'block' => 'about_approach',
                'label' => 'Our Approach',
                'summary' => 'The four step approach.',
                'fields' => [
                    'eyebrow' => ['label' => 'Approach label', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Approach heading', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'approach_one_title' => ['label' => 'Step 1 title', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'approach_one_text' => ['label' => 'Step 1 text', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'approach_two_title' => ['label' => 'Step 2 title', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'approach_two_text' => ['label' => 'Step 2 text', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'approach_three_title' => ['label' => 'Step 3 title', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'approach_three_text' => ['label' => 'Step 3 text', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'approach_four_title' => ['label' => 'Step 4 title', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'approach_four_text' => ['label' => 'Step 4 text', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                ],
            ],
            'cta' => [
                'block' => 'about_cta',
                'label' => 'Call to Action',
                'summary' => 'Bottom call to action.',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'body' => ['label' => 'Body text', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => ''],
                    'secondary_label' => ['label' => 'Secondary button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'secondary_url' => ['label' => 'Secondary button link', 'type' => 'path', 'max' => 255, 'default' => ''],
                ],
            ],
        ];
    }

    private const HIDDEN_BY_DEFAULT = [];

    public static function definition(string $section): array
    {
        return self::definitions()[$section] ?? abort(404);
    }

    public static function keys(): array
    {
        return array_keys(self::definitions());
    }

    public static function blockKeys(): array
    {
        return array_column(self::definitions(), 'block');
    }

    public static function blocks(): Collection
    {
        $definitions = self::definitions();
        $existing = CmsBlock::query()->with('media')->whereIn('key', self::blockKeys())->get()->keyBy('key');

        foreach ($definitions as $section => $definition) {
            if (! $existing->has($definition['block'])) {
                $existing->put($definition['block'], CmsBlock::query()->create([
                    'key' => $definition['block'],
                    'label' => 'About page: '.$definition['label'],
                    'content' => self::defaults($section),
                    'is_visible' => ! in_array($section, self::HIDDEN_BY_DEFAULT, true),
                    'sort_order' => (array_search($section, array_keys($definitions), true) + 1) * 10,
                    'updated_at' => now(),
                ]));
            }
        }

        $orders = $existing->pluck('sort_order');
        if ($orders->unique()->count() !== $orders->count()) {
            foreach (array_keys($definitions) as $position => $section) {
                $existing->get($definitions[$section]['block'])->forceFill(['sort_order' => ($position + 1) * 10])->save();
            }
        }

        $ordered = new Collection;
        $sorted = collect($definitions)
            ->map(fn (array $definition, string $section): array => ['section' => $section, 'block' => $existing->get($definition['block'])])
            ->sortBy(fn (array $row): array => [$row['block']->sort_order, array_search($row['section'], array_keys($definitions), true)]);

        foreach ($sorted as $row) {
            $ordered->put($row['section'], $row['block']);
        }

        return $ordered;
    }

    public static function reorder(array $sections): void
    {
        $blocks = self::blocks();

        foreach (array_values($sections) as $position => $section) {
            $block = $blocks->get($section);
            if ($block && $block->sort_order !== ($position + 1) * 10) {
                $block->forceFill(['sort_order' => ($position + 1) * 10, 'updated_at' => now()])->save();
            }
        }

        TaggedCache::flush(['cms', 'aboutpage']);
    }

    public static function defaults(string $section): array
    {
        return array_map(fn (array $field): mixed => in_array($field['type'], ['text', 'textarea', 'path', 'select'], true)
            ? ''
            : ($field['default'] ?? null), self::definition($section)['fields']);
    }

    public static function resolve(string $section, ?array $content): array
    {
        $definition = self::definition($section);
        $defaults = self::defaults($section);

        foreach ($defaults as $field => $default) {
            $value = $content[$field] ?? null;

            if ($definition['fields'][$field]['type'] === 'select' && is_numeric($value)) {
                $value = self::ICONS[(int) $value] ?? '';
            }

            if ($value !== null && $value !== '' && $value !== []) {
                $defaults[$field] = $value;
            }
        }

        return $defaults;
    }

    public static function visible(): array
    {
        return TaggedCache::remember(['cms', 'aboutpage'], 'about-sections', 3600, fn (): array => self::blocks()
            ->filter(fn (CmsBlock $block): bool => $block->is_visible)
            ->map(fn (CmsBlock $block, string $section): array => self::resolve($section, $block->content))
            ->all());
    }
}
