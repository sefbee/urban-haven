<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Services\Cms\CmsService;
use App\Support\HomeSections;
use App\Support\TaggedCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeSectionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('update', new CmsPage);

        return view('admin.home-sections.index', [
            'blocks' => HomeSections::blocks(),
            'definitions' => HomeSections::definitions(),
            'canPublish' => $request->user()->hasPermission('cms.publish'),
        ]);
    }

    public function edit(Request $request, string $section): View
    {
        $this->authorize('update', new CmsPage);
        $definition = HomeSections::definition($section);
        $blocks = HomeSections::blocks();
        $block = $blocks->get($section);
        $keys = $blocks->keys()->all();
        $position = array_search($section, $keys, true);

        return view('admin.home-sections.edit', [
            'section' => $section,
            'definition' => $definition,
            'block' => $block,
            'values' => HomeSections::resolve($section, $block->draft_content ?? $block->content),
            'icons' => HomeSections::ICONS,
            'position' => $position + 1,
            'total' => count($keys),
            'previous' => $keys[$position - 1] ?? null,
            'next' => $keys[$position + 1] ?? null,
            'canPublish' => $request->user()->hasPermission('cms.publish'),
        ]);
    }

    public function update(Request $request, string $section, CmsService $cms): RedirectResponse
    {
        $this->authorize('update', new CmsPage);
        $definition = HomeSections::definition($section);
        $block = HomeSections::blocks()->get($section);

        $validated = $request->validate($this->rules($definition['fields']), [], $this->attributes($definition['fields']));
        $content = $this->content($definition['fields'], $validated['content'] ?? []);

        $cms->updateBlock($block, $content, $request->user());

        return redirect()->route('admin.home-sections.edit', $section)->with('status', $request->user()->hasPermission('cms.publish')
            ? $definition['label'].' updated on the homepage.'
            : $definition['label'].' changes saved as a draft for publish approval.');
    }

    public function visibility(Request $request, string $section, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $definition = HomeSections::definition($section);
        $block = HomeSections::blocks()->get($section);

        $block->forceFill(['is_visible' => ! $block->is_visible, 'updated_at' => now()])->save();
        $audit->record($request->user()->id, $block->is_visible ? 'cms.section_shown' : 'cms.section_hidden', CmsBlock::class, $block->id, null, ['key' => $block->key], $request->ip());
        $this->flush();

        return back()->with('status', $definition['label'].($block->is_visible ? ' is now shown on the homepage.' : ' is now hidden from the homepage.'));
    }

    public function move(Request $request, string $section, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $definition = HomeSections::definition($section);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        $order = HomeSections::blocks()->keys()->all();
        $from = array_search($section, $order, true);
        $to = $direction === 'up' ? $from - 1 : $from + 1;

        if (! isset($order[$to])) {
            return back();
        }

        [$order[$from], $order[$to]] = [$order[$to], $order[$from]];
        HomeSections::reorder($order);
        $audit->record($request->user()->id, 'cms.sections_reordered', CmsBlock::class, null, null, ['order' => $order], $request->ip());
        $this->flush();

        return back()->with('status', $definition['label'].' moved '.$direction.'.');
    }

    public function publish(Request $request, string $section, CmsService $cms): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $definition = HomeSections::definition($section);
        $cms->publishBlock(HomeSections::blocks()->get($section), $request->user());

        return back()->with('status', $definition['label'].' changes published.');
    }

    public function discard(Request $request, string $section, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $definition = HomeSections::definition($section);
        $block = HomeSections::blocks()->get($section);

        if ($block->hasDraft()) {
            $block->forceFill(['draft_content' => null, 'updated_at' => now()])->save();
            $audit->record($request->user()->id, 'cms.block_draft_discarded', CmsBlock::class, $block->id, null, ['key' => $block->key], $request->ip());
        }

        return back()->with('status', 'Pending changes to '.$definition['label'].' discarded.');
    }

    public function reset(Request $request, string $section, CmsService $cms): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $definition = HomeSections::definition($section);
        $cms->updateBlock(HomeSections::blocks()->get($section), HomeSections::defaults($section), $request->user());

        return back()->with('status', $definition['label'].' restored to the built-in text.');
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function rules(array $fields, string $prefix = 'content', bool $withinRepeater = false): array
    {
        $rules = [$prefix => ['nullable', 'array']];

        foreach ($fields as $name => $field) {
            $key = $prefix.'.'.$name;
            $presence = ($field['required'] ?? false) && ! $withinRepeater ? 'required' : 'nullable';

            $rules[$key] = match ($field['type']) {
                'number' => [$presence, 'integer', 'min:'.$field['min'], 'max:'.$field['max']],
                'path' => [$presence, 'string', 'max:'.$field['max'], 'regex:/^\/[^\s]*$/'],
                'icon' => [$presence, 'string', Rule::in(HomeSections::ICONS)],
                'repeater' => ['nullable', 'array', 'max:'.$field['max']],
                default => [$presence, 'string', 'max:'.$field['max']],
            };

            if ($field['type'] === 'repeater') {
                $rules[$key.'.*'] = ['array'];
                foreach ($this->rules($field['fields'], $key.'.*', true) as $childKey => $childRules) {
                    if ($childKey !== $key.'.*') {
                        $rules[$childKey] = $childRules;
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, string>
     */
    private function attributes(array $fields, string $prefix = 'content'): array
    {
        $attributes = [];

        foreach ($fields as $name => $field) {
            $attributes[$prefix.'.'.$name] = mb_strtolower($field['label']);

            if ($field['type'] === 'repeater') {
                $attributes += $this->attributes($field['fields'], $prefix.'.'.$name.'.*');
            }
        }

        return $attributes;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function content(array $fields, array $input): array
    {
        $content = [];

        foreach ($fields as $name => $field) {
            $value = $input[$name] ?? null;

            $content[$name] = match ($field['type']) {
                'number' => $value === null ? ($field['default'] ?? null) : (int) $value,
                'repeater' => collect(is_array($value) ? $value : [])
                    ->map(fn (array $item): array => $this->content($field['fields'], $item))
                    ->filter(fn (array $item): bool => collect($item)->except('icon')->filter(fn (mixed $part): bool => filled($part))->isNotEmpty())
                    ->values()
                    ->all(),
                default => $value === null ? '' : trim((string) $value),
            };
        }

        return $content;
    }

    private function flush(): void
    {
        TaggedCache::flush(['homepage', 'cms']);
    }
}
