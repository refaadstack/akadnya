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

        return [
            'subject' => 'Pembayaran Akadnya.com Berhasil',
            'htmlContent' => <<<HTML
<h1>Pembayaran Berhasil</h1>
<p>Halo {$name},</p>
<p>Pembayaran kamu sudah berhasil kami terima.</p>
<p><strong>Nomor order:</strong> {$orderNumber}<br><strong>Total pembayaran:</strong> {$escapedAmount}</p>
<p>Fitur dan undangan yang kamu beli sudah aktif di akun Akadnya.com.</p>
<p><a href="{$escapedDashboardUrl}" style="display:inline-block;padding:12px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:6px;">Buka Dashboard Akadnya.com</a></p>
<p>Terima kasih sudah menggunakan Akadnya.com.</p>
HTML,
            'textContent' => "Halo {$notifiable->name},\n\nPembayaran kamu berhasil.\nNomor order: {$order->order_number}\nTotal pembayaran: {$amount}\n\nBuka dashboard: {$dashboardUrl}\n",
        ];
    }
}
