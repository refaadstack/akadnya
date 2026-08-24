<?php

namespace App\Notifications\Channels;

use App\Services\CloudMailMailer;
use Illuminate\Notifications\Notification;
use Throwable;

class CloudMailMailChannel
{
    public function __construct(
        protected CloudMailMailer $mailer
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toCloudMailMail')) {
            return;
        }

        $message = $notification->toCloudMailMail($notifiable);
        $recipient = $this->recipient($notifiable, $notification);

        try {
            $this->mailer->sendAs(
                $message['sender'] ?? 'default',
                [$recipient],
                $message['subject'],
                $message['htmlContent'],
                $message['textContent'] ?? null,
            );
        } catch (Throwable $e) {
            // A mail outage must never break the surrounding flow (e.g. a
            // registration that already persisted its user). Report and move on.
            report($e);
        }
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
