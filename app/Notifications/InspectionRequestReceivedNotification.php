<?php

namespace App\Notifications;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionRequestReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Inspection $inspection)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('We received your viewing request')
            ->greeting('Viewing request received')
            ->line("Thanks {$this->inspection->requester_name}, we received your request to view {$this->inspection->property->title}.")
            ->line('Unit: '.($this->inspection->unit?->unit_name ?? 'Not specified'))
            ->line('Preferred date: '.($this->inspection->requested_for_date?->format('M j, Y') ?? 'Not specified'))
            ->line('Preferred time: '.($this->inspection->requested_for_time ?? 'Not specified'))
            ->line('Our team will review the request and contact you with the next step.');
    }
}
