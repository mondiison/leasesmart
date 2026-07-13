<?php

namespace App\Notifications;

use App\Models\RentalApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalApplicationSubmittedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage())
            ->subject('New rental application submitted')
            ->greeting('New rental application received')
            ->line("{$this->application->applicant_name} applied for {$this->application->property->title}.")
            ->line('Unit: '.($this->application->unit?->unit_name ?? 'Not specified'))
            ->action('Review applications', route('applications.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New rental application',
            'message' => "{$this->application->applicant_name} applied for {$this->application->property->title}.",
            'action_url' => route('applications.index'),
            'action_label' => 'Review application',
            'application_id' => $this->application->getKey(),
        ];
    }
}
