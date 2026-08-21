<?php

namespace App\Notifications;

use App\Notifications\Channels\BrevoMailChannel;
use App\Notifications\Channels\CloudMailMailChannel;
use App\Services\CloudMailMailer;
use Illuminate\Auth\Notifications\VerifyEmail;

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
        return app(CloudMailMailer::class)->enabled()
            ? [CloudMailMailChannel::class]
            : [BrevoMailChannel::class];
    }

    /**
     * Build the payload for Brevo.
     *
     * @param  mixed  $notifiable
     * @return array{subject:string,htmlContent:string,textContent:string}
     */
    public function toBrevoMail($notifiable): array
    {
        return $this->payload($notifiable);
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

        return [
            'subject' => 'Verifikasi Email MyAkad',

            'htmlContent' => <<<HTML
                <h1>Verifikasi Email MyAkad</h1>

                <p>Halo {$name},</p>

                <p>
                Terima kasih sudah membuat akun MyAkad.
                Klik tombol di bawah ini untuk mengaktifkan akun kamu.
                </p>

                <p>
                    <a href="{$escapedUrl}" style="display:inline-block;padding:12px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:6px;">
                        Verifikasi Email
                    </a>
                </p>

                <p>Kalau tombol tidak bisa dibuka, gunakan link berikut:</p>

                <p>
                    <a href="{$escapedUrl}">
                        {$escapedUrl}
                    </a>
                </p>
                HTML,

            'textContent' => <<<TEXT
                Halo {$notifiable->name},

                Terima kasih sudah membuat akun MyAkad.

                Verifikasi email kamu melalui link berikut:

                {$url}
                TEXT,
        ];
    }
}
