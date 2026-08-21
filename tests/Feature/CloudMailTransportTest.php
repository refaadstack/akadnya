<?php

use App\Mail\Transports\CloudMailTransport;
use App\Services\CloudMailMailer;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

beforeEach(function () {
    config()->set('cache.default', 'array');
    setCloudMailConfig();
});

test('the cloudmail transport delivers mailables through the api', function () {
    fakeCloudMailApi();

    Mail::mailer('cloudmail')->send(
        (new class extends Mailable
        {
            public function envelope(): Envelope
            {
                return new Envelope(
                    from: new Address('no-reply@myakad.id', 'MyAkad'),
                    subject: 'Reset kata sandi',
                );
            }

            public function content(): Content
            {
                return new Content(htmlString: '<p>Klik link reset.</p>');
            }
        })->to('bride@example.com')
    );

    Http::assertSentInOrder([
        fn ($request) => str_contains($request->url(), '/login'),
        fn ($request) => str_contains($request->url(), '/account/list'),
        function ($request) {
            return str_contains($request->url(), '/email/send')
                && $request['accountId'] === 11
                && $request['receiveEmail'] === ['bride@example.com']
                && $request['subject'] === 'Reset kata sandi'
                && $request['content'] === '<p>Klik link reset.</p>'
                && $request['name'] === 'MyAkad';
        },
    ]);
});

test('the transport forwards both html and plain text bodies', function () {
    fakeCloudMailApi();

    $email = (new Email)
        ->from(new \Symfony\Component\Mime\Address('no-reply@myakad.id', 'MyAkad'))
        ->to('bride@example.com')
        ->subject('Undangan siap')
        ->html('<p>Undangan kamu siap.</p>')
        ->text('Undangan kamu siap.');

    (new CloudMailTransport(app(CloudMailMailer::class)))->send($email);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/email/send')
            && $request['content'] === '<p>Undangan kamu siap.</p>'
            && $request['text'] === 'Undangan kamu siap.';
    });
});
