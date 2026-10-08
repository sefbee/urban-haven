<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        $this->authorize('publish', new CmsPage);

        $items = MenuItem::query()->orderBy('sort_order')->orderBy('id')->get();
        $children = $items->whereNotNull('parent_id')
            ->each(fn (MenuItem $child) => $child->setRelation('children', collect()))
            ->groupBy('parent_id');

        return view('admin.menus.index', [
            'locations' => MenuItem::LOCATIONS,
            'menus' => collect(MenuItem::LOCATIONS)->map(fn (string $label, string $location) => $items
                ->where('location', $location)
                ->whereNull('parent_id')
                ->values()
                ->each(fn (MenuItem $item) => $item->setRelation('children', $children->get($item->id, collect())->values()))),
            'parents' => $items->whereNull('parent_id')->groupBy('location'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $attributes = $this->validated($request);
        $attributes['sort_order'] ??= (int) MenuItem::query()
            ->where('location', $attributes['location'])
            ->where('parent_id', $attributes['parent_id'])
            ->max('sort_order') + 10;

        $item = MenuItem::query()->create($attributes);
        $this->audit->record($request->user()->id, 'menu.created', MenuItem::class, $item->id, null, $item->only(['location', 'parent_id', 'label', 'url']), $request->ip());

        return back()->with('status', 'Menu link added.');
    }

    public function update(Request $request, MenuItem $item): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $old = $item->only(['location', 'parent_id', 'label', 'url', 'is_visible', 'opens_new_tab']);
        $attributes = $this->validated($request, $item);
        $attributes['sort_order'] ??= $item->sort_order;

        $item->update($attributes);

        if ($item->wasChanged('location')) {
            $item->children()->update(['location' => $item->location]);
        }

        $this->audit->record($request->user()->id, 'menu.updated', MenuItem::class, $item->id, $old, $item->only(array_keys($old)), $request->ip());

        return back()->with('status', 'Menu link updated.');
    }

    public function visibility(Request $request, MenuItem $item): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $item->update(['is_visible' => ! $item->is_visible]);
        $this->audit->record($request->user()->id, 'menu.updated', MenuItem::class, $item->id, ['is_visible' => ! $item->is_visible], ['is_visible' => $item->is_visible], $request->ip());

        return back()->with('status', '“'.$item->label.'” is now '.($item->is_visible ? 'shown.' : 'hidden.'));
    }

    public function move(Request $request, MenuItem $item): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        $siblings = MenuItem::query()
            ->where('location', $item->location)
            ->where('parent_id', $item->parent_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values();

        $from = $siblings->search(fn (MenuItem $sibling): bool => $sibling->is($item));
        $to = $direction === 'up' ? $from - 1 : $from + 1;

        if ($to >= 0 && $to < $siblings->count()) {
            $order = $siblings->all();
            [$order[$from], $order[$to]] = [$order[$to], $order[$from]];

            foreach ($order as $position => $sibling) {
                if ($sibling->sort_order !== ($position + 1) * 10) {
                    $sibling->update(['sort_order' => ($position + 1) * 10]);
                }
            }

            $this->audit->record($request->user()->id, 'menu.reordered', MenuItem::class, $item->id, null, ['direction' => $direction], $request->ip());
        }

        return back();
    }

    public function destroy(Request $request, MenuItem $item): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $this->audit->record($request->user()->id, 'menu.deleted', MenuItem::class, $item->id, $item->only(['label', 'url']), null, $request->ip());
        $item->children()->get()->each->delete();
        $item->delete();

        return back()->with('status', 'Menu link removed.');
    }

    /**
     * @return array{location: string, parent_id: int|null, label: string, url: string, opens_new_tab: bool, sort_order: int|null, is_visible: bool}
     */
    private function validated(Request $request, ?MenuItem $item = null): array
    {
        $validated = $request->validate([
            'location' => ['required', Rule::in(array_keys(MenuItem::LOCATIONS))],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('menu_items', 'id')->whereNull('parent_id')->where('location', $request->input('location')),
                Rule::notIn(array_filter([$item?->id])),
            ],
            'label' => ['required', 'string', 'max:60'],
            'url' => ['required', 'string', 'max:255', 'regex:#^(/(?!/)[^\s]*|https://[^\s]+)$#'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_visible' => ['nullable', 'boolean'],
            'opens_new_tab' => ['nullable', 'boolean'],
        ], [
            'url.regex' => 'Use an internal path starting with / or a full https:// address.',
            'parent_id.exists' => 'Choose a top-level link from the same menu.',
            'parent_id.not_in' => 'A link cannot sit under itself.',
        ], ['parent_id' => 'parent link']);

        if ($item && filled($validated['parent_id'] ?? null) && $item->children()->exists()) {
            throw ValidationException::withMessages(['parent_id' => 'Move or remove this link’s own sub-links first; menus are two levels deep.']);
        }

        return [
            'location' => $validated['location'],
            'parent_id' => filled($validated['parent_id'] ?? null) ? (int) $validated['parent_id'] : null,
            'label' => $validated['label'],
            'url' => $validated['url'],
            'opens_new_tab' => $request->boolean('opens_new_tab'),
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : null,
            'is_visible' => $request->boolean('is_visible', true),
        ];
    }
}
