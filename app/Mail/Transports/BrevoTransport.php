<?php

namespace App\Mail\Transports;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    public function __toString(): string
    {
        return 'brevo';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = array_map(
            fn ($address): array => ['email' => $address->getAddress(), 'name' => $address->getName() ?: null],
            array_merge($email->getTo(), $email->getCc(), $email->getBcc()),
        );

        $from = $email->getFrom()[0];

        $payload = [
            'sender' => [
                'email' => $from->getAddress(),
                'name' => $from->getName() ?: null,
            ],
            'to' => $to,
            'subject' => $email->getSubject() ?? '',
        ];

        if ($html = $email->getHtmlBody()) {
            $payload['htmlContent'] = $html;
        }

        if ($text = $email->getTextBody()) {
            $payload['textContent'] = $text;
        }

        $replyTo = $email->getReplyTo()[0] ?? null;

        if ($replyTo) {
            $payload['replyTo'] = ['email' => $replyTo->getAddress()];
        }

        $response = Http::withHeaders([
            'api-key' => (string) config('services.brevo.key'),
            'content-type' => 'application/json',
            'accept' => 'application/json',
        ])
            ->timeout(20)
            ->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Brevo API error '.$response->status().': '.$response->body(),
            );
        }
    }
}
