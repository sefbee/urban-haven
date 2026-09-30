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

    public function __construct(public SiteVisitRequest $visit) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
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
            'message' => 'A site visit was requested.',
        ];
    }
}
