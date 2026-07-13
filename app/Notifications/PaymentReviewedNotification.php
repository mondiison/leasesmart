<?php

namespace App\Notifications;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReviewedNotification extends Notification implements ShouldQueue
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
        $message = (new MailMessage())
            ->subject('Your payment has been reviewed')
            ->greeting('Payment update')
            ->line("Payment {$this->payment->payment_reference} is now {$this->payment->status->label()}.");

        if ($this->payment->status === PaymentStatus::Verified && $this->payment->receipt !== null) {
            $message->line('Receipt number: '.$this->payment->receipt->receipt_number);
        }

        if ($this->payment->rejection_reason) {
            $message->line('Reason: '.$this->payment->rejection_reason);
        }

        return $message->action('Open billing workspace', route('billing.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment reviewed',
            'message' => "Payment {$this->payment->payment_reference} is now {$this->payment->status->label()}.",
            'action_url' => route('billing.index'),
            'action_label' => 'View billing',
            'payment_id' => $this->payment->getKey(),
        ];
    }
}
