<?php

namespace App\Notifications\Channels;

use App\Services\BrevoTransactionalMailer;
use Illuminate\Notifications\Notification;

class BrevoMailChannel
{
    public function __construct(
        protected BrevoTransactionalMailer $mailer
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toBrevoMail')) {
            return;
        }

        $message = $notification->toBrevoMail($notifiable);
        $recipient = $this->recipient($notifiable, $notification);

        $this->mailer->send(
            [$recipient],
            $message['subject'],
            $message['htmlContent'],
            $message['textContent'] ?? null,
        );
    }

    /**
     * @return array{email: string, name?: string|null}
     */
    protected function recipient(object $notifiable, Notification $notification): array
    {
        $route = $notifiable->routeNotificationFor('mail', $notification);

        if (is_array($route)) {
            $email = array_key_first($route);
            $name = $route[$email] ?? null;

            return ['email' => $email, 'name' => $name];
        }

        return [
            'email' => $route ?: $notifiable->email,
            'name' => $notifiable->name ?? null,
        ];
    }
}
