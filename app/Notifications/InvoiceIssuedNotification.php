<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Invoice $invoice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('A new invoice has been issued')
            ->greeting('Invoice issued')
            ->line("Invoice {$this->invoice->invoice_number} for {$this->invoice->tenancy->property->title} is now available.")
            ->line('Due date: '.$this->invoice->due_date->format('M j, Y'))
            ->line('Amount due: NGN '.number_format((float) $this->invoice->total_amount, 2))
            ->action('Open billing workspace', route('billing.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New invoice issued',
            'message' => "Invoice {$this->invoice->invoice_number} is ready for payment.",
            'action_url' => route('billing.index'),
            'action_label' => 'View billing',
            'invoice_id' => $this->invoice->getKey(),
        ];
    }
}
