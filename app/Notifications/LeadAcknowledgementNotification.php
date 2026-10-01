<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAcknowledgementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [30, 120, 300];

    public function __construct(public int $leadId) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = Lead::query()->with(['property', 'project'])->findOrFail($this->leadId);
        $subject = $lead->property?->title ?? $lead->project?->name;

        $message = (new MailMessage)
            ->subject($lead->type === 'visit_request' ? 'We received your visit request' : 'We received your inquiry')
            ->greeting('Hello '.$lead->name.',');

        if ($lead->type === 'visit_request') {
            $message->line('Thank you for requesting a visit'.($subject ? ' to '.$subject : '').'. Your request is pending confirmation — our sales team will contact you to agree a time. The visit is not confirmed until we do.');
        } else {
            $message->line('Thank you for contacting Urban Haven'.($subject ? ' about '.$subject : '').'. Our sales team will be in touch shortly.');
        }

        $phone = (string) Setting::get('sales_phone') ?: Setting::get('phone', '');

        if ($phone !== '') {
            $message->line('If your inquiry is urgent, call us on '.$phone.'.');
        }

        return $message->salutation('Urban Haven Properties Ltd.');
    }
}
