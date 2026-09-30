<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyNewLeadJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $leadId) {}

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(): void
    {
        $lead = Lead::query()->with(['property', 'project', 'assignee'])->find($this->leadId);

        if ($lead === null) {
            return;
        }

        $recipients = collect();

        if ($lead->assignee) {
            $recipients->push($lead->assignee);
        } else {
            $recipients = User::query()
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->whereIn('key', [Role::SALES_USER, Role::OWNER_ADMIN]))
                ->get();
        }

        foreach ($recipients as $recipient) {
            $recipient->notify(new NewLeadNotification($lead));
        }
    }
}
