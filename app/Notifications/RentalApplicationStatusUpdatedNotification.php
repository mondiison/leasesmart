<?php

namespace App\Notifications;

use App\Models\RentalApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalApplicationStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected RentalApplication $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject('Your rental application has been updated')
            ->greeting('Rental application update')
            ->line("Your application for {$this->application->property->title} is now {$this->application->status->label()}.");

        if ($this->application->review_notes) {
            $message->line('Review note: '.$this->application->review_notes);
        }

        return $message->action('View property', route('marketplace.show', $this->application->property));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Rental application updated',
            'message' => "Your application for {$this->application->property->title} is now {$this->application->status->label()}.",
            'action_url' => route('marketplace.show', $this->application->property),
            'action_label' => 'View property',
            'application_id' => $this->application->getKey(),
        ];
    }
}
