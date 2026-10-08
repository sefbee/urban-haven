<?php

namespace App\Support;

use App\Models\Post;
use App\Models\User;

/**
 * The staff sidebar. Every entry is filtered by the signed-in user's permissions,
 * so a link is only shown when the screen behind it would not answer 403.
 */
final class AdminNavigation
{
    /**
     * @return array<string, list<array{pattern: string|list<string>, label: string, url: string, icon: string}>>
     */
    public static function for(User $user): array
    {
        $can = fn (string $permission): bool => $user->hasPermission($permission);
        $owner = $user->isOwnerAdmin();
        $canReview = $can('property.publish') || $can('project.publish');
        $canCms = $can('cms.update') || $can('cms.create');

        $canReference = $user->can('reference.manage');

        $groups = [
            'Dashboard' => [
                self::item('admin.dashboard', 'Overview', route('admin.dashboard'), 'dashboard'),
            ],
            'Website pages' => [
                $canCms ? self::item('admin.home-sections.*', 'Home', route('admin.home-sections.index'), 'home') : null,
                $canCms ? self::item('admin.cms.*', 'Custom pages', route('admin.cms.index'), 'document') : null,
                $canCms ? self::item('admin.faqs.*', 'FAQ page', route('admin.faqs.index'), 'info') : null,
                $can('cms.publish') ? self::item('admin.page-seo.*', 'Page SEO', route('admin.page-seo.index'), 'globe') : null,
            ],
            'Content library' => [
                $can('property.view') || $can('property.update') ? self::item(['admin.properties.*', 'admin.units.*'], 'Properties', route('admin.properties.index'), 'grid') : null,
                $can('project.view') || $can('project.update') ? self::item('admin.projects.*', 'Projects', route('admin.projects.index'), 'building') : null,
                $canReview ? self::item('admin.review.*', 'Review queue', route('admin.review.index'), 'check-circle') : null,
                $canReference ? self::item('admin.areas.*', 'Areas', route('admin.areas.index'), 'pin') : null,
                $canReference ? self::item('admin.property-types.*', 'Property types', route('admin.property-types.index'), 'tag') : null,
                $canReference ? self::item('admin.amenities.*', 'Amenities', route('admin.amenities.index'), 'sparkle') : null,
                $user->can('viewAny', Post::class) ? self::item('admin.posts.*', 'Articles', route('admin.posts.index'), 'list') : null,
                $user->can('create', Post::class) ? self::item('admin.post-categories.*', 'Article categories', route('admin.post-categories.index'), 'tag') : null,
                $can('cms.publish') ? self::item('admin.menus.*', 'Menu management', route('admin.menus.index'), 'menu') : null,
                $canCms ? self::item('admin.site-urls', 'Pages & slugs', route('admin.site-urls'), 'link') : null,
            ],
            'Communication' => [
                $can('lead.view') ? self::item('admin.leads.*', 'Leads & enquiries', route('admin.leads.index'), 'inbox') : null,
                $can('lead.view') ? self::item('admin.follow-ups.*', 'Follow-ups', route('admin.follow-ups.index'), 'clock') : null,
                $can('lead.view') ? self::item('admin.visits.*', 'Site visits', route('admin.visits.index'), 'calendar') : null,
            ],
            'Website settings' => [
                $owner ? self::item('admin.settings.index', 'Branding', route('admin.settings.index'), 'settings') : null,
                $owner ? self::item('admin.settings.contact', 'Contact & social', route('admin.settings.contact'), 'phone') : null,
                $owner ? self::item('admin.settings.theme', 'Theme & colours', route('admin.settings.theme'), 'sparkle') : null,
                $owner ? self::item('admin.settings.enquiries', 'Enquiries & email', route('admin.settings.enquiries'), 'mail') : null,
                $owner ? self::item('admin.listing-display', 'Listing display', route('admin.listing-display'), 'map') : null,
                $owner ? self::item('admin.settings.analytics', 'Analytics & tracking', route('admin.settings.analytics'), 'compass') : null,
                $owner ? self::item('admin.settings.seo', 'SEO defaults', route('admin.settings.seo'), 'globe') : null,
                $user->can('redirect.manage') ? self::item('admin.redirects.*', 'Redirects', route('admin.redirects.index'), 'arrow-right') : null,
                $user->can('audit.view') ? self::item('admin.audit.*', 'Activity log', route('admin.audit.index'), 'shield') : null,
            ],
            'Administration' => [
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
     * @param  array<string, list<array{pattern: string|list<string>, label: string, url: string, icon: string}>>  $groups
     * @return array{group: string, item: array{pattern: string|list<string>, label: string, url: string, icon: string}}|null
     */
    public static function current(array $groups): ?array
    {
        foreach ($groups as $group => $items) {
            foreach ($items as $item) {
                if (request()->routeIs(...(array) $item['pattern'])) {
                    return ['group' => $group, 'item' => $item];
                }
            }
        }

        return null;
    }

    /**
     * @param  string|list<string>  $pattern
     * @return array{pattern: string|list<string>, label: string, url: string, icon: string}
     */
    private static function item(string|array $pattern, string $label, string $url, string $icon): array
    {
        return ['pattern' => $pattern, 'label' => $label, 'url' => $url, 'icon' => $icon];
    }
}
