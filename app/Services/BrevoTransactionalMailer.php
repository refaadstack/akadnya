<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BrevoTransactionalMailer
{
    /**
     * @param  array<int, array{email: string, name?: string|null}>  $to
     */
    public function send(array $to, string $subject, string $htmlContent, ?string $textContent = null): void
    {
        $apiKey = config('services.brevo.key');

        if (! $apiKey) {
            throw new \RuntimeException('BREVO_API_KEY is not configured.');
        }

        $fromEmail = config('services.brevo.from_address') ?? config('mail.from.address');
        $fromName = config('services.brevo.from_name') ?? config('mail.from.name');

        $payload = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
            ],
            'to' => $to,
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        if ($textContent) {
            $payload['textContent'] = $textContent;
        }

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'api-key' => $apiKey,
            'content-type' => 'application/json',
        ])->timeout(20)->post(config('services.brevo.endpoint'), $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Brevo API error: '.$response->status().' '.$response->body());
        }
    }
}
