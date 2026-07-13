<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestUpdatedNotification extends Notification implements ShouldQueue
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
        $message = (new MailMessage())
            ->subject('Maintenance request updated')
            ->greeting('Maintenance request update')
            ->line("{$this->request->title} is now {$this->request->status->label()}.");

        if ($this->request->updates->isNotEmpty() && $this->request->updates->first()->message) {
            $message->line('Latest note: '.$this->request->updates->first()->message);
        }

        return $message->action('Open maintenance workspace', route('maintenance.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Maintenance request updated',
            'message' => "{$this->request->title} is now {$this->request->status->label()}.",
            'action_url' => route('maintenance.index'),
            'action_label' => 'View request',
            'maintenance_request_id' => $this->request->getKey(),
        ];
    }
}
