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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminSearchController extends Controller
{
    private const PER_GROUP = 8;

    /**
     * One search box across every record the signed-in staff member is allowed to open.
     */
    public function __invoke(Request $request): View
    {
        $term = trim((string) ($request->validate(['q' => ['nullable', 'string', 'max:120']])['q'] ?? ''));
        $user = $request->user();
        $groups = [];

        if (mb_strlen($term) >= 2) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
            $digits = preg_replace('/\D+/', '', $term);

            if ($user->can('viewAny', Property::class)) {
                $groups['Properties'] = Property::query()
                    ->where(fn (Builder $query) => $query->where('title', 'like', $like)->orWhere('reference', 'like', $like)->orWhere('slug', 'like', $like))
                    ->latest('updated_at')->limit(self::PER_GROUP)->get(['id', 'slug', 'title', 'reference', 'availability', 'listing_type'])
                    ->map(fn (Property $property): array => [
                        'title' => $property->title,
                        'meta' => collect([$property->reference, Str::headline((string) $property->listing_type), Str::headline((string) $property->availability)])->filter()->implode(' · '),
                        'url' => route('admin.properties.edit', $property),
                        'icon' => 'building',
                    ])->all();
            }

            if ($user->can('viewAny', Project::class)) {
                $groups['Projects'] = Project::query()
                    ->where(fn (Builder $query) => $query->where('name', 'like', $like)->orWhere('developer_name', 'like', $like)->orWhere('slug', 'like', $like))
                    ->latest('updated_at')->limit(self::PER_GROUP)->get(['id', 'slug', 'name', 'developer_name', 'development_stage'])
                    ->map(fn (Project $project): array => [
                        'title' => $project->name,
                        'meta' => collect([$project->developer_name, Str::headline((string) $project->development_stage)])->filter()->implode(' · '),
                        'url' => route('admin.projects.edit', $project),
                        'icon' => 'grid',
                    ])->all();
            }

            if ($user->can('viewAny', Lead::class)) {
                $groups['Leads'] = Lead::query()->visibleTo($user)
                    ->where(function (Builder $query) use ($like, $digits): void {
                        $query->where('name', 'like', $like)->orWhere('email', 'like', $like);
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

            if ($user->can('viewAny', User::class)) {
                $groups['Staff'] = User::query()
                    ->where(fn (Builder $query) => $query->where('name', 'like', $like)->orWhere('email', 'like', $like))
                    ->orderBy('name')->limit(self::PER_GROUP)->get(['id', 'name', 'email'])
                    ->map(fn (User $staff): array => [
                        'title' => $staff->name,
                        'meta' => $staff->email,
                        'url' => route('admin.staff.edit', $staff),
                        'icon' => 'user',
                    ])->all();
            }

            $groups = array_filter($groups);
        }

        return view('admin.search', [
            'term' => $term,
            'groups' => $groups,
            'total' => array_sum(array_map('count', $groups)),
        ]);
    }
}
