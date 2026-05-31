<?php

namespace App\Notifications;

use App\Notifications\Channels\BrevoMailChannel;
use Illuminate\Auth\Notifications\VerifyEmail;

class VerifyEmailViaBrevo extends VerifyEmail
{
    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [BrevoMailChannel::class];
    }

    /**
     * @return array{subject: string, htmlContent: string, textContent: string}
     */
    public function toBrevoMail(object $notifiable): array
    {
        $url = $this->verificationUrl($notifiable);
        $name = e($notifiable->name);
        $escapedUrl = e($url);

        return [
            'subject' => 'Verifikasi Email MyAkad',
            'htmlContent' => <<<HTML
<h1>Verifikasi Email MyAkad</h1>
<p>Halo {$name},</p>
<p>Terima kasih sudah membuat akun MyAkad. Klik tombol di bawah ini untuk mengaktifkan akun kamu.</p>
<p><a href="{$escapedUrl}" style="display:inline-block;padding:12px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:6px;">Verifikasi Email</a></p>
<p>Kalau tombol tidak bisa dibuka, salin link ini ke browser:</p>
<p><a href="{$escapedUrl}">{$escapedUrl}</a></p>
HTML,
            'textContent' => "Halo {$notifiable->name},\n\nKlik link ini untuk verifikasi email MyAkad:\n{$url}\n",
        ];
    }
}
