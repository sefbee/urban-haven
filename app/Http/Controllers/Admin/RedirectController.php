<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Redirect;
use App\Services\Seo\RedirectResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use League\Csv\Reader;
use Throwable;

class RedirectController extends Controller
{
    private const MAX_IMPORT_ROWS = 2000;

    public function __construct(
        private readonly RedirectResolver $resolver,
        private readonly AuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('redirect.manage');

        return view('admin.redirects.index', [
            'redirects' => Redirect::query()
                ->when($request->filled('q'), fn ($query) => $query->where('from_path', 'like', '%'.addcslashes((string) $request->string('q'), '%_\\').'%'))
                ->latest('id')
                ->paginate(50)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('redirect.manage');
        $validated = $request->validate($this->rules());

        $redirect = $this->resolver->store($validated['from_path'], $validated['to_path'], (int) $validated['http_code'], $validated['reason'] ?? null, $request->user());
        $this->audit->record($request->user()->id, 'redirect.saved', Redirect::class, $redirect->id, null, $redirect->only(['from_path', 'to_path', 'http_code']), $request->ip());

        return back()->with('status', 'Redirect saved.');
    }

    /**
     * Accepts a CSV with columns from_path,to_path[,http_code][,reason]; each row is validated independently
     * so one bad row never blocks the rest, and the outcome is reported per line.
     */
    public function import(Request $request): RedirectResponse
    {
        $this->authorize('redirect.manage');
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:1024']]);

        $reader = Reader::createFromPath($request->file('file')->getRealPath());
        $reader->setHeaderOffset(0);

        $imported = 0;
        $failures = [];

        foreach ($reader->getRecords() as $offset => $row) {
            if ($offset > self::MAX_IMPORT_ROWS) {
                $failures[] = 'Stopped after '.self::MAX_IMPORT_ROWS.' rows.';
                break;
            }

            $row = array_change_key_case(array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row));
            $validator = validator([...$row, 'http_code' => $row['http_code'] ?? 301], $this->rules());

            if ($validator->fails()) {
                $failures[] = 'Line '.($offset + 1).': '.$validator->errors()->first();

                continue;
            }

            try {
                $this->resolver->store($row['from_path'], $row['to_path'], (int) ($row['http_code'] ?? 301), $row['reason'] ?? 'CSV import', $request->user());
                $imported++;
            } catch (ValidationException $exception) {
                $failures[] = 'Line '.($offset + 1).': '.collect($exception->errors())->flatten()->first();
            } catch (Throwable) {
                $failures[] = 'Line '.($offset + 1).': could not be saved.';
            }
        }

        $this->audit->record($request->user()->id, 'redirect.imported', Redirect::class, null, null, ['imported' => $imported, 'failed' => count($failures)], $request->ip());

        return back()
            ->with('status', "Imported {$imported} redirects.")
            ->with('importFailures', array_slice($failures, 0, 50));
    }

    public function toggle(Request $request, Redirect $redirect): RedirectResponse
    {
        $this->authorize('redirect.manage');
        $redirect->forceFill(['is_active' => ! $redirect->is_active])->save();
        $this->audit->record($request->user()->id, 'redirect.toggled', Redirect::class, $redirect->id, null, ['is_active' => $redirect->is_active], $request->ip());

        return back()->with('status', $redirect->is_active ? 'Redirect enabled.' : 'Redirect disabled.');
    }

    public function destroy(Request $request, Redirect $redirect): RedirectResponse
    {
        $this->authorize('redirect.manage');
        $this->audit->record($request->user()->id, 'redirect.deleted', Redirect::class, $redirect->id, $redirect->only(['from_path', 'to_path']), null, $request->ip());
        $redirect->delete();

        return back()->with('status', 'Redirect deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'from_path' => ['required', 'string', 'max:255', 'regex:#^/[^\s]*$#'],
            'to_path' => ['required', 'string', 'max:255', 'regex:#^(/[^\s]*|https://[^\s]+)$#'],
            'http_code' => ['required', 'in:301,302'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
