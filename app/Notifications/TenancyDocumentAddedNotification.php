<?php

namespace App\Notifications;

use App\Models\Tenancy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenancyDocumentAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Tenancy $tenancy, protected int $documentCount)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->documentCount === 1 ? 'document has' : 'documents have';

        return (new MailMessage())
            ->subject('Tenancy document added')
            ->greeting('Tenancy document update')
            ->line("{$this->documentCount} {$label} been added to your tenancy record for {$this->tenancy->property->title}.")
            ->action('Open tenancy workspace', route('tenancies.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tenancy document added',
            'message' => "{$this->documentCount} document(s) were added to your tenancy record.",
            'action_url' => route('tenancies.index'),
            'action_label' => 'View documents',
            'tenancy_id' => $this->tenancy->getKey(),
        ];
    }
}
