<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCmsPageRequest;
use App\Http\Requests\Admin\UpdateCmsPageRequest;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Services\Cms\CmsService;
use App\Support\HomeSections;
use App\Support\SeoFields;
use App\Support\SeoMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CmsPage::class);

        return view('admin.cms.pages.index', [
            'pages' => CmsPage::query()->with('publicationState')->orderBy('title')->get(),
            'blocks' => CmsBlock::query()->whereNotIn('key', HomeSections::blockKeys())->orderBy('sort_order')->orderBy('label')->get(),
            'canPublish' => request()->user()->hasPermission('cms.publish'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CmsPage::class);

        return view('admin.cms.pages.create', ['templates' => CmsPage::TEMPLATES]);
    }

    public function store(StoreCmsPageRequest $request, CmsService $cms): RedirectResponse
    {
        $page = $cms->createPage($request->content(), $request->user());
        SeoFields::sync($page, $request->validated());

        return redirect()->route('admin.cms.edit', $page)->with('status', 'Page created as a draft.');
    }

    public function edit(CmsPage $page): View
    {
        $this->authorize('update', $page);

        return view('admin.cms.pages.edit', [
            'page' => $page->load(['publicationState', 'seoOverride', 'media']),
            'templates' => CmsPage::TEMPLATES,
            'canPublish' => request()->user()->can('publish', $page),
        ]);
    }

    public function preview(CmsPage $page, CmsService $cms): Response
    {
        $this->authorize('update', $page);
        $preview = $cms->previewOf($page);

        return response()->view('public.cms.show', [
            'page' => $preview,
            'isPreview' => true,
            'seo' => SeoMeta::for(null, 'Preview: '.$preview->title, null, ['noindex' => true]),
        ])->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function update(UpdateCmsPageRequest $request, CmsPage $page, CmsService $cms): RedirectResponse
    {
        $cms->updatePage($page, $request->content(), $request->user());
        SeoFields::sync($page, $request->validated());

        return back()->with('status', $page->fresh()->hasPendingChanges()
            ? 'Changes saved and waiting for publish approval. The live page is unchanged.'
            : 'Page updated.');
    }

    public function publish(CmsPage $page, CmsService $cms): RedirectResponse
    {
        $this->authorize('publish', $page);
        $cms->publishPage($page, request()->user());

        return back()->with('status', 'Page published.');
    }

    public function unpublish(CmsPage $page, CmsService $cms): RedirectResponse
    {
        $this->authorize('publish', $page);
        $cms->unpublishPage($page, request()->user());

        return back()->with('status', 'Page unpublished.');
    }

    public function destroy(CmsPage $page, CmsService $cms): RedirectResponse
    {
        $this->authorize('delete', $page);
        $cms->deletePage($page, request()->user());

        return redirect()->route('admin.cms.index')->with('status', 'Page deleted.');
    }

    public function updateBlock(Request $request, CmsBlock $block, CmsService $cms): RedirectResponse
    {
        $this->authorize('update', new CmsPage);
        $validated = $request->validate([
            'content' => ['required', 'array', function (string $attribute, mixed $value, \Closure $fail): void {
                foreach (Arr::dot($value) as $leaf) {
                    if ($leaf !== null && (! is_string($leaf) || mb_strlen($leaf) > 2000)) {
                        $fail('Each block field must be text of 2,000 characters or fewer.');

                        return;
                    }
                }
            }],
            'is_visible' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $cms->updateBlock(
            $block,
            $validated['content'],
            $request->user(),
            $request->has('is_visible') ? $request->boolean('is_visible') : null,
            isset($validated['sort_order']) ? (int) $validated['sort_order'] : null,
        );

        return back()->with('status', $block->fresh()->hasDraft()
            ? 'Block changes saved as a draft for publish approval.'
            : 'Block updated.');
    }

    public function publishBlock(CmsBlock $block, CmsService $cms): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $cms->publishBlock($block, request()->user());

        return back()->with('status', 'Block changes published.');
    }
}
