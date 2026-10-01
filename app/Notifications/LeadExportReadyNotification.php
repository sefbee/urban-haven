<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadExportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public string $url, public int $rows) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Your lead export ('.number_format($this->rows).' rows) is ready. The link expires in 24 hours.',
            'url' => $this->url,
        ];
    }
}
