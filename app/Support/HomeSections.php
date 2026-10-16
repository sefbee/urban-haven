<?php

namespace App\Support;

use App\Models\CmsBlock;
use Illuminate\Database\Eloquent\Collection;

/**
 * Every homepage section, its editable fields and their built-in defaults. Each section
 * is stored as one CMS block, so order, visibility and drafts reuse the block workflow.
 */
final class HomeSections
{
    public const ICONS = ['home', 'building', 'key', 'shield', 'check-circle', 'users', 'user', 'phone', 'map', 'pin', 'calendar', 'clock', 'sparkle', 'compass', 'document', 'tag', 'calculator', 'heart', 'globe', 'search'];

    /**
     * @return array<string, array{block: string, label: string, summary: string, source?: array{label: string, route: string}, images?: bool, fields: array<string, array<string, mixed>>}>
     */
    public static function definitions(): array
    {
        return [
            'hero' => [
                'block' => 'hero',
                'label' => 'Hero',
                'summary' => 'Top of the homepage: property search and background photos.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Small label above the headline', 'type' => 'text', 'max' => 80, 'default' => 'Urban Haven Properties Ltd.', 'editable' => false],
                    'title' => ['label' => 'Headline', 'type' => 'text', 'max' => 120, 'default' => 'Considered places to live in Dhaka.', 'editable' => false],
                    'body' => ['label' => 'Supporting line', 'type' => 'textarea', 'max' => 300, 'default' => 'Search houses, apartments, and land across Dhaka at prices you can verify with our desk.', 'editable' => false],
                    'cta_label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'cta_url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => '', 'hint' => 'A page on this website, starting with “/”.'],
                    'slide_seconds' => ['label' => 'Seconds per background photo', 'type' => 'number', 'min' => 3, 'max' => 20, 'default' => 7],
                ],
            ],
            'housing' => [
                'block' => 'home_housing',
                'label' => 'Housing & apartment listings',
                'summary' => 'Live residential apartment listings shown in the housing rail.',
                'source' => ['label' => 'Manage properties', 'route' => 'admin.properties.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'limit' => ['label' => 'Number of properties', 'type' => 'number', 'min' => 1, 'max' => 16, 'default' => 8],
                ],
            ],
            'projects' => [
                'block' => 'home_projects',
                'label' => 'Featured developments',
                'summary' => 'Projects published by the team and marked for homepage placement.',
                'source' => ['label' => 'Manage projects', 'route' => 'admin.projects.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'limit' => ['label' => 'Number of projects', 'type' => 'number', 'min' => 1, 'max' => 12, 'default' => 6],
                ],
            ],
            'featured' => [
                'block' => 'home_featured',
                'label' => 'Featured properties',
                'summary' => 'Listings marked “Featured”, shown as a large carousel.',
                'source' => ['label' => 'Choose featured properties', 'route' => 'admin.properties.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'limit' => ['label' => 'Number of properties', 'type' => 'number', 'min' => 3, 'max' => 16, 'default' => 8],
                ],
            ],
            'trending' => [
                'block' => 'home_trending',
                'label' => 'Trending properties',
                'summary' => 'The most viewed live listings over the last 30 days.',
                'source' => ['label' => 'Manage properties', 'route' => 'admin.properties.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'limit' => ['label' => 'Number of properties', 'type' => 'number', 'min' => 3, 'max' => 12, 'default' => 6],
                ],
            ],
            'locations' => [
                'block' => 'home_locations',
                'label' => 'Explore by location',
                'summary' => 'Areas with live listings, each with a cover photo.',
                'source' => ['label' => 'Manage areas', 'route' => 'admin.areas.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'link_label' => ['label' => 'Map link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'limit' => ['label' => 'Number of areas', 'type' => 'number', 'min' => 3, 'max' => 24, 'default' => 12],
                ],
            ],
            'types' => [
                'block' => 'home_types',
                'label' => 'Explore by property type',
                'summary' => 'Property types grouped under Residential and Commercial tabs.',
                'source' => ['label' => 'Manage property types', 'route' => 'admin.property-types.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                ],
            ],
            'latest' => [
                'block' => 'home_latest',
                'label' => 'Latest properties',
                'summary' => 'The newest live listings with For sale / For rent tabs.',
                'source' => ['label' => 'Manage properties', 'route' => 'admin.properties.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'limit' => ['label' => 'Number of properties', 'type' => 'number', 'min' => 3, 'max' => 16, 'default' => 8],
                ],
            ],
            'requirements' => [
                'block' => 'home_requirements',
                'label' => 'Property search callout',
                'summary' => 'The callout inviting visitors to tell the team what they need.',
                'fields' => [
                    'eyebrow' => ['label' => 'Small label', 'type' => 'text', 'max' => 60, 'default' => ''],
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'body' => ['label' => 'Supporting line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'cta_label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'cta_url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => ''],
                ],
            ],
            'why' => [
                'block' => 'about',
                'label' => 'Why Urban Haven',
                'summary' => 'A short statement about the company with reasons to choose you.',
                'fields' => [
                    'eyebrow' => ['label' => 'Small label', 'type' => 'text', 'max' => 60, 'default' => 'Our Advantage'],
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Why Urban Haven?'],
                    'body' => ['label' => 'Statement', 'type' => 'textarea', 'max' => 600, 'default' => 'One place to find verified properties in Dhaka with the details you need to decide. The same team that publishes each listing answers your calls, verifies title documentation, and walks you through the door.'],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'More about us'],
                    'link_url' => ['label' => 'Link', 'type' => 'path', 'max' => 255, 'default' => '/about'],
                    'items' => ['label' => 'Reasons', 'type' => 'repeater', 'max' => 6, 'default' => [
                        ['icon' => 'home', 'title' => 'Verified Listings', 'text' => 'All properties are checked and documented, so you get accurate information you can trust.'],
                        ['icon' => 'users', 'title' => 'Expert Team', 'text' => 'Professional guidance at every step.'],
                        ['icon' => 'document', 'title' => 'Clear Information', 'text' => 'Accurate details you can trust.'],
                        ['icon' => 'phone', 'title' => 'Personal Support', 'text' => "We're here from first call to final step."],
                    ], 'item_label' => 'reason', 'fields' => [
                        'icon' => ['label' => 'Icon', 'type' => 'icon', 'default' => 'check-circle', 'required' => true],
                        'title' => ['label' => 'Title', 'type' => 'text', 'max' => 80, 'required' => true],
                        'text' => ['label' => 'Description', 'type' => 'textarea', 'max' => 200, 'required' => true],
                    ]],
                ],
            ],
            'articles' => [
                'block' => 'home_articles',
                'label' => 'Latest articles',
                'summary' => 'The newest published articles from the blog.',
                'source' => ['label' => 'Manage articles', 'route' => 'admin.posts.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'videos_title' => ['label' => 'Property video heading', 'type' => 'text', 'max' => 80, 'default' => ''],
                    'video_limit' => ['label' => 'Number of property videos', 'type' => 'number', 'min' => 1, 'max' => 9, 'default' => 3],
                ],
            ],
            'faq' => [
                'block' => 'home_faq',
                'label' => 'Frequently asked questions',
                'summary' => 'Visible FAQs with a link to the full FAQ page.',
                'source' => ['label' => 'Manage FAQs', 'route' => 'admin.faqs.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => ''],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'cta_label' => ['label' => 'Contact button text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'limit' => ['label' => 'Number of questions', 'type' => 'number', 'min' => 1, 'max' => 12, 'default' => 5],
                ],
            ],
        ];
    }

    /**
     * Sections hidden until someone switches them on.
     *
     * @var list<string>
     */
    private const HIDDEN_BY_DEFAULT = ['why', 'projects'];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function definition(string $section): array
    {
        return self::definitions()[$section] ?? abort(404);
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }

    /**
     * @return list<string>
     */
    public static function blockKeys(): array
    {
        return array_column(self::definitions(), 'block');
    }

    /**
     * Section blocks in display order, created with defaults when missing.
     *
     * @return Collection<string, CmsBlock>
     */
    public static function blocks(): Collection
    {
        $definitions = self::definitions();
        $existing = CmsBlock::query()->with('media')->whereIn('key', self::blockKeys())->get()->keyBy('key');

        foreach ($definitions as $section => $definition) {
            if (! $existing->has($definition['block'])) {
                $existing->put($definition['block'], CmsBlock::query()->create([
                    'key' => $definition['block'],
                    'label' => 'Homepage: '.$definition['label'],
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

    /**
     * Rewrite sort orders in steps of ten so moves never collide.
     *
     * @param  list<string>  $sections
     */
    public static function reorder(array $sections): void
    {
        $blocks = self::blocks();

        foreach (array_values($sections) as $position => $section) {
            $block = $blocks->get($section);
            if ($block && $block->sort_order !== ($position + 1) * 10) {
                $block->forceFill(['sort_order' => ($position + 1) * 10, 'updated_at' => now()])->save();
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(string $section): array
    {
        return array_map(fn (array $field): mixed => $field['default'] ?? null, self::definition($section)['fields']);
    }

    /**
     * Published content merged over defaults, while explicit blanks remain intentional.
     *
     * @param  array<string, mixed>|null  $content
     * @return array<string, mixed>
     */
    public static function resolve(string $section, ?array $content): array
    {
        $defaults = self::defaults($section);
        $content ??= [];

        foreach ($defaults as $field => $default) {
            $value = $content[$field] ?? null;
            if ($section === 'why') {
                if ($field === 'items') {
                    $items = is_array($value) ? array_filter($value, fn (mixed $item): bool => is_array($item)) : [];
                    $items = array_map(function (array $item): array {
                        foreach (['title', 'text'] as $textField) {
                            $item[$textField] = is_string($item[$textField] ?? null) ? $item[$textField] : '';
                        }

                        if (! in_array($item['icon'] ?? null, self::ICONS, true)) {
                            $item['icon'] = 'check-circle';
                        }

                        return $item;
                    }, $items);
                    $items = array_filter($items, fn (array $item): bool => filled($item['title']) || filled($item['text']));
                    $defaults[$field] = array_key_exists($field, $content) ? array_values($items) : $default;
                } elseif (array_key_exists($field, $content)) {
                    $defaults[$field] = is_string($value) ? $value : null;
                }

                continue;
            }

            if ($value !== null && $value !== '' && $value !== []) {
                $defaults[$field] = $value;
            }
        }

        return $defaults;
    }

    /**
     * Visible sections in display order with resolved content, for the public homepage.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function visible(): array
    {
        return TaggedCache::remember(['cms', 'homepage'], 'home-sections', 3600, fn (): array => self::blocks()
            ->filter(fn (CmsBlock $block): bool => $block->is_visible)
            ->map(fn (CmsBlock $block, string $section): array => self::resolve($section, $block->content))
            ->all());
    }
}
