<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected MaintenanceRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('New maintenance request created')
            ->greeting('Maintenance request created')
            ->line("{$this->request->title} was reported for {$this->request->property->title}.")
            ->line('Priority: '.$this->request->priority->label())
            ->action('Open maintenance workspace', route('maintenance.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New maintenance request',
            'message' => "{$this->request->title} was reported for {$this->request->property->title}.",
            'action_url' => route('maintenance.index'),
            'action_label' => 'Review request',
            'maintenance_request_id' => $this->request->getKey(),
        ];
    }
}
