<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [30, 120, 300];

    public function __construct(public Lead $lead, public bool $mailOnly = false) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $this->mailOnly ? ['mail'] : ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(($this->lead->is_repeat_contact ? 'Repeat inquiry' : 'New inquiry').' #'.$this->lead->id)
            ->view('emails.new-lead', [
                'lead' => $this->lead,
                'notifiable' => $notifiable,
            ]);
    }

    /**
     * The in-app record intentionally carries no phone or email; staff open the lead to see contact details.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'name' => $this->lead->name,
            'property' => $this->lead->property?->title ?? $this->lead->project?->name,
            'message' => ($this->lead->is_repeat_contact ? 'Repeat inquiry from ' : 'New inquiry from ').$this->lead->name,
        ];
    }
}
