<?php

namespace App\Support;

use App\Models\CmsBlock;
use Illuminate\Database\Eloquent\Collection;

final class ContactPageSections
{
    public const ICONS = HomeSections::ICONS;

    public static function definitions(): array
    {
        return [
            'hero' => [
                'block' => 'contact_hero',
                'label' => 'Hero / General contact',
                'summary' => 'Top section with main heading, contact details and the contact form.',
                'fields' => [
                    'eyebrow' => ['label' => 'Small label above the headline', 'type' => 'text', 'max' => 80, 'default' => 'Contact Us'],
                    'title' => ['label' => 'Headline', 'type' => 'text', 'max' => 140, 'default' => 'Talk to our sales team'],
                    'intro' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'Call, WhatsApp or send a message. The people who manage our homes will get back to you.'],
                    'heading' => ['label' => 'Contact details heading', 'type' => 'text', 'max' => 80, 'default' => 'General Contact'],
                ],
            ],
            'office' => [
                'block' => 'contact_office',
                'label' => 'Office details',
                'summary' => 'Office locations and operating hours.',
                'images' => true,
                'fields' => [
                    'title' => ['label' => 'Office details heading', 'type' => 'text', 'max' => 80, 'default' => 'Corporate Office'],
                ],
            ],
            'direct' => [
                'block' => 'contact_direct',
                'label' => 'Direct contact',
                'summary' => 'Contact form fallback direct details.',
                'fields' => [
                    'title' => ['label' => 'Direct contact heading', 'type' => 'text', 'max' => 80, 'default' => 'Direct Contact'],
                    'intro' => ['label' => 'Direct contact intro', 'type' => 'textarea', 'max' => 300, 'default' => 'Reach out to our agents directly for immediate assistance with any property inquiries.'],
                ],
            ],
            'cta' => [
                'block' => 'contact_cta',
                'label' => 'Bottom Call to Action',
                'summary' => 'Bottom banner inviting visitors to schedule a consultation or browse properties.',
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 140, 'default' => 'Ready to Make Your Next Property Move?'],
                    'intro' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'Schedule a confidential consultation with our real-estate team and let us guide you home.'],
                    'primary_cta' => ['label' => 'Primary button text', 'type' => 'text', 'max' => 40, 'default' => 'Book a Consultation'],
                    'primary_url' => ['label' => 'Primary button link', 'type' => 'path', 'max' => 255, 'default' => '#page-form-heading'],
                    'secondary_cta' => ['label' => 'Secondary button text', 'type' => 'text', 'max' => 40, 'default' => 'Browse Properties'],
                    'secondary_url' => ['label' => 'Secondary button link', 'type' => 'path', 'max' => 255, 'default' => '/properties'],
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
                    'label' => 'Contact page: '.$definition['label'],
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

        TaggedCache::flush(['cms', 'contactpage']);
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
        return TaggedCache::remember(['cms', 'contactpage'], 'contact-sections', 3600, fn (): array => self::blocks()
            ->filter(fn (CmsBlock $block): bool => $block->is_visible)
            ->map(fn (CmsBlock $block, string $section): array => self::resolve($section, $block->content))
            ->all());
    }
}
