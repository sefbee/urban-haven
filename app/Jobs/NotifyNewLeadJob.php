<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

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

    /**
     * Unassigned leads go to administrators and the sales inbox only; sales users cannot open them yet.
     */
    public function handle(): void
    {
        $lead = Lead::query()->with(['property', 'project', 'assignee'])->find($this->leadId);

        if ($lead === null) {
            return;
        }

        $recipients = $lead->assignee && $lead->assignee->is_active
            ? collect([$lead->assignee])
            : User::query()
                ->where('is_active', true)
                ->whereHas('roles', fn ($query) => $query->where('key', Role::OWNER_ADMIN))
                ->get();

        foreach ($recipients as $recipient) {
            $recipient->notify(new NewLeadNotification($lead));
        }

        $inbox = (string) Setting::get('sales_inbox_email', '');

        if ($inbox !== '' && filter_var($inbox, FILTER_VALIDATE_EMAIL) && ! $recipients->contains(fn (User $user): bool => strcasecmp($user->email, $inbox) === 0)) {
            Notification::route('mail', $inbox)->notify(new NewLeadNotification($lead, mailOnly: true));
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Lead notification failed', [
            'lead_id' => $this->leadId,
            'error' => $exception ? class_basename($exception).': '.mb_strimwidth($exception->getMessage(), 0, 200) : null,
        ]);
    }
}
