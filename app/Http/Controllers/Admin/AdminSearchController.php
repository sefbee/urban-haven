<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminSearchController extends Controller
{
    private const PER_GROUP = 8;

    /**
     * One search box across every record and navigation page the signed-in staff member is allowed to open.
     */
    public function __invoke(Request $request): View|JsonResponse
    {
        $term = trim((string) ($request->validate(['q' => ['nullable', 'string', 'max:120']])['q'] ?? ''));
        $user = $request->user();
        $groups = [];

        if (mb_strlen($term) >= 2) {
            $termLower = mb_strtolower($term);
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
            $digits = preg_replace('/\D+/', '', $term);

            // 1. Navigation Pages & Tools
            $navItems = $this->searchableNavigationItems($user);
            $matchingNav = [];
            foreach ($navItems as $item) {
                $titleMatch = Str::contains(mb_strtolower($item['title']), $termLower);
                $keywordMatch = collect($item['keywords'] ?? [])->contains(function ($kw) use ($termLower) {
                    $kwLower = mb_strtolower($kw);

                    return Str::contains($kwLower, $termLower) || Str::contains($termLower, $kwLower);
                });

                if ($titleMatch || $keywordMatch) {
                    $matchingNav[] = [
                        'title' => $item['title'],
                        'meta' => $item['meta'],
                        'url' => $item['url'],
                        'icon' => $item['icon'],
                    ];
                }
            }

            if (! empty($matchingNav)) {
                $groups['Navigation & Tools'] = array_slice($matchingNav, 0, self::PER_GROUP);
            }

            // 2. Properties
            if ($user->can('viewAny', Property::class)) {
                $groups['Properties'] = Property::query()
                    ->where(function (Builder $query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('reference', 'like', $like)
                            ->orWhere('slug', 'like', $like)
                            ->orWhere('address', 'like', $like)
                            ->orWhereHas('locationArea', fn ($q) => $q->where('name', 'like', $like)->orWhere('city', 'like', $like))
                            ->orWhereHas('propertyType', fn ($q) => $q->where('label', 'like', $like)->orWhere('key', 'like', $like))
                            ->orWhereHas('project', fn ($q) => $q->where('name', 'like', $like));
                    })
                    ->latest('updated_at')->limit(self::PER_GROUP)->get(['id', 'slug', 'title', 'reference', 'availability', 'listing_type'])
                    ->map(fn (Property $property): array => [
                        'title' => $property->title,
                        'meta' => collect([$property->reference, Str::headline((string) $property->listing_type), Str::headline((string) $property->availability)])->filter()->implode(' · '),
                        'url' => route('admin.properties.edit', $property),
                        'icon' => 'building',
                    ])->all();
            }

            // 3. Projects
            if ($user->can('viewAny', Project::class)) {
                $groups['Projects'] = Project::query()
                    ->where(function (Builder $query) use ($like) {
                        $query->where('name', 'like', $like)
                            ->orWhere('developer_name', 'like', $like)
                            ->orWhere('slug', 'like', $like)
                            ->orWhere('city', 'like', $like)
                            ->orWhere('address', 'like', $like);
                    })
                    ->latest('updated_at')->limit(self::PER_GROUP)->get(['id', 'slug', 'name', 'developer_name', 'development_stage'])
                    ->map(fn (Project $project): array => [
                        'title' => $project->name,
                        'meta' => collect([$project->developer_name, Str::headline((string) $project->development_stage)])->filter()->implode(' · '),
                        'url' => route('admin.projects.edit', $project),
                        'icon' => 'grid',
                    ])->all();
            }

            // 4. Leads
            if ($user->can('viewAny', Lead::class)) {
                $groups['Leads'] = Lead::query()->visibleTo($user)
                    ->where(function (Builder $query) use ($like, $digits): void {
                        $query->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('message', 'like', $like)
                            ->orWhere('next_action', 'like', $like)
                            ->orWhereHas('property', fn ($q) => $q->where('title', 'like', $like))
                            ->orWhereHas('project', fn ($q) => $q->where('name', 'like', $like));
                        if (strlen($digits) >= 4) {
                            $query->orWhere('phone', 'like', '%'.$digits.'%');
                        }
                    })
                    ->latest()->limit(self::PER_GROUP)->get(['id', 'name', 'phone', 'status', 'created_at'])
                    ->map(fn (Lead $lead): array => [
                        'title' => $lead->name ?: 'Unnamed enquiry',
                        'meta' => collect([$lead->phone, Str::headline((string) $lead->status), $lead->created_at?->diffForHumans()])->filter()->implode(' · '),
                        'url' => route('admin.leads.show', $lead),
                        'icon' => 'inbox',
                    ])->all();
            }

            // 5. Articles
            if ($user->can('viewAny', Post::class)) {
                $groups['Articles'] = Post::query()
                    ->where(fn (Builder $query) => $query->where('title', 'like', $like)->orWhere('slug', 'like', $like))
                    ->with('publicationState')->latest('updated_at')->limit(self::PER_GROUP)->get(['id', 'slug', 'title', 'updated_at'])
                    ->map(fn (Post $post): array => [
                        'title' => $post->title,
                        'meta' => Str::headline($post->editorialStatus()).' · updated '.$post->updated_at?->diffForHumans(),
                        'url' => route('admin.posts.edit', $post),
                        'icon' => 'document',
                    ])->all();
            }

            // 6. Pages & FAQs
            if ($user->can('viewAny', CmsPage::class)) {
                $groups['Pages'] = CmsPage::query()
                    ->where(fn (Builder $query) => $query->where('title', 'like', $like)->orWhere('slug', 'like', $like))
                    ->orderBy('title')->limit(self::PER_GROUP)->get(['id', 'title', 'slug', 'status'])
                    ->map(fn (CmsPage $page): array => [
                        'title' => $page->title,
                        'meta' => '/'.$page->slug.' · '.Str::headline((string) $page->status),
                        'url' => route('admin.cms.edit', $page),
                        'icon' => 'globe',
                    ])->all();

                $groups['FAQs'] = Faq::query()
                    ->where(fn (Builder $query) => $query->where('question', 'like', $like)->orWhere('answer', 'like', $like))
                    ->orderBy('sort_order')->limit(self::PER_GROUP)->get(['id', 'question', 'group'])
                    ->map(fn (Faq $faq): array => [
                        'title' => $faq->question,
                        'meta' => Str::headline((string) $faq->group),
                        'url' => route('admin.faqs.index').'#faq-'.$faq->id,
                        'icon' => 'info',
                    ])->all();
            }

            // 7. Staff & Roles
            if ($user->can('viewAny', User::class)) {
                $staffQuery = User::query()->with('roles');

                if (in_array($termLower, ['staff', 'user', 'users', 'member', 'members', 'admin', 'sales', 'team'])) {
                    $staffQuery->where('is_active', true);
                } else {
                    $staffQuery->where(function (Builder $query) use ($like) {
                        $query->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('phone', 'like', $like)
                            ->orWhereHas('roles', fn ($q) => $q->where('label', 'like', $like)->orWhere('key', 'like', $like));
                    });
                }

                $groups['Staff'] = $staffQuery->orderBy('name')->limit(self::PER_GROUP)->get(['id', 'name', 'email'])
                    ->map(fn (User $staff): array => [
                        'title' => $staff->name,
                        'meta' => collect([$staff->email, $staff->roles->pluck('label')->first() ?: 'Staff'])->filter()->implode(' · '),
                        'url' => route('admin.staff.edit', $staff),
                        'icon' => 'user',
                    ])->all();
            }

            $groups = array_filter($groups);
        }

        $total = array_sum(array_map('count', $groups));

        if ($request->wantsJson() || $request->ajax()) {
            $flattened = [];
            foreach ($groups as $groupName => $items) {
                foreach ($items as $item) {
                    $flattened[] = array_merge($item, ['group' => $groupName]);
                }
            }

            return response()->json([
                'term' => $term,
                'groups' => $groups,
                'results' => array_slice($flattened, 0, 8),
                'total' => $total,
            ]);
        }

        return view('admin.search', [
            'term' => $term,
            'groups' => $groups,
            'total' => $total,
        ]);
    }

    /**
     * Searchable admin navigation tools and destinations based on user permissions.
     *
     * @return list<array{title: string, keywords: list<string>, meta: string, url: string, icon: string}>
     */
    private function searchableNavigationItems(User $user): array
    {
        $items = [];

        if ($user->can('viewAny', User::class)) {
            $items[] = [
                'title' => 'Users & roles',
                'keywords' => ['staff', 'users', 'roles', 'permissions', 'team', 'accounts', 'members', 'people'],
                'meta' => 'Staff directory, account statuses and role permissions',
                'url' => route('admin.staff.index'),
                'icon' => 'user',
            ];
            $items[] = [
                'title' => 'Invite / create staff member',
                'keywords' => ['add staff', 'new staff', 'invite user', 'new user', 'create staff', 'add user'],
                'meta' => 'Create a new staff member account',
                'url' => route('admin.staff.create'),
                'icon' => 'user',
            ];
        }

        if ($user->can('viewAny', Property::class)) {
            $items[] = [
                'title' => 'Properties',
                'keywords' => ['properties', 'listings', 'apartments', 'flats', 'real estate', 'inventory'],
                'meta' => 'Property listings catalogue and management',
                'url' => route('admin.properties.index'),
                'icon' => 'building',
            ];
            $items[] = [
                'title' => 'Add new property',
                'keywords' => ['create property', 'new property', 'add listing', 'new listing', 'post property'],
                'meta' => 'Add and publish a new property listing',
                'url' => route('admin.properties.create'),
                'icon' => 'plus',
            ];
            $items[] = [
                'title' => 'Areas & locations',
                'keywords' => ['areas', 'locations', 'zones', 'neighborhoods', 'dhaka', 'gulshan', 'banani', 'dhanmondi'],
                'meta' => 'Location areas and city zones',
                'url' => route('admin.areas.index'),
                'icon' => 'map',
            ];
            $items[] = [
                'title' => 'Property types',
                'keywords' => ['property types', 'types', 'categories', 'commercial', 'residential', 'apartment'],
                'meta' => 'Property types and asset categories',
                'url' => route('admin.property-types.index'),
                'icon' => 'tag',
            ];
            $items[] = [
                'title' => 'Amenities catalogue',
                'keywords' => ['amenities', 'features', 'facilities', 'lift', 'generator', 'parking', 'gym', 'pool'],
                'meta' => 'Property amenities and specifications catalogue',
                'url' => route('admin.amenities.index'),
                'icon' => 'sparkle',
            ];
        }

        if ($user->can('viewAny', Project::class)) {
            $items[] = [
                'title' => 'Projects',
                'keywords' => ['projects', 'developments', 'buildings', 'complexes', 'condos'],
                'meta' => 'Development projects and complex developments',
                'url' => route('admin.projects.index'),
                'icon' => 'grid',
            ];
            $items[] = [
                'title' => 'Add new project',
                'keywords' => ['create project', 'new project', 'add project', 'post project'],
                'meta' => 'Add a new real estate development project',
                'url' => route('admin.projects.create'),
                'icon' => 'plus',
            ];
        }

        if ($user->can('viewAny', Lead::class)) {
            $items[] = [
                'title' => 'Leads & enquiries',
                'keywords' => ['leads', 'enquiries', 'inquiries', 'crm', 'contacts', 'customers', 'clients', 'messages'],
                'meta' => 'Customer enquiries and lead pipeline',
                'url' => route('admin.leads.index'),
                'icon' => 'inbox',
            ];
            $items[] = [
                'title' => 'Follow-up reminders',
                'keywords' => ['follow-ups', 'followups', 'reminders', 'tasks', 'calls', 'scheduled'],
                'meta' => 'Pending and overdue lead follow-ups',
                'url' => route('admin.follow-ups.index'),
                'icon' => 'clock',
            ];
            $items[] = [
                'title' => 'Site visits',
                'keywords' => ['site visits', 'visits', 'tours', 'viewings', 'appointments', 'schedule'],
                'meta' => 'Booked customer property visits',
                'url' => route('admin.visits.index'),
                'icon' => 'calendar',
            ];
        }

        if ($user->can('viewAny', Post::class)) {
            $items[] = [
                'title' => 'Articles & blog',
                'keywords' => ['articles', 'posts', 'blog', 'news', 'guides', 'editorial'],
                'meta' => 'Editorial posts and buying guides',
                'url' => route('admin.posts.index'),
                'icon' => 'document',
            ];
            $items[] = [
                'title' => 'Write new article',
                'keywords' => ['create article', 'new post', 'new article', 'write post'],
                'meta' => 'Compose a new blog post or guide',
                'url' => route('admin.posts.create'),
                'icon' => 'plus',
            ];
        }

        if ($user->can('viewAny', CmsPage::class)) {
            $items[] = [
                'title' => 'Custom pages',
                'keywords' => ['pages', 'cms', 'about us', 'terms', 'privacy', 'content pages'],
                'meta' => 'Manage static CMS website pages',
                'url' => route('admin.cms.index'),
                'icon' => 'globe',
            ];
            $items[] = [
                'title' => 'FAQ management',
                'keywords' => ['faq', 'faqs', 'frequently asked questions', 'questions', 'answers'],
                'meta' => 'Manage FAQ groups and questions',
                'url' => route('admin.faqs.index'),
                'icon' => 'info',
            ];
        }

        if ($user->isOwnerAdmin()) {
            $items[] = [
                'title' => 'Review queue',
                'keywords' => ['review', 'approvals', 'pending', 'publish queue', 'moderation'],
                'meta' => 'Properties pending review or approval before publishing',
                'url' => route('admin.review.index'),
                'icon' => 'check',
            ];
            $items[] = [
                'title' => 'Website settings',
                'keywords' => ['settings', 'configuration', 'brand', 'company', 'contact info', 'social', 'phone', 'email', 'inbox'],
                'meta' => 'General business and contact settings',
                'url' => route('admin.settings.index'),
                'icon' => 'settings',
            ];
            $items[] = [
                'title' => 'Theme & colours',
                'keywords' => ['theme', 'colours', 'colors', 'palette', 'styling', 'branding', 'appearance'],
                'meta' => 'Visual branding and color palette settings',
                'url' => route('admin.settings.theme'),
                'icon' => 'sparkle',
            ];
            $items[] = [
                'title' => 'Analytics & tracking',
                'keywords' => ['analytics', 'tracking', 'gtm', 'google analytics', 'facebook pixel', 'scripts'],
                'meta' => 'Analytics tag managers and tracking pixels',
                'url' => route('admin.settings.analytics'),
                'icon' => 'dashboard',
            ];
            $items[] = [
                'title' => 'SEO defaults',
                'keywords' => ['seo defaults', 'sitemap', 'robots', 'canonical', 'social preview', 'meta description'],
                'meta' => 'Global SEO settings and sitemaps',
                'url' => route('admin.settings.seo'),
                'icon' => 'globe',
            ];
            $items[] = [
                'title' => 'Page SEO management',
                'keywords' => ['page seo', 'meta tags', 'page titles', 'opengraph'],
                'meta' => 'Per-page SEO metadata and OpenGraph tags',
                'url' => route('admin.page-seo.index'),
                'icon' => 'search',
            ];
            $items[] = [
                'title' => 'Menu management',
                'keywords' => ['menus', 'navigation', 'header menu', 'footer menu', 'links'],
                'meta' => 'Header and footer navigation menus',
                'url' => route('admin.menus.index'),
                'icon' => 'menu',
            ];
            $items[] = [
                'title' => 'URL redirects',
                'keywords' => ['redirects', '301', '302', 'url forwarding', 'paths'],
                'meta' => 'Manage URL redirects and status codes',
                'url' => route('admin.redirects.index'),
                'icon' => 'external',
            ];
            $items[] = [
                'title' => 'Activity audit log',
                'keywords' => ['audit', 'audit log', 'activity', 'history', 'logs', 'actions', 'who changed what'],
                'meta' => 'Security and activity log of all admin actions',
                'url' => route('admin.audit.index'),
                'icon' => 'document',
            ];
        }

        return $items;
    }
}
