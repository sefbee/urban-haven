<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueFollowUpsDigest extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $count) {}

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
            ->subject('You have '.$this->count.' overdue follow-ups')
            ->greeting('Good morning '.$notifiable->name.',')
            ->line('You have '.$this->count.' overdue '.str('follow-up')->plural($this->count).' in the sales queue.')
            ->action('Open overdue follow-ups', route('admin.follow-ups.index', ['view' => 'overdue']));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->count.' overdue '.str('follow-up')->plural($this->count).' need attention.',
            'url' => route('admin.follow-ups.index', ['view' => 'overdue']),
        ];
    }
}
