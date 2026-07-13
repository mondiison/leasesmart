<?php

namespace App\Notifications;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Inspection $inspection)
    {
    }

    public function via(object $notifiable): array
    {
        return method_exists($notifiable, 'notify') ? ['database', 'mail'] : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject('Your inspection request has been updated')
            ->greeting('Inspection request update')
            ->line("Your viewing request for {$this->inspection->property->title} is now {$this->inspection->status->label()}.");

        if ($this->inspection->scheduled_at) {
            $message->line('Scheduled for: '.$this->inspection->scheduled_at->format('M j, Y g:i A'));
        }

        if ($this->inspection->internal_notes) {
            $message->line('Update note: '.$this->inspection->internal_notes);
        }

        return $message->action('View property', route('marketplace.show', $this->inspection->property));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Inspection updated',
            'message' => "Your inspection for {$this->inspection->property->title} is now {$this->inspection->status->label()}.",
            'action_url' => route('marketplace.show', $this->inspection->property),
            'action_label' => 'View property',
            'inspection_id' => $this->inspection->getKey(),
        ];
    }
}
