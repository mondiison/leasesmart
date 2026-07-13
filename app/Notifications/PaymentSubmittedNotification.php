<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('A payment needs verification')
            ->greeting('Payment awaiting review')
            ->line("Payment {$this->payment->payment_reference} was submitted for invoice {$this->payment->invoice?->invoice_number}.")
            ->line('Amount: NGN '.number_format((float) $this->payment->amount, 2))
            ->action('Open billing queue', route('billing.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment submitted',
            'message' => "Payment {$this->payment->payment_reference} is awaiting verification.",
            'action_url' => route('billing.index'),
            'action_label' => 'Review payment',
            'payment_id' => $this->payment->getKey(),
        ];
    }
}
