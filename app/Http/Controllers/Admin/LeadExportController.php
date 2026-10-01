<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ExportLeadsJob;
use App\Models\Lead;
use App\Services\Lead\LeadExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadExportController extends Controller
{
    public function export(Request $request, LeadExporter $exporter): StreamedResponse|RedirectResponse
    {
        $this->authorize('export', Lead::class);

        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', Rule::in(Lead::STATUSES)],
            'assigned_to' => ['nullable', 'string', 'max:20'],
            'source' => ['nullable', 'string', 'max:120'],
            'property_id' => ['nullable', 'integer'],
            'project_id' => ['nullable', 'integer'],
        ]);

        $count = $exporter->query($filters)->count();

        if ($count > (int) config('urbanhaven.lead.export_queue_threshold', 2000)) {
            ExportLeadsJob::dispatch($request->user()->id, $filters, $request->ip());

            return back()->with('status', 'This export has '.number_format($count).' rows. It is being prepared and a private download link will appear in your notifications.');
        }

        $exporter->audit($request->user(), $filters, $count, $request->ip(), 'download');

        return response()->streamDownload(function () use ($exporter, $filters): void {
            $csv = Writer::fromString();
            $exporter->writeTo($filters, $csv);
            echo $csv->toString();
        }, 'leads-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function download(Request $request, string $file): StreamedResponse
    {
        $this->authorize('export', Lead::class);
        abort_unless(preg_match('/^[a-f0-9-]{36}\.csv$/', $file) === 1, 404);

        $path = 'exports/'.$request->user()->id.'/'.$file;
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, 'leads-export.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
