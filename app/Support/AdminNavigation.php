<?php

namespace App\Support;

use App\Models\CmsPage;
use App\Models\Post;
use App\Models\User;

/**
 * The staff sidebar. Every entry is filtered by the signed-in user's permissions,
 * so a link is only shown when the screen behind it would not answer 403.
 */
final class AdminNavigation
{
    /**
     * @return array<string, list<array{pattern: string|list<string>, label: string, url: string, icon: string, matcher: ?\Closure}>>
     */
    public static function for(User $user): array
    {
        $can = fn (string $permission): bool => $user->hasPermission($permission);
        $owner = $user->isOwnerAdmin();
        $canCms = $can('cms.update') || $can('cms.create');

        $canReference = $user->can('reference.manage');

        $dynamicPages = [];
        if ($canCms) {
            try {
                $pages = CmsPage::query()->orderBy('title')->get();
                foreach ($pages as $page) {
                    if (in_array($page->slug, ['about', 'contact', 'blog'], true)
                        || in_array($page->template, ['about', 'contact'], true)) {
                        continue;
                    }

                    $url = route('admin.cms.edit', $page);
                    $dynamicPages[$page->id] = self::item(
                        'admin.cms.edit.'.$page->id,
                        $page->title,
                        $url,
                        $page->menu_icon ?: 'document',
                        fn () => request()->url() === $url
                    );
                }
            } catch (\Throwable $e) {
                // Ignore DB issues during setup/migration
            }
        }

        $orderedDynamicPages = [];
        if (isset($dynamicPages['contact'])) {
            $orderedDynamicPages[] = $dynamicPages['contact'];
        }
        if (isset($dynamicPages['about'])) {
            $orderedDynamicPages[] = $dynamicPages['about'];
        }
        foreach ($dynamicPages as $k => $p) {
            if ($k !== 'contact' && $k !== 'about') {
                $orderedDynamicPages[] = $p;
            }
        }

        $pagesGroup = [];
        if ($canCms) {
            $pagesGroup[] = self::item('admin.page-sections.*', 'Home', route('admin.page-sections.index', ['page' => 'home']), 'home', fn () => request()->route('page') === 'home');
            $pagesGroup[] = self::item('admin.page-sections.*', 'Contact', route('admin.page-sections.index', ['page' => 'contact']), 'phone', fn () => request()->route('page') === 'contact');
            $pagesGroup[] = self::item('admin.page-sections.*', 'About Urban Haven', route('admin.page-sections.index', ['page' => 'about']), 'user-group', fn () => request()->route('page') === 'about');
            $pagesGroup[] = self::item('admin.page-sections.*', 'Blog', route('admin.page-sections.index', ['page' => 'articles']), 'document', fn () => request()->route('page') === 'articles');
            foreach ($orderedDynamicPages as $dp) {
                $pagesGroup[] = $dp;
            }
            $pagesGroup[] = self::item('admin.faqs.*', 'FAQ page', route('admin.faqs.index'), 'info');
            if ($can('cms.publish')) {
                $pagesGroup[] = self::item('admin.menus.*', 'Menu management', route('admin.menus.index'), 'menu');
            }
            $pagesGroup[] = self::item(['admin.cms.index', 'admin.cms.create', 'admin.cms.store'], 'Custom pages', route('admin.cms.index'), 'document');
        }
        $articleGroup = [];
        if ($user->can('viewAny', Post::class)) {
            $articleGroup[] = self::item('admin.posts.*', 'Articles content', route('admin.posts.index'), 'list');
            $articleGroup[] = self::item('admin.post-categories.*', 'Article categories', route('admin.post-categories.index'), 'tag');
        }

        $groups = [
            'Dashboard' => [
                self::item('admin.dashboard', 'Overview', route('admin.dashboard'), 'dashboard'),
            ],
            'Properties' => [
                $can('property.view') || $can('property.update') ? self::item(['admin.properties.*', 'admin.units.*'], 'Properties', route('admin.properties.index'), 'grid') : null,
                $can('project.view') || $can('project.update') ? self::item('admin.projects.*', 'Projects', route('admin.projects.index'), 'building') : null,
                $canReference ? self::item('admin.areas.*', 'Areas', route('admin.areas.index'), 'pin') : null,
                $canReference ? self::item('admin.property-types.*', 'Property types', route('admin.property-types.index'), 'tag') : null,
                $canReference ? self::item('admin.amenities.*', 'Amenities', route('admin.amenities.index'), 'sparkle') : null,
            ],
            'Website pages' => $pagesGroup,
            'Articles' => $articleGroup,
            'SEO & Marketing' => [
                $canCms ? self::item('admin.site-urls', 'Pages & slugs', route('admin.site-urls'), 'link') : null,
                $can('cms.publish') ? self::item('admin.page-seo.*', 'Page SEO', route('admin.page-seo.index'), 'globe') : null,
            ],

            'Communication' => [
                $can('lead.view') ? self::item('admin.leads.*', 'Leads & enquiries', route('admin.leads.index'), 'inbox') : null,
                $can('lead.view') ? self::item('admin.follow-ups.*', 'Follow-ups', route('admin.follow-ups.index'), 'clock') : null,
                $can('lead.view') ? self::item('admin.visits.*', 'Site visits', route('admin.visits.index'), 'calendar') : null,
            ],
            'Website settings' => [
                $owner ? self::item('admin.settings.index', 'General setting', route('admin.settings.index'), 'settings') : null,
                $owner ? self::item('admin.settings.theme', 'Theme & colours', route('admin.settings.theme'), 'sparkle') : null,
                $owner ? self::item('admin.settings.contact', 'Contact & social', route('admin.settings.contact'), 'phone') : null,
                $owner ? self::item('admin.listing-display', 'Listing display', route('admin.listing-display'), 'map') : null,
                $owner ? self::item('admin.settings.analytics', 'Analytics & tracking', route('admin.settings.analytics'), 'compass') : null,
                $owner ? self::item('admin.settings.seo', 'SEO defaults', route('admin.settings.seo'), 'globe') : null,
                $owner ? self::item('admin.settings.copy', 'Website copy', route('admin.settings.copy'), 'document') : null,
            ],
            'Administration' => [
                $user->can('redirect.manage') ? self::item('admin.redirects.*', 'Redirects', route('admin.redirects.index'), 'arrow-right') : null,
                $user->can('audit.view') ? self::item('admin.audit.*', 'Activity log', route('admin.audit.index'), 'shield') : null,
                $user->can('viewAny', User::class) ? self::item('admin.staff.*', 'Users & roles', route('admin.staff.index'), 'users') : null,
            ],
        ];

        return array_filter(
            array_map(fn (array $items): array => array_values(array_filter($items)), $groups),
            fn (array $items): bool => $items !== [],
        );
    }

    /**
     * Label of the current screen's group and item, for the header breadcrumb.
     *
     * @param  array<string, list<array{pattern: string|list<string>, label: string, url: string, icon: string, matcher: ?\Closure}>>  $groups
     * @return array{group: string, item: array{pattern: string|list<string>, label: string, url: string, icon: string, matcher: ?\Closure}}|null
     */
    public static function current(array $groups): ?array
    {
        foreach ($groups as $group => $items) {
            foreach ($items as $item) {
                if (isset($item['matcher']) && $item['matcher']()) {
                    return ['group' => $group, 'item' => $item];
                }
            }
        }

        foreach ($groups as $group => $items) {
            foreach ($items as $item) {
                if (! isset($item['matcher']) && request()->routeIs(...(array) $item['pattern'])) {
                    return ['group' => $group, 'item' => $item];
                }
            }
        }

        return null;
    }

    /**
     * @param  string|list<string>  $pattern
     * @return array{pattern: string|list<string>, label: string, url: string, icon: string, matcher: ?\Closure}
     */
    private static function item(string|array $pattern, string $label, string $url, string $icon, ?\Closure $matcher = null): array
    {
        return ['pattern' => $pattern, 'label' => $label, 'url' => $url, 'icon' => $icon, 'matcher' => $matcher];
    }
}
