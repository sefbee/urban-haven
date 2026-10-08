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
                'summary' => 'Top of the homepage: headline, property search and background photos.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Small label above the headline', 'type' => 'text', 'max' => 80, 'default' => 'Urban Haven Properties Ltd.'],
                    'title' => ['label' => 'Headline', 'type' => 'text', 'max' => 120, 'default' => 'Considered places to live in Dhaka.', 'required' => true],
                    'body' => ['label' => 'Supporting line', 'type' => 'textarea', 'max' => 300, 'default' => 'Search houses, apartments, and land across Dhaka at prices you can verify with our desk.', 'hint' => 'Also used as the homepage description in Google when no SEO default is set.'],
                    'cta_label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => 'Explore properties'],
                    'cta_url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => '/properties', 'hint' => 'A page on this website, starting with “/”.'],
                    'slide_seconds' => ['label' => 'Seconds per background photo', 'type' => 'number', 'min' => 3, 'max' => 20, 'default' => 7],
                ],
            ],
            'housing' => [
                'block' => 'home_housing',
                'label' => 'Housing & apartment listings',
                'summary' => 'Live residential apartment listings shown in the housing rail.',
                'source' => ['label' => 'Manage properties', 'route' => 'admin.properties.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Housing & Apartment Projects in Bangladesh', 'required' => true],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'View all'],
                    'limit' => ['label' => 'Number of properties', 'type' => 'number', 'min' => 1, 'max' => 16, 'default' => 8],
                ],
            ],
            'projects' => [
                'block' => 'home_projects',
                'label' => 'Featured developments',
                'summary' => 'Projects published by the team and marked for homepage placement.',
                'source' => ['label' => 'Manage projects', 'route' => 'admin.projects.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Featured Developments', 'required' => true],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'Explore considered developments from our team.'],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'View all projects'],
                    'limit' => ['label' => 'Number of projects', 'type' => 'number', 'min' => 1, 'max' => 12, 'default' => 6],
                ],
            ],
            'featured' => [
                'block' => 'home_featured',
                'label' => 'Featured properties',
                'summary' => 'Listings marked “Featured”, shown as a large carousel.',
                'source' => ['label' => 'Choose featured properties', 'route' => 'admin.properties.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Featured Properties', 'required' => true],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'A closer look at the places our team is featuring right now.'],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'See every property'],
                    'limit' => ['label' => 'Number of properties', 'type' => 'number', 'min' => 3, 'max' => 16, 'default' => 8],
                ],
            ],
            'trending' => [
                'block' => 'home_trending',
                'label' => 'Trending properties',
                'summary' => 'The most viewed live listings over the last 30 days.',
                'source' => ['label' => 'Manage properties', 'route' => 'admin.properties.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Trending Properties', 'required' => true],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'View All'],
                    'limit' => ['label' => 'Number of properties', 'type' => 'number', 'min' => 3, 'max' => 12, 'default' => 6],
                ],
            ],
            'locations' => [
                'block' => 'home_locations',
                'label' => 'Explore by location',
                'summary' => 'Areas with live listings, each with a cover photo.',
                'source' => ['label' => 'Manage areas', 'route' => 'admin.areas.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Explore Properties by Location', 'required' => true],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => ''],
                    'link_label' => ['label' => 'Map link text', 'type' => 'text', 'max' => 40, 'default' => 'See them on the map'],
                    'limit' => ['label' => 'Number of areas', 'type' => 'number', 'min' => 3, 'max' => 24, 'default' => 12],
                ],
            ],
            'types' => [
                'block' => 'home_types',
                'label' => 'Explore by property type',
                'summary' => 'Property types grouped under Residential and Commercial tabs.',
                'source' => ['label' => 'Manage property types', 'route' => 'admin.property-types.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Explore Real Estate in Bangladesh', 'required' => true],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'View all'],
                ],
            ],
            'latest' => [
                'block' => 'home_latest',
                'label' => 'Latest properties',
                'summary' => 'The newest live listings with For sale / For rent tabs.',
                'source' => ['label' => 'Manage properties', 'route' => 'admin.properties.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Latest Properties', 'required' => true],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'New to Urban Haven this week.'],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'See everything new'],
                    'limit' => ['label' => 'Number of properties', 'type' => 'number', 'min' => 3, 'max' => 16, 'default' => 8],
                ],
            ],
            'requirements' => [
                'block' => 'home_requirements',
                'label' => 'Property search callout',
                'summary' => 'The callout inviting visitors to tell the team what they need.',
                'fields' => [
                    'eyebrow' => ['label' => 'Small label', 'type' => 'text', 'max' => 60, 'default' => 'Personal property search'],
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Can’t find what you’re looking for?', 'required' => true],
                    'body' => ['label' => 'Supporting line', 'type' => 'textarea', 'max' => 300, 'default' => 'Tell us your preferred area, budget and needs. Our team will help you find a property that fits.'],
                    'cta_label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => 'Tell us what you need'],
                    'cta_url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => '/contact'],
                ],
            ],
            'why' => [
                'block' => 'about',
                'label' => 'Why Urban Haven',
                'summary' => 'A short statement about the company with reasons to choose you.',
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Why Urban Haven?', 'required' => true],
                    'body' => ['label' => 'Statement', 'type' => 'textarea', 'max' => 600, 'default' => 'One place to find Urban Haven properties, with the details you need to decide. The same team that publishes each listing answers your questions and walks you through the door.'],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'More about us'],
                    'link_url' => ['label' => 'Link', 'type' => 'path', 'max' => 255, 'default' => '/about'],
                    'items' => ['label' => 'Reasons', 'type' => 'repeater', 'max' => 6, 'default' => [], 'item_label' => 'reason', 'fields' => [
                        'icon' => ['label' => 'Icon', 'type' => 'icon', 'default' => 'check-circle'],
                        'title' => ['label' => 'Title', 'type' => 'text', 'max' => 80, 'required' => true],
                        'text' => ['label' => 'Short line', 'type' => 'textarea', 'max' => 200],
                    ]],
                ],
            ],
            'articles' => [
                'block' => 'home_articles',
                'label' => 'Latest articles',
                'summary' => 'The newest published articles from the blog.',
                'source' => ['label' => 'Manage articles', 'route' => 'admin.posts.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Latest Articles & Blog', 'required' => true],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'Practical notes on buying, renting and investing in property from the Urban Haven team.'],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'Read all articles'],
                    'limit' => ['label' => 'Number of articles', 'type' => 'number', 'min' => 1, 'max' => 9, 'default' => 3],
                    'videos_title' => ['label' => 'Property video heading', 'type' => 'text', 'max' => 80, 'default' => 'Property TV'],
                    'video_limit' => ['label' => 'Number of property videos', 'type' => 'number', 'min' => 1, 'max' => 9, 'default' => 3],
                ],
            ],
            'faq' => [
                'block' => 'home_faq',
                'label' => 'Frequently asked questions',
                'summary' => 'Visible FAQs with a link to the full FAQ page.',
                'source' => ['label' => 'Manage FAQs', 'route' => 'admin.faqs.index'],
                'fields' => [
                    'title' => ['label' => 'Heading', 'type' => 'text', 'max' => 120, 'default' => 'Frequently asked questions.', 'required' => true],
                    'subtitle' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'Answers to your questions, every step of the way.'],
                    'cta_label' => ['label' => 'Contact button text', 'type' => 'text', 'max' => 40, 'default' => 'Get in touch'],
                    'link_label' => ['label' => 'Link text', 'type' => 'text', 'max' => 40, 'default' => 'View all'],
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
     * Published content merged over defaults, so a missing field never blanks the site.
     *
     * @param  array<string, mixed>|null  $content
     * @return array<string, mixed>
     */
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
