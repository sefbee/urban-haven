<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadExportReadyNotification;
use App\Services\Lead\LeadExporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use League\Csv\Writer;

class ExportLeadsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 600;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(public int $userId, public array $filters, public ?string $ip = null) {}

    public function handle(LeadExporter $exporter): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null || ! $user->is_active || ! $user->can('export', Lead::class)) {
            return;
        }

        $file = Str::uuid()->toString().'.csv';
        $path = 'exports/'.$user->id.'/'.$file;

        $csv = Writer::fromString();
        $rows = $exporter->writeTo($this->filters, $csv);
        Storage::disk('local')->put($path, $csv->toString());
        $exporter->audit($user, $this->filters, $rows, $this->ip, 'queued');

        $url = URL::temporarySignedRoute('admin.leads.export.download', now()->addHours(24), ['file' => $file]);
        $user->notify(new LeadExportReadyNotification($url, $rows));
    }
}
