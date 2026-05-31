<?php

namespace App\Notifications;

use App\Notifications\Channels\BrevoMailChannel;
use Illuminate\Auth\Notifications\VerifyEmail;

class VerifyEmailViaBrevo extends VerifyEmail
{
    /**
     * Get the notification delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return [BrevoMailChannel::class];
    }

    /**
     * Build the payload for Brevo.
     *
     * @param mixed $notifiable
     * @return array{subject:string,htmlContent:string,textContent:string}
     */
    public function toBrevoMail($notifiable): array
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