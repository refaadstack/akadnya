<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\CloudMailMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;

class PaymentSuccessfulNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return config('services.cloudmail.enabled')
            ? [CloudMailMailChannel::class]
            : [MailChannel::class];
    }

    /**
     * Build the mailable used when the standard SMTP channel is active.
     */
    public function toMail(object $notifiable): Mailable
    {
        $payload = $this->payload($notifiable);
        $recipient = $notifiable->routeNotificationFor('mail', $this) ?? $notifiable->email;

        return (new Mailable)
            ->to($recipient)
            ->subject($payload['subject'])
            ->html($payload['htmlContent']);
    }

    /**
     * @return array{sender: string, subject: string, htmlContent: string, textContent: string}
     */
    public function toCloudMailMail(object $notifiable): array
    {
        return ['sender' => 'payment'] + $this->payload($notifiable);
    }

    /**
     * @return array{subject: string, htmlContent: string, textContent: string}
     */
    protected function payload(object $notifiable): array
    {
        $order = $this->order->loadMissing('items');
        $amount = 'Rp '.number_format((float) $order->total_amount, 0, ',', '.');
        $dashboardUrl = url('/dashboard');
        $name = e($notifiable->name);
        $orderNumber = e($order->order_number);
        $escapedAmount = e($amount);
        $escapedDashboardUrl = e($dashboardUrl);

        $body = <<<HTML
            <h1 style="margin:0 0 16px 0;font-size:22px;line-height:30px;color:#141F1A;font-weight:bold;">Pembayaran Berhasil</h1>

            <p style="margin:0 0 12px 0;font-size:15px;line-height:24px;color:#141F1A;">Halo {$name},</p>

            <p style="margin:0 0 16px 0;font-size:15px;line-height:24px;color:#141F1A;">
                Pembayaran kamu sudah berhasil kami terima. Fitur dan undangan
                yang kamu beli sudah aktif di akun Akadnya.com kamu.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 16px 0;border:1px solid #E8DFC8;border-radius:8px;background-color:#FBF6E8;">
                <tr>
                    <td style="padding:16px 20px;">
                        <p style="margin:0 0 6px 0;font-size:13px;line-height:18px;color:#7A6B4A;">Nomor order</p>
                        <p style="margin:0 0 12px 0;font-size:15px;line-height:22px;color:#141F1A;font-weight:bold;font-family:monospace;">{$orderNumber}</p>
                        <p style="margin:0 0 6px 0;font-size:13px;line-height:18px;color:#7A6B4A;">Total pembayaran</p>
                        <p style="margin:0;font-size:18px;line-height:24px;color:#AD7F35;font-weight:bold;">{$escapedAmount}</p>
                    </td>
                </tr>
            </table>

        HTML;

        $body .= \App\Services\EmailTemplate::button($dashboardUrl, 'Buka Dashboard');

        $body .= <<<HTML
            <p style="margin:24px 0 0 0;font-size:14px;line-height:22px;color:#7A6B4A;">
                Terima kasih sudah menggunakan Akadnya.com untuk momen spesial kamu.
            </p>
        HTML;

        return [
            'subject' => 'Pembayaran Akadnya.com Berhasil',
            'htmlContent' => \App\Services\EmailTemplate::wrap('Pembayaran Berhasil', $body),
            'textContent' => "Halo {$notifiable->name},\n\nPembayaran kamu berhasil.\nNomor order: {$order->order_number}\nTotal pembayaran: {$amount}\n\nBuka dashboard: {$dashboardUrl}\n",
        ];
    }
}
