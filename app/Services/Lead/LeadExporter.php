<?php

namespace App\Services\Lead;

use App\Contracts\AuditLogger;
use App\Models\Lead;
use App\Models\User;
use App\Support\DisplayTimezone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use League\Csv\Writer;

class LeadExporter
{
    public const HEADERS = [
        'Lead ID', 'Date', 'Type', 'Name', 'Phone', 'Email', 'Property reference', 'Project', 'Stage',
        'Priority', 'Assignee', 'Source', 'Medium', 'Campaign', 'Next action', 'Loss reason',
    ];

    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Lead>
     */
    public function query(array $filters): Builder
    {
        $timezone = config('urbanhaven.display_timezone');

        return Lead::query()
            ->with(['property:id,reference,title', 'project:id,name', 'assignee:id,name'])
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->where('created_at', '>=', Carbon::parse($from, $timezone)->startOfDay()->utc()))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->where('created_at', '<=', Carbon::parse($to, $timezone)->endOfDay()->utc()))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['assigned_to'] ?? null, fn (Builder $q, mixed $assignee) => $assignee === 'unassigned' ? $q->whereNull('assigned_to') : $q->where('assigned_to', (int) $assignee))
            ->when($filters['source'] ?? null, fn (Builder $q, string $source) => $q->where(fn (Builder $s) => $s->where('utm_source', $source)->orWhere('source', $source)))
            ->when($filters['property_id'] ?? null, fn (Builder $q, mixed $id) => $q->where('property_id', (int) $id))
            ->when($filters['project_id'] ?? null, fn (Builder $q, mixed $id) => $q->where('project_id', (int) $id))
            ->orderBy('id');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function writeTo(array $filters, Writer $csv): int
    {
        $csv->insertOne(self::HEADERS);
        $rows = 0;

        $this->query($filters)->chunkById(500, function ($leads) use ($csv, &$rows): void {
            foreach ($leads as $lead) {
                $csv->insertOne(array_map([self::class, 'neutralise'], $this->row($lead)));
                $rows++;
            }
        });

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function audit(User $actor, array $filters, int $rows, ?string $ip, string $mode): void
    {
        $this->auditLogger->record($actor->id, 'lead.exported', Lead::class, null, null, [
            'filters' => array_filter($filters, fn (mixed $value): bool => filled($value)),
            'rows' => $rows,
            'mode' => $mode,
        ], $ip);
    }

    /**
     * Spreadsheet apps execute cells that start with these characters, so they are prefixed with a quote.
     */
    public static function neutralise(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * @return list<mixed>
     */
    private function row(Lead $lead): array
    {
        return [
            $lead->id,
            DisplayTimezone::format($lead->created_at, 'Y-m-d H:i'),
            $lead->typeLabel(),
            $lead->name,
            $lead->phone,
            $lead->email,
            $lead->property?->reference ?? $lead->property?->title,
            $lead->project?->name,
            $lead->statusLabel(),
            ucfirst((string) $lead->priority),
            $lead->assignee?->name,
            $lead->utm_source ?? $lead->source,
            $lead->utm_medium,
            $lead->utm_campaign,
            $lead->next_action,
            $lead->loss_reason ? (Lead::LOSS_REASONS[$lead->loss_reason] ?? $lead->loss_reason) : null,
        ];
    }
}
