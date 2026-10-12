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
                'summary' => 'Top section of the articles page with title, intro and category links.',
                'fields' => [
                    'eyebrow' => ['label' => 'Small label above the headline', 'type' => 'text', 'max' => 80, 'default' => 'Urban Haven Journal'],
                    'title' => ['label' => 'Headline', 'type' => 'text', 'max' => 140, 'default' => 'Explore News, Insights and Videos'],
                    'intro' => ['label' => 'Intro line', 'type' => 'textarea', 'max' => 300, 'default' => 'Ideas, places and perspectives shaping the way Bangladesh lives.'],
                ],
            ],
            'featured' => [
                'block' => 'articles_featured',
                'label' => 'Featured articles',
                'summary' => 'The top featured articles highlight section.',
                'source' => ['label' => 'Manage articles', 'route' => 'admin.posts.index'],
                'fields' => [
                    'limit' => ['label' => 'Number of featured articles to show', 'type' => 'number', 'min' => 1, 'max' => 5, 'default' => 5],
                ],
            ],
            'categories' => [
                'block' => 'articles_categories',
                'label' => 'Showcase categories',
                'summary' => 'Category-specific article lists.',
                'source' => ['label' => 'Manage article categories', 'route' => 'admin.post-categories.index'],
                'fields' => [
                    'limit' => ['label' => 'Number of categories to showcase', 'type' => 'number', 'min' => 1, 'max' => 6, 'default' => 6],
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
