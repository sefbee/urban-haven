<?php

namespace App\Notifications;

use App\Models\SiteVisitRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SiteVisitNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SiteVisitRequest $visit, public bool $databaseOnly = false, public bool $mailOnly = false) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        if ($this->databaseOnly) {
            return ['database'];
        }

        if ($this->mailOnly) {
            return ['mail'];
        }

        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Site visit requested')
            ->view('emails.site-visit-confirmation', [
                'visit' => $this->visit,
                'notifiable' => $notifiable,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'visit_id' => $this->visit->id,
            'lead_id' => $this->visit->lead_id,
            'message' => 'Site visit requested'.($this->visit->lead?->name ? ' by '.$this->visit->lead->name : ''),
            'url' => route('admin.visits.index'),
            'icon' => 'calendar',
        ];
    }
}
