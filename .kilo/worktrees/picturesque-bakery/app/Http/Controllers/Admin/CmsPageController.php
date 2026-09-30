<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCmsPageRequest;
use App\Http\Requests\Admin\UpdateCmsPageRequest;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Services\Cms\CmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CmsPage::class);

        return view('admin.cms.pages.index', [
            'pages' => CmsPage::query()->with('publicationState')->orderBy('title')->get(),
            'blocks' => CmsBlock::query()->orderBy('label')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CmsPage::class);

        return view('admin.cms.pages.create');
    }

    public function store(StoreCmsPageRequest $request, CmsService $cms): RedirectResponse
    {
        $page = $cms->createPage($request->validated(), $request->user());

        return redirect()->route('admin.cms.edit', $page)->with('status', 'Page created.');
    }

    public function edit(CmsPage $page): View
    {
        $this->authorize('update', $page);

        return view('admin.cms.pages.edit', ['page' => $page]);
    }

    public function update(UpdateCmsPageRequest $request, CmsPage $page, CmsService $cms): RedirectResponse
    {
        $cms->updatePage($page, $request->validated(), $request->user());

        return back()->with('status', 'Page updated.');
    }

    public function publish(CmsPage $page, CmsService $cms): RedirectResponse
    {
        $this->authorize('update', $page);
        $cms->publishPage($page, request()->user());

        return back()->with('status', 'Page published.');
    }

    public function destroy(CmsPage $page, CmsService $cms): RedirectResponse
    {
        $this->authorize('delete', $page);
        $cms->deletePage($page, request()->user());

        return redirect()->route('admin.cms.index')->with('status', 'Page deleted.');
    }

    public function updateBlock(Request $request, CmsBlock $block, CmsService $cms): RedirectResponse
    {
        $this->authorize('update', CmsPage::class);
        $validated = $request->validate(['content' => ['required', 'array']]);
        $cms->updateBlock($block, $validated['content'], $request->user());

        return back()->with('status', 'Block updated.');
    }
}
