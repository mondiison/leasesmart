<?php

namespace App\Notifications;

use App\Models\Tenancy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaseExpiryAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Tenancy $tenancy,
        protected int $daysRemaining,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->daysRemaining === 0 ? 'today' : "in {$this->daysRemaining} days";

        return (new MailMessage())
            ->subject('Lease expiry alert')
            ->greeting('Lease renewal reminder')
            ->line("The lease for {$this->tenancy->tenant_name} at {$this->tenancy->property->title} expires {$label}.")
            ->line('Lease end date: '.$this->tenancy->lease_end_date?->format('M j, Y'))
            ->action('Open tenancies workspace', route('tenancies.index'));
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->daysRemaining === 0 ? 'today' : "in {$this->daysRemaining} days";

        return [
            'title' => 'Lease expiry alert',
            'message' => "The lease for {$this->tenancy->tenant_name} expires {$label}.",
            'action_url' => route('tenancies.index'),
            'action_label' => 'Review tenancy',
            'tenancy_id' => $this->tenancy->getKey(),
            'days_remaining' => $this->daysRemaining,
        ];
    }
}
