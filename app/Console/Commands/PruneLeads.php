<?php

namespace App\Console\Commands;

use App\Contracts\AuditLogger;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('uh:prune-leads {--dry-run : Count without deleting}')]
#[Description('Delete closed leads older than the approved retention period')]
class PruneLeads extends Command
{
    public function handle(AuditLogger $audit): int
    {
        $days = (int) Setting::get('lead_retention_days', config('urbanhaven.lead.retention_days', 730));

        if ($days < 30) {
            $this->error('Retention must be at least 30 days; refusing to prune.');

            return self::FAILURE;
        }

        $query = Lead::query()
            ->whereIn('status', Lead::CLOSED_STATUSES)
            ->where('updated_at', '<', now()->subDays($days));

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} closed leads are older than {$days} days.");

            return self::SUCCESS;
        }

        $query->chunkById(200, fn ($leads) => $leads->each->delete());
        $audit->record(null, 'lead.retention_pruned', Lead::class, null, null, ['deleted' => $count, 'retention_days' => $days]);
        $this->info("Deleted {$count} closed leads older than {$days} days.");

        return self::SUCCESS;
    }
}
