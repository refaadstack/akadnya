<?php

namespace App\Notifications;

use App\Notifications\Channels\CloudMailMailChannel;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Channels\MailChannel;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Get the notification delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, class-string>
     */
    public function via($notifiable): array
    {
        return config('services.cloudmail.enabled')
            ? [CloudMailMailChannel::class]
            : [MailChannel::class];
    }

    /**
     * Build the mailable used when the standard SMTP channel is active.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): Mailable
    {
        $payload = $this->payload($notifiable);
        $recipient = $notifiable->routeNotificationFor('mail', $this) ?? $notifiable->email;

        return (new Mailable)
            ->to($recipient)
            ->subject($payload['subject'])
            ->html($payload['htmlContent']);
    }

    /**
     * Build the payload for CloudMail.
     *
     * @param  mixed  $notifiable
     * @return array{sender:string,subject:string,htmlContent:string,textContent:string}
     */
    public function toCloudMailMail($notifiable): array
    {
        return ['sender' => 'registration'] + $this->payload($notifiable);
    }

    /**
     * Build the shared message payload.
     *
     * @param  mixed  $notifiable
     * @return array{subject:string,htmlContent:string,textContent:string}
     */
    protected function payload($notifiable): array
    {
        $url = $this->verificationUrl($notifiable);

        $name = e($notifiable->name ?? 'User');
        $escapedUrl = e($url);

        $body = <<<HTML
            <h1 style="margin:0 0 16px 0;font-size:22px;line-height:30px;color:#141F1A;font-weight:bold;">Verifikasi Email Akadnya.com</h1>

            <p style="margin:0 0 12px 0;font-size:15px;line-height:24px;color:#141F1A;">Halo {$name},</p>

            <p style="margin:0 0 24px 0;font-size:15px;line-height:24px;color:#141F1A;">
                Terima kasih sudah membuat akun Akadnya.com.
                Klik tombol di bawah ini untuk mengaktifkan akun kamu.
            </p>

            HTML;

        $body .= \App\Services\EmailTemplate::button($url, 'Verifikasi Email');

        $body .= <<<HTML
            <p style="margin:0 0 8px 0;font-size:14px;line-height:22px;color:#7A6B4A;">Atau salin link ini ke browser:</p>
            <p style="margin:0 0 24px 0;word-break:break-all;font-size:13px;line-height:20px;color:#AD7F35;">
                <a href="{$escapedUrl}" style="color:#AD7F35;text-decoration:underline;">{$escapedUrl}</a>
            </p>

            <p style="margin:0;font-size:13px;line-height:20px;color:#7A6B4A;">
                Email tidak muncul di inbox? Coba periksa folder
                <strong>Spam</strong> atau <strong>Promosi</strong>.
                Batas waktu verifikasi: 60 menit.
            </p>
        HTML;

        return [
            'subject' => 'Verifikasi Email Akadnya.com',
            'htmlContent' => \App\Services\EmailTemplate::wrap('Verifikasi Email Akadnya.com', $body),
            'textContent' => <<<TEXT
                Halo {$notifiable->name},

                Terima kasih sudah membuat akun Akadnya.com.

                Verifikasi email kamu melalui link berikut:

                {$url}

                Email tidak muncul? Periksa folder Spam atau Promosi.
                Batas waktu verifikasi: 60 menit.
                TEXT,
        ];
    }
}
