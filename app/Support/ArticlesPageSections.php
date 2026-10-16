<?php

namespace App\Support;

use App\Models\CmsBlock;
use Illuminate\Database\Eloquent\Collection;

final class ArticlesPageSections
{
    public const ICONS = HomeSections::ICONS;

    public static function definitions(): array
    {
        return [
            'masthead' => [
                'block' => 'articles_masthead',
                'label' => 'Masthead / Header',
                'summary' => 'Editorial hero, introduction, calls to action and hero photography.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Small label above the headline', 'type' => 'text', 'max' => 80, 'default' => 'News & Insights'],
                    'title' => ['label' => 'Headline', 'type' => 'text', 'max' => 140, 'default' => 'Ideas Today for Better Places Tomorrow'],
                    'intro' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'Expert insights, market trends and practical guides to help you make smarter property decisions in Bangladesh.'],
                    'cta_label' => ['label' => 'Primary button text', 'type' => 'text', 'max' => 40, 'default' => 'Explore Articles'],
                    'cta_url' => ['label' => 'Primary button link', 'type' => 'path', 'max' => 255, 'default' => '/articles'],
                    'video_label' => ['label' => 'Optional video link text', 'type' => 'text', 'max' => 40, 'default' => ''],
                    'video_url' => ['label' => 'Optional video link', 'type' => 'path', 'max' => 255, 'default' => ''],
                ],
            ],
            'featured' => [
                'block' => 'articles_featured',
                'label' => 'Featured articles',
                'summary' => 'The top featured articles highlight section.',
                'source' => ['label' => 'Manage articles', 'route' => 'admin.posts.index'],
                'fields' => [
                    'post_id' => ['label' => 'Featured article', 'type' => 'article', 'default' => null],
                ],
            ],
            'categories' => [
                'block' => 'articles_categories',
                'label' => 'Showcase categories',
                'summary' => 'Category discovery cards and their displayed limit.',
                'source' => ['label' => 'Manage article categories', 'route' => 'admin.post-categories.index'],
                'fields' => [
                    'title' => ['label' => 'Section heading', 'type' => 'text', 'max' => 120, 'default' => 'Explore by category'],
                    'limit' => ['label' => 'Number of categories to show', 'type' => 'number', 'min' => 1, 'max' => 12, 'default' => 6],
                ],
            ],
            'latest' => [
                'block' => 'articles_latest',
                'label' => 'Latest articles',
                'summary' => 'Latest articles heading and number of stories shown beside the feature.',
                'source' => ['label' => 'Manage articles', 'route' => 'admin.posts.index'],
                'fields' => [
                    'title' => ['label' => 'Section heading', 'type' => 'text', 'max' => 120, 'default' => 'Latest Articles'],
                    'limit' => ['label' => 'Number of articles', 'type' => 'number', 'min' => 1, 'max' => 8, 'default' => 4],
                ],
            ],
            'grid' => [
                'block' => 'articles_grid',
                'label' => 'Article listing grid',
                'summary' => 'The main grid of articles.',
                'source' => ['label' => 'Manage articles', 'route' => 'admin.posts.index'],
                'fields' => [
                    'limit' => ['label' => 'Articles per page', 'type' => 'number', 'min' => 4, 'max' => 24, 'default' => 12],
                ],
            ],
            'promo' => [
                'block' => 'articles_promo',
                'label' => 'Mid-page Promo Banner',
                'summary' => 'Banner between featured articles and categories.',
                'images' => true,
                'fields' => [
                    'eyebrow' => ['label' => 'Small label above headline', 'type' => 'text', 'max' => 80, 'default' => 'Find your next address'],
                    'title' => ['label' => 'Banner headline', 'type' => 'text', 'max' => 140, 'default' => 'A better way to find your place.'],
                    'cta_label' => ['label' => 'Button text', 'type' => 'text', 'max' => 40, 'default' => 'Explore properties'],
                    'cta_url' => ['label' => 'Button link', 'type' => 'path', 'max' => 255, 'default' => '/properties'],
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
                    'label' => 'Articles page: '.$definition['label'],
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

        TaggedCache::flush(['cms', 'articlespage']);
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
        return TaggedCache::remember(['cms', 'articlespage'], 'articles-sections', 3600, fn (): array => self::blocks()
            ->filter(fn (CmsBlock $block): bool => $block->is_visible)
            ->map(fn (CmsBlock $block, string $section): array => self::resolve($section, $block->content))
            ->all());
    }
}
