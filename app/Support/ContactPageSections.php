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
                'summary' => 'Optional introductory copy and hero photo.',
                'fields' => [
                    'eyebrow' => ['label' => 'Small label above the headline', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Headline', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'intro' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                ],
            ],
            'channels' => [
                'block' => 'contact_channels',
                'label' => 'Contact methods',
                'summary' => 'Headings and descriptions for the phone, email, and office cards.',
                'fields' => [
                    'title' => ['label' => 'Section heading', 'type' => 'text', 'max' => 100, 'default' => ''],
                    'intro' => ['label' => 'Section description', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'phone_title' => ['label' => 'Phone card heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'phone_intro' => ['label' => 'Phone card description', 'type' => 'textarea', 'max' => 200, 'default' => ''],
                    'email_title' => ['label' => 'Email card heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'email_intro' => ['label' => 'Email card description', 'type' => 'textarea', 'max' => 200, 'default' => ''],
                    'office_title' => ['label' => 'Office card heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'office_intro' => ['label' => 'Office card description', 'type' => 'textarea', 'max' => 200, 'default' => ''],
                ],
            ],
            'enquiry' => [
                'block' => 'contact_enquiry',
                'label' => 'Enquiry form introduction',
                'summary' => 'Copy above the contact form.',
                'fields' => [
                    'eyebrow' => ['label' => 'Small label', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'intro' => ['label' => 'Description', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                ],
            ],
            'office' => [
                'block' => 'contact_office',
                'label' => 'Office details',
                'summary' => 'Office locations and operating hours.',
                'images' => true,
                'fields' => [
                    'title' => ['label' => 'Office details heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'address_label' => ['label' => 'Address label', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'hours_label' => ['label' => 'Business hours label', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'social_label' => ['label' => 'Social links label', 'type' => 'text', 'max' => 80, 'default' => ''],
                ],
            ],
            'direct' => [
                'block' => 'contact_direct',
                'label' => 'Direct contact',
                'summary' => 'Contact form fallback direct details.',
                'fields' => [
                    'title' => ['label' => 'Direct contact heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'intro' => ['label' => 'Direct contact intro', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                ],
            ],
            'map' => [
                'block' => 'contact_map',
                'label' => 'Office map',
                'summary' => 'Map card copy. The location comes from Contact settings.',
                'fields' => [
                    'title' => ['label' => 'Map card heading', 'type' => 'text', 'max' => 100, 'default' => ''],
                    'button' => ['label' => 'Directions button text', 'type' => 'text', 'max' => 50, 'default' => ''],
                ],
            ],
            'cta' => [
                'block' => 'contact_cta',
                'label' => 'Bottom Call to Action',
                'summary' => 'Bottom banner inviting visitors to schedule a consultation or browse properties.',
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 140, 'default' => ''],
                    'intro' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'primary_cta' => ['label' => 'Primary button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'primary_url' => ['label' => 'Primary button link', 'type' => 'path', 'max' => 255, 'default' => ''],
                    'secondary_cta' => ['label' => 'Secondary button text', 'type' => 'text', 'max' => 40, 'default' => ''],
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
            if (array_key_exists($field, $content ?? [])) {
                $value = $content[$field];
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
