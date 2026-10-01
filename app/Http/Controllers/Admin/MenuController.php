<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        $this->authorize('publish', new CmsPage);

        return view('admin.menus.index', [
            'locations' => MenuItem::LOCATIONS,
            'items' => MenuItem::query()->orderBy('location')->orderBy('sort_order')->get()->groupBy('location'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $item = MenuItem::query()->create($this->validated($request));
        $this->audit->record($request->user()->id, 'menu.created', MenuItem::class, $item->id, null, $item->only(['location', 'label', 'url']), $request->ip());

        return back()->with('status', 'Menu link added.');
    }

    public function update(Request $request, MenuItem $item): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $old = $item->only(['label', 'url', 'is_visible']);
        $item->update($this->validated($request));
        $this->audit->record($request->user()->id, 'menu.updated', MenuItem::class, $item->id, $old, $item->only(['label', 'url', 'is_visible']), $request->ip());

        return back()->with('status', 'Menu link updated.');
    }

    public function destroy(Request $request, MenuItem $item): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $this->audit->record($request->user()->id, 'menu.deleted', MenuItem::class, $item->id, $item->only(['label', 'url']), null, $request->ip());
        $item->delete();

        return back()->with('status', 'Menu link removed.');
    }

    /**
     * @return array{location: string, label: string, url: string, sort_order: int, is_visible: bool}
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'location' => ['required', Rule::in(array_keys(MenuItem::LOCATIONS))],
            'label' => ['required', 'string', 'max:60'],
            'url' => ['required', 'string', 'max:255', 'regex:#^(/(?!/)[^\s]*|https://[^\s]+)$#'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_visible' => ['nullable', 'boolean'],
        ], ['url.regex' => 'Use an internal path starting with / or a full https:// address.']);

        return [
            'location' => $validated['location'],
            'label' => $validated['label'],
            'url' => $validated['url'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_visible' => $request->boolean('is_visible', true),
        ];
    }
}
