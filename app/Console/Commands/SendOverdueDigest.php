<?php

namespace App\Console\Commands;

use App\Models\LeadFollowUp;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\OverdueFollowUpsDigest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('uh:overdue-digest')]
#[Description('Send each active staff member a digest of their overdue follow-ups')]
class SendOverdueDigest extends Command
{
    public function handle(): int
    {
        if (! (bool) Setting::get('overdue_digest_enabled', true)) {
            $this->info('Overdue digest is turned off in settings.');

            return self::SUCCESS;
        }

        $sent = 0;

        LeadFollowUp::query()
            ->overdue()
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->selectRaw('user_id, count(*) as overdue_count')
            ->groupBy('user_id')
            ->get()
            ->each(function (LeadFollowUp $row) use (&$sent): void {
                $user = User::query()->find($row->user_id);

                if ($user) {
                    $user->notify(new OverdueFollowUpsDigest((int) $row->overdue_count));
                    $sent++;
                }
            });

        $this->info("Sent {$sent} overdue digests.");

        return self::SUCCESS;
    }
}
