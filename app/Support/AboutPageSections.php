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
                    'eyebrow' => ['label' => 'Small label above the headline', 'type' => 'text', 'max' => 80, 'default' => 'A home is more than an address'],
                    'title' => ['label' => 'Headline', 'type' => 'textarea', 'max' => 200, 'default' => "Building Trust.\nCreating Better Places to Live.", 'required' => true],
                    'intro' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'Urban Haven Properties Ltd. helps people discover thoughtfully selected homes and investment opportunities through transparency, expertise and personal service.'],
                    'quote' => ['label' => 'Quote', 'type' => 'textarea', 'max' => 240, 'default' => 'We believe the right property is more than an address — it is the foundation for a better future.'],
                    'quote_kicker' => ['label' => 'Quote eyebrow', 'type' => 'text', 'max' => 80, 'default' => 'Our point of view'],
                    'quote_label' => ['label' => 'Quote attribution', 'type' => 'text', 'max' => 80, 'default' => 'The Urban Haven approach'],
                    'badge' => ['label' => 'Corner label', 'type' => 'text', 'max' => 100, 'default' => 'Made for your next chapter'],
                    'primary_cta' => ['label' => 'Primary button text', 'type' => 'text', 'max' => 40, 'default' => 'Discover Our Properties'],
                    'primary_url' => ['label' => 'Primary button link', 'type' => 'path', 'max' => 255, 'default' => '/properties'],
                    'secondary_cta' => ['label' => 'Secondary button text', 'type' => 'text', 'max' => 40, 'default' => 'Talk to our team'],
                    'secondary_url' => ['label' => 'Secondary button link', 'type' => 'path', 'max' => 255, 'default' => '/contact'],
                ],
            ],
            'story' => [
                'block' => 'about_story',
                'label' => 'Our Story',
                'summary' => 'The story section.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Story label', 'type' => 'text', 'max' => 80, 'default' => 'Our Story'],
                    'title' => ['label' => 'Story heading', 'type' => 'text', 'max' => 140, 'default' => 'A New Standard in Property Experience'],
                    'quote' => ['label' => 'Story quote', 'type' => 'textarea', 'max' => 240, 'default' => ''],
                    'quote_kicker' => ['label' => 'Story quote eyebrow', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'quote_label' => ['label' => 'Story quote attribution', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'cta_label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => 'Discover Our Properties'],
                    'cta_url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => '/properties'],
                    'stat_one_value' => ['label' => 'Stat 1 Value', 'type' => 'text', 'max' => 40, 'default' => '10+'],
                    'stat_one_label' => ['label' => 'Stat 1 Label', 'type' => 'text', 'max' => 100, 'default' => 'Years of Combined Experience'],
                    'stat_two_value' => ['label' => 'Stat 2 Value', 'type' => 'text', 'max' => 40, 'default' => '25+'],
                    'stat_two_label' => ['label' => 'Stat 2 Label', 'type' => 'text', 'max' => 100, 'default' => 'Property Opportunities'],
                    'stat_three_value' => ['label' => 'Stat 3 Value', 'type' => 'text', 'max' => 40, 'default' => '500+'],
                    'stat_three_label' => ['label' => 'Stat 3 Label', 'type' => 'text', 'max' => 100, 'default' => 'Client Consultations'],
                    'stat_four_value' => ['label' => 'Stat 4 Value', 'type' => 'text', 'max' => 40, 'default' => '100%'],
                    'stat_four_label' => ['label' => 'Stat 4 Label', 'type' => 'text', 'max' => 100, 'default' => 'Commitment to Transparency'],
                ],
            ],
            'purpose' => [
                'block' => 'about_purpose',
                'label' => 'Our Purpose',
                'summary' => 'Mission and Vision statement.',
                'fields' => [
                    'eyebrow' => ['label' => 'Purpose label', 'type' => 'text', 'max' => 80, 'default' => 'Our Purpose'],
                    'title' => ['label' => 'Purpose heading', 'type' => 'text', 'max' => 140, 'default' => 'Driven by Purpose, Guided by Trust'],
                    'mission_title' => ['label' => 'Mission title', 'type' => 'text', 'max' => 80, 'default' => 'Mission'],
                    'mission_text' => ['label' => 'Mission text', 'type' => 'textarea', 'max' => 300, 'default' => 'To simplify the property journey through expert guidance, transparent communication and carefully selected real-estate opportunities.'],
                    'vision_title' => ['label' => 'Vision title', 'type' => 'text', 'max' => 80, 'default' => 'Vision'],
                    'vision_text' => ['label' => 'Vision text', 'type' => 'textarea', 'max' => 300, 'default' => 'To become a trusted real-estate name in Bangladesh, known for quality properties, lasting relationships and responsible growth.'],
                ],
            ],
            'values' => [
                'block' => 'about_values',
                'label' => 'Values',
                'summary' => 'The four core values.',
                'fields' => [
                    'eyebrow' => ['label' => 'Values label', 'type' => 'text', 'max' => 80, 'default' => 'What Guides Us'],
                    'title' => ['label' => 'Values heading', 'type' => 'text', 'max' => 140, 'default' => 'Values Behind Every Decision'],
                    'intro' => ['label' => 'Values introduction', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'value_one_icon' => ['label' => 'First value icon', 'type' => 'select', 'options' => self::ICONS, 'default' => 'shield'],
                    'value_one_title' => ['label' => 'First value heading', 'type' => 'text', 'max' => 80, 'default' => 'Integrity'],
                    'value_one_text' => ['label' => 'First value text', 'type' => 'textarea', 'max' => 240, 'default' => 'We communicate clearly and act responsibly.'],
                    'value_two_icon' => ['label' => 'Second value icon', 'type' => 'select', 'options' => self::ICONS, 'default' => 'users'],
                    'value_two_title' => ['label' => 'Second value heading', 'type' => 'text', 'max' => 80, 'default' => 'Client First'],
                    'value_two_text' => ['label' => 'Second value text', 'type' => 'textarea', 'max' => 240, 'default' => 'Every recommendation begins with your needs.'],
                    'value_three_icon' => ['label' => 'Third value icon', 'type' => 'select', 'options' => self::ICONS, 'default' => 'sparkle'],
                    'value_three_title' => ['label' => 'Third value heading', 'type' => 'text', 'max' => 80, 'default' => 'Quality'],
                    'value_three_text' => ['label' => 'Third value text', 'type' => 'textarea', 'max' => 240, 'default' => 'We focus on properties with lasting value.'],
                    'value_four_icon' => ['label' => 'Fourth value icon', 'type' => 'select', 'options' => self::ICONS, 'default' => 'refresh'],
                    'value_four_title' => ['label' => 'Fourth value heading', 'type' => 'text', 'max' => 80, 'default' => 'Long-Term Partnership'],
                    'value_four_text' => ['label' => 'Fourth value text', 'type' => 'textarea', 'max' => 240, 'default' => 'Our relationship continues beyond the transaction.'],
                ],
            ],
            'choose' => [
                'block' => 'about_choose',
                'label' => 'Why Choose Us',
                'summary' => 'Reasons to choose us.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Why Choose Us label', 'type' => 'text', 'max' => 80, 'default' => 'Why Choose Us'],
                    'title' => ['label' => 'Why Choose Us heading', 'type' => 'text', 'max' => 140, 'default' => 'Property Guidance You Can Rely On'],
                    'list' => ['label' => 'Why Choose Us list (one per line)', 'type' => 'textarea', 'max' => 500, 'default' => "Carefully selected property portfolio\nClear and transparent communication\nDedicated property consultants\nSupport from enquiry to handover\nLocal market knowledge"],
                    'cta_label' => ['label' => 'Why Choose button text', 'type' => 'text', 'max' => 40, 'default' => 'Talk to Our Team'],
                    'cta_url' => ['label' => 'Why Choose button link', 'type' => 'path', 'max' => 255, 'default' => '/contact'],
                ],
            ],
            'approach' => [
                'block' => 'about_approach',
                'label' => 'Our Approach',
                'summary' => 'The four step approach.',
                'fields' => [
                    'eyebrow' => ['label' => 'Approach label', 'type' => 'text', 'max' => 80, 'default' => 'Our Approach'],
                    'title' => ['label' => 'Approach heading', 'type' => 'text', 'max' => 140, 'default' => 'A Simpler Way to Find Your Property'],
                    'approach_one_title' => ['label' => 'Step 1 title', 'type' => 'text', 'max' => 80, 'default' => 'Understand'],
                    'approach_one_text' => ['label' => 'Step 1 text', 'type' => 'textarea', 'max' => 240, 'default' => 'We listen to your goals, preferences and budget.'],
                    'approach_two_title' => ['label' => 'Step 2 title', 'type' => 'text', 'max' => 80, 'default' => 'Recommend'],
                    'approach_two_text' => ['label' => 'Step 2 text', 'type' => 'textarea', 'max' => 240, 'default' => 'We shortlist relevant property opportunities.'],
                    'approach_three_title' => ['label' => 'Step 3 title', 'type' => 'text', 'max' => 80, 'default' => 'Experience'],
                    'approach_three_text' => ['label' => 'Step 3 text', 'type' => 'textarea', 'max' => 240, 'default' => 'We arrange visits and provide complete information.'],
                    'approach_four_title' => ['label' => 'Step 4 title', 'type' => 'text', 'max' => 80, 'default' => 'Support'],
                    'approach_four_text' => ['label' => 'Step 4 text', 'type' => 'textarea', 'max' => 240, 'default' => 'We guide you through the decision and handover.'],
                ],
            ],
            'cta' => [
                'block' => 'about_cta',
                'label' => 'Call to Action',
                'summary' => 'Bottom call to action.',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text', 'max' => 80, 'default' => 'Your next chapter'],
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 140, 'default' => 'Let’s Build Your Property Journey Together.'],
                    'body' => ['label' => 'Body text', 'type' => 'textarea', 'max' => 300, 'default' => 'Speak with our team and discover an opportunity that matches your goals.'],
                    'label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => 'Book a Consultation'],
                    'url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => '/contact'],
                    'secondary_label' => ['label' => 'Secondary button text', 'type' => 'text', 'max' => 40, 'default' => 'Contact Us'],
                    'secondary_url' => ['label' => 'Secondary button link', 'type' => 'path', 'max' => 255, 'default' => '/contact'],
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
        return array_map(fn (array $field): mixed => $field['default'] ?? null, self::definition($section)['fields']);
    }

    public static function resolve(string $section, ?array $content): array
    {
        $defaults = self::defaults($section);

        foreach ($defaults as $field => $default) {
            $value = $content[$field] ?? null;
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
