<?php

namespace App\Notifications;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Inspection $inspection)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('New inspection request received')
            ->greeting('Inspection request received')
            ->line("{$this->inspection->requester_name} requested a viewing for {$this->inspection->property->title}.")
            ->line('Preferred date: '.($this->inspection->requested_for_date?->format('M j, Y') ?? 'Not specified'))
            ->line('Preferred time: '.($this->inspection->requested_for_time ?? 'Not specified'))
            ->action('Open inspections queue', route('inspections.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New inspection request',
            'message' => "{$this->inspection->requester_name} requested a viewing for {$this->inspection->property->title}.",
            'action_url' => route('inspections.index'),
            'action_label' => 'Review inspection',
            'inspection_id' => $this->inspection->getKey(),
        ];
    }
}
