<?php

namespace App\Mail\Transports;

use App\Services\CloudMailMailer;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class CloudMailTransport extends AbstractTransport
{
    public function __construct(
        protected CloudMailMailer $mailer,
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'cloudmail';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $recipients = array_map(
            fn (Address $address): array => ['email' => $address->getAddress(), 'name' => $address->getName() ?: null],
            array_merge($email->getTo(), $email->getCc(), $email->getBcc()),
        );

        $from = $email->getFrom()[0] ?? null;

        $this->mailer->send(
            $from?->getAddress() ?? (string) config('mail.from.address'),
            $recipients,
            $email->getSubject() ?? '',
            $email->getHtmlBody() ?? '',
            $email->getTextBody(),
            $from?->getName() ?: null,
        );
    }
}
