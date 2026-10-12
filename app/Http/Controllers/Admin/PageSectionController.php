<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use App\Services\Cms\CmsService;
use App\Support\HomeSections;
use App\Support\AboutPageSections;
use App\Support\ContactPageSections;
use App\Support\ArticlesPageSections;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    private function getSectionsClass(string $page) {
        return match($page) {
            'home' => HomeSections::class,
            'about' => AboutPageSections::class,
            'contact' => ContactPageSections::class,
            'articles' => ArticlesPageSections::class,
            default => abort(404),
        };
    }

    public function index(Request $request, string $page): View
    {
        $this->authorize('update', new CmsPage);
        $sectionsClass = $this->getSectionsClass($page);

        return view('admin.page-sections.index', [
            'page' => $page,
            'blocks' => $sectionsClass::blocks(),
            'definitions' => $sectionsClass::definitions(),
            'canPublish' => $request->user()->hasPermission('cms.publish'),
        ]);
    }

    public function edit(Request $request, string $page, string $section): View
    {
        $this->authorize('update', new CmsPage);
        $sectionsClass = $this->getSectionsClass($page);
        
        $definition = $sectionsClass::definition($section);
        $blocks = $sectionsClass::blocks();
        $block = $blocks->get($section);
        $keys = $blocks->keys()->all();
        $position = array_search($section, $keys, true);

        return view('admin.page-sections.edit', [
            'page' => $page,
            'section' => $section,
            'definition' => $definition,
            'block' => $block,
            'values' => $sectionsClass::resolve($section, $block->draft_content ?? $block->content),
            'icons' => HomeSections::ICONS,
            'position' => $position + 1,
            'total' => count($keys),
            'previous' => $keys[$position - 1] ?? null,
            'next' => $keys[$position + 1] ?? null,
            'canPublish' => $request->user()->hasPermission('cms.publish'),
        ]);
    }

    public function update(Request $request, string $page, string $section, CmsService $cms): RedirectResponse
    {
        $this->authorize('update', new CmsPage);
        $sectionsClass = $this->getSectionsClass($page);
        
        $definition = $sectionsClass::definition($section);
        $block = $sectionsClass::blocks()->get($section);

        $validated = $request->validate($this->rules($definition['fields']), [], $this->attributes($definition['fields']));
        $content = $this->content($definition['fields'], $validated['content'] ?? []);

        $cms->updateBlock($block, $content, $request->user());

        return redirect()->route('admin.page-sections.edit', ['page' => $page, 'section' => $section])->with('status', $request->user()->hasPermission('cms.publish')
            ? $definition['label'].' updated on the page.'
            : $definition['label'].' changes saved as a draft for publish approval.');
    }

    public function visibility(Request $request, string $page, string $section, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $sectionsClass = $this->getSectionsClass($page);
        
        $definition = $sectionsClass::definition($section);
        $block = $sectionsClass::blocks()->get($section);

        $block->forceFill(['is_visible' => ! $block->is_visible, 'updated_at' => now()])->save();
        $audit->record($request->user()->id, $block->is_visible ? 'cms.section_shown' : 'cms.section_hidden', CmsBlock::class, $block->id, null, ['key' => $block->key], $request->ip());

        return back()->with('status', $definition['label'].($block->is_visible ? ' is now shown on the page.' : ' is now hidden from the page.'));
    }

    public function move(Request $request, string $page, string $section): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $sectionsClass = $this->getSectionsClass($page);
        
        $direction = $request->input('direction');
        abort_unless(in_array($direction, ['up', 'down'], true), 400);

        $order = $sectionsClass::blocks()->keys()->all();
        $current = array_search($section, $order, true);
        if ($current === false) {
            abort(404);
        }

        $swap = $direction === 'up' ? $current - 1 : $current + 1;
        if (isset($order[$swap])) {
            $temp = $order[$swap];
            $order[$swap] = $section;
            $order[$current] = $temp;
            $sectionsClass::reorder($order);
        }

        return back()->with('status', 'Page sections reordered.');
    }

    public function publish(Request $request, string $page, string $section, CmsService $cms): RedirectResponse
    {
        $this->authorize('publish', new CmsPage);
        $sectionsClass = $this->getSectionsClass($page);
        
        $definition = $sectionsClass::definition($section);
        $cms->publishBlock($sectionsClass::blocks()->get($section), $request->user());

        return back()->with('status', $definition['label'].' changes are now live.');
    }

    public function discard(Request $request, string $page, string $section, CmsService $cms): RedirectResponse
    {
        $this->authorize('update', new CmsPage);
        $sectionsClass = $this->getSectionsClass($page);
        
        $definition = $sectionsClass::definition($section);
        $block = $sectionsClass::blocks()->get($section);
        $cms->discardBlockDraft($block, $request->user());

        return redirect()->route('admin.page-sections.edit', ['page' => $page, 'section' => $section])->with('status', 'Draft changes for '.$definition['label'].' were discarded.');
    }

    public function reset(Request $request, string $page, string $section, CmsService $cms): RedirectResponse
    {
        $this->authorize('update', new CmsPage);
        $sectionsClass = $this->getSectionsClass($page);
        
        $definition = $sectionsClass::definition($section);
        $cms->updateBlock($sectionsClass::blocks()->get($section), $sectionsClass::defaults($section), $request->user());

        return redirect()->route('admin.page-sections.edit', ['page' => $page, 'section' => $section])->with('status', $definition['label'].' was reset to the built-in text.');
    }

    private function rules(array $fields): array
    {
        $rules = [];
        foreach ($fields as $key => $field) {
            $rules["content.$key"] = ['nullable'];

            if ($field['type'] === 'repeater') {
                $rules["content.$key"][] = 'array';
                $rules["content.$key"][] = 'max:'.$field['max'];
                foreach ($field['fields'] as $subKey => $subField) {
                    $presence = ! empty($subField['required']) ? 'required_with:content.'.$key.'.*.'.$subKey : 'nullable';
                    if ($subField['type'] === 'text') {
                        $rules["content.$key.*.$subKey"] = [$presence, 'string', 'max:'.$subField['max']];
                    } elseif ($subField['type'] === 'textarea') {
                        $rules["content.$key.*.$subKey"] = [$presence, 'string', 'max:'.$subField['max']];
                    } elseif ($subField['type'] === 'icon') {
                        $rules["content.$key.*.$subKey"] = [$presence, 'string', Rule::in(HomeSections::ICONS)];
                    }
                }
            } else {
                $presence = ! empty($field['required']) ? 'required' : 'nullable';
                if ($field['type'] === 'text') {
                    $rules["content.$key"] = [$presence, 'string', 'max:'.$field['max']];
                } elseif ($field['type'] === 'textarea') {
                    $rules["content.$key"] = [$presence, 'string', 'max:'.$field['max']];
                } elseif ($field['type'] === 'path') {
                    $rules["content.$key"] = [$presence, 'string', 'max:'.$field['max'], 'regex:/^\/[a-zA-Z0-9\-\/]+$/'];
                } elseif ($field['type'] === 'number') {
                    $rules["content.$key"] = [$presence, 'integer', 'min:'.$field['min'], 'max:'.$field['max']];
                }
            }
        }

        return $rules;
    }

    private function attributes(array $fields): array
    {
        $attributes = [];
        foreach ($fields as $key => $field) {
            $attributes["content.$key"] = strtolower($field['label']);
            if ($field['type'] === 'repeater') {
                foreach ($field['fields'] as $subKey => $subField) {
                    $attributes["content.$key.*.$subKey"] = strtolower($subField['label']);
                }
            }
        }

        return $attributes;
    }

    private function content(array $fields, array $input): array
    {
        $content = [];
        foreach ($fields as $key => $field) {
            if ($field['type'] === 'repeater' && isset($input[$key]) && is_array($input[$key])) {
                $content[$key] = array_values(array_filter($input[$key], function (array $item) use ($field): bool {
                    foreach ($field['fields'] as $subKey => $subField) {
                        if (! empty($subField['required']) && blank($item[$subKey] ?? null)) {
                            return false;
                        }
                    }
                    return true;
                }));
            } elseif (isset($input[$key])) {
                $content[$key] = $field['type'] === 'number' ? (int) $input[$key] : $input[$key];
            }
        }

        return $content;
    }
}
