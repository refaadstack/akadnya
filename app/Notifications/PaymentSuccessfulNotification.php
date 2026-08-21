<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\BrevoMailChannel;
use App\Notifications\Channels\CloudMailMailChannel;
use App\Services\CloudMailMailer;
use Illuminate\Bus\Queueable;
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
        return app(CloudMailMailer::class)->enabled()
            ? [CloudMailMailChannel::class]
            : [BrevoMailChannel::class];
    }

    /**
     * @return array{subject: string, htmlContent: string, textContent: string}
     */
    public function toBrevoMail(object $notifiable): array
    {
        return $this->payload($notifiable);
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
            'subject' => 'Pembayaran MyAkad Berhasil',
            'htmlContent' => <<<HTML
<h1>Pembayaran Berhasil</h1>
<p>Halo {$name},</p>
<p>Pembayaran kamu sudah berhasil kami terima.</p>
<p><strong>Nomor order:</strong> {$orderNumber}<br><strong>Total pembayaran:</strong> {$escapedAmount}</p>
<p>Fitur dan undangan yang kamu beli sudah aktif di akun MyAkad.</p>
<p><a href="{$escapedDashboardUrl}" style="display:inline-block;padding:12px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:6px;">Buka Dashboard MyAkad</a></p>
<p>Terima kasih sudah menggunakan MyAkad.</p>
HTML,
            'textContent' => "Halo {$notifiable->name},\n\nPembayaran kamu berhasil.\nNomor order: {$order->order_number}\nTotal pembayaran: {$amount}\n\nBuka dashboard: {$dashboardUrl}\n",
        ];
    }
}
