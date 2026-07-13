<?php

namespace App\Notifications;

use App\Models\RentalApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalApplicationReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected RentalApplication $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('We received your rental application')
            ->greeting('Application received')
            ->line("Thanks {$this->application->applicant_name}, we received your application for {$this->application->property->title}.")
            ->line('Unit: '.($this->application->unit?->unit_name ?? 'Not specified'))
            ->line('Our review team will check your application and contact you with the next step.');
    }
}
