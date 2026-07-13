<?php

namespace App\Notifications;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyReportDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected User $recipient, protected CarbonImmutable $reportDate)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject('Your LeaseSmart weekly report digest')
            ->greeting('Weekly report digest')
            ->line('Your weekly LeaseSmart reports are ready.')
            ->line('Open the premium report links for charted summaries, or use CSV exports for reconciliation and offline analysis.');

        foreach ($this->links() as $link) {
            $mail->line($link['label'].': '.$link['url']);
        }

        return $mail->action('Open billing report', route('reports.premium', ['type' => 'billing-invoices']));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Weekly report digest ready',
            'message' => 'Premium reports and CSV exports are ready for this week.',
            'action_url' => route('reports.premium', ['type' => 'billing-invoices']),
            'action_label' => 'Open report',
            'report_date' => $this->reportDate->toDateString(),
            'links' => $this->links(),
        ];
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    protected function links(): array
    {
        return [
            ['label' => 'Premium billing report', 'url' => route('reports.premium', ['type' => 'billing-invoices'])],
            ['label' => 'Premium tenancy report', 'url' => route('reports.premium', ['type' => 'tenancies'])],
            ['label' => 'Premium maintenance report', 'url' => route('reports.premium', ['type' => 'maintenance'])],
            ['label' => 'Premium applications report', 'url' => route('reports.premium', ['type' => 'applications'])],
            ['label' => 'Invoices CSV', 'url' => route('exports.show', ['type' => 'billing-invoices'])],
            ['label' => 'Payments CSV', 'url' => route('exports.show', ['type' => 'billing-payments'])],
            ['label' => 'Tenancies CSV', 'url' => route('exports.show', ['type' => 'tenancies'])],
            ['label' => 'Maintenance CSV', 'url' => route('exports.show', ['type' => 'maintenance'])],
        ];
    }
}
