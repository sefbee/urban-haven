<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Faq;
use App\Support\TaggedCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stevebauman\Purify\Facades\Purify;

class FaqController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(): View
    {
        $this->authorize('viewAny', CmsPage::class);

        return view('admin.faqs.index', [
            'faqs' => Faq::query()->orderBy('group')->orderBy('sort_order')->get(),
            'canPublish' => request()->user()->hasPermission('cms.publish'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CmsPage::class);
        $faq = Faq::query()->create($this->validated($request));
        $this->audit->record($request->user()->id, 'faq.created', Faq::class, $faq->id, null, ['question' => $faq->question], $request->ip());
        TaggedCache::flush(['cms']);

        return back()->with('status', 'Question added.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $this->authorize('update', new CmsPage);
        $faq->update($this->validated($request, $faq));
        $this->audit->record($request->user()->id, 'faq.updated', Faq::class, $faq->id, null, ['question' => $faq->question, 'is_visible' => $faq->is_visible], $request->ip());
        TaggedCache::flush(['cms']);

        return back()->with('status', 'Question updated.');
    }

    public function destroy(Request $request, Faq $faq): RedirectResponse
    {
        $this->authorize('delete', new CmsPage);
        $this->audit->record($request->user()->id, 'faq.deleted', Faq::class, $faq->id, ['question' => $faq->question], null, $request->ip());
        $faq->delete();
        TaggedCache::flush(['cms']);

        return back()->with('status', 'Question deleted.');
    }

    /**
     * Only publishers may make a question visible; editors' entries stay hidden until approved.
     *
     * @return array{question: string, answer: string, group: string, sort_order: int, is_visible: bool}
     */
    private function validated(Request $request, ?Faq $faq = null): array
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:5000'],
            'group' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        $canPublish = $request->user()->hasPermission('cms.publish');

        return [
            'question' => $validated['question'],
            'answer' => Purify::clean($validated['answer']),
            'group' => filled($validated['group'] ?? null) ? $validated['group'] : 'General',
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_visible' => $canPublish ? $request->boolean('is_visible') : ($faq?->is_visible ?? false),
        ];
    }
}
