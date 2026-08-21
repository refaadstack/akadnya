<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudMailMailer
{
    protected ?string $token = null;

    /**
     * Determine whether the CloudMail integration is enabled.
     */
    public function enabled(): bool
    {
        return (bool) config('services.cloudmail.enabled');
    }

    /**
     * Send an email using the sender account that matches the given address.
     *
     * @param  array<int, array{email: string, name?: string|null}>  $to
     */
    public function send(string $fromEmail, array $to, string $subject, string $htmlContent, ?string $textContent = null, ?string $fromName = null): void
    {
        if ($to === []) {
            throw new RuntimeException('CloudMail requires at least one recipient.');
        }

        $payload = [
            'accountId' => $this->accountIdFor($fromEmail),
            'receiveEmail' => array_map(fn (array $recipient): string => $recipient['email'], $to),
            'subject' => $subject,
            'content' => $htmlContent,
            'name' => $fromName ?? $this->accountNameFor($fromEmail),
        ];

        if ($textContent !== null && $textContent !== '') {
            $payload['text'] = $textContent;
        }

        $response = $this->withReauth(fn (): Response => $this->request()->post($this->url('/email/send'), $payload));

        $this->assertSuccess($response);
    }

    /**
     * Send an email using a named sender configured in services.cloudmail.senders.
     *
     * @param  array<int, array{email: string, name?: string|null}>  $to
     */
    public function sendAs(string $senderKey, array $to, string $subject, string $htmlContent, ?string $textContent = null): void
    {
        $fromEmail = config("services.cloudmail.senders.{$senderKey}");

        if (! is_string($fromEmail) || $fromEmail === '') {
            throw new RuntimeException("CloudMail sender [{$senderKey}] is not configured.");
        }

        $this->send($fromEmail, $to, $subject, $htmlContent, $textContent, config('mail.from.name'));
    }

    /**
     * Find the CloudMail account id for the given sender address.
     */
    public function accountIdFor(string $fromEmail): int
    {
        $account = $this->accounts()->first(
            fn (array $account): bool => strtolower($account['email']) === strtolower($fromEmail)
        );

        if ($account === null) {
            throw new RuntimeException("CloudMail sender account [{$fromEmail}] was not found for the authenticated user.");
        }

        return $account['accountId'];
    }

    /**
     * List the sender accounts owned by the authenticated CloudMail user.
     *
     * @return Collection<int, array{accountId: int, email: string, name: string|null}>
     */
    public function accounts(): Collection
    {
        $cached = Cache::get($this->cacheKey('accounts'));

        if (is_array($cached)) {
            return collect($cached);
        }

        $response = $this->withReauth(fn (): Response => $this->request()->get($this->url('/account/list'), ['size' => 200]));

        $this->assertSuccess($response);

        $rows = $response->json('data');

        if (is_array($rows) && array_key_exists('list', $rows)) {
            $rows = $rows['list'];
        }

        $accounts = collect($rows ?? [])->map(fn (array $row): array => [
            'accountId' => (int) $row['accountId'],
            'email' => (string) $row['email'],
            'name' => $row['name'] ?? null,
        ])->values();

        Cache::put($this->cacheKey('accounts'), $accounts->all(), $this->cacheTtl());

        return $accounts;
    }

    protected function accountNameFor(string $fromEmail): ?string
    {
        $account = $this->accounts()->first(
            fn (array $account): bool => strtolower($account['email']) === strtolower($fromEmail)
        );

        return $account['name'] ?? null;
    }

    protected function withReauth(callable $attempt): Response
    {
        $response = $attempt();

        if ($response->status() === 401) {
            $this->flushToken();
            $response = $attempt();
        }

        return $response;
    }

    protected function request(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $this->token(),
        ])->acceptJson()->timeout((int) config('services.cloudmail.timeout', 20));
    }

    protected function token(): string
    {
        if (is_string($this->token) && $this->token !== '') {
            return $this->token;
        }

        $cached = Cache::get($this->cacheKey('token'));

        if (is_string($cached) && $cached !== '') {
            return $this->token = $cached;
        }

        return $this->token = $this->login();
    }

    protected function login(): string
    {
        $email = config('services.cloudmail.email');
        $password = config('services.cloudmail.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            throw new RuntimeException('CloudMail credentials are not configured.');
        }

        $response = Http::asJson()
            ->timeout((int) config('services.cloudmail.timeout', 20))
            ->post($this->url('/login'), ['email' => $email, 'password' => $password]);

        if ($response->failed()) {
            throw new RuntimeException(sprintf('CloudMail login failed (%s): %s', $response->status(), $response->body()));
        }

        $token = $response->json('data.token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException(sprintf('CloudMail login failed: %s', $response->json('message') ?? 'no token returned'));
        }

        Cache::put($this->cacheKey('token'), $token, $this->cacheTtl());

        return $token;
    }

    protected function flushToken(): void
    {
        $this->token = null;
        Cache::forget($this->cacheKey('token'));
    }

    protected function assertSuccess(Response $response): void
    {
        if ($response->failed()) {
            throw new RuntimeException(sprintf('CloudMail API error (%s): %s', $response->status(), $response->body()));
        }

        if (($response->json('code') ?? 0) !== 200) {
            throw new RuntimeException(sprintf('CloudMail API error: %s', $response->json('message') ?? 'unknown error'));
        }
    }

    protected function url(string $path): string
    {
        return rtrim((string) config('services.cloudmail.base_url'), '/').$path;
    }

    protected function cacheKey(string $suffix): string
    {
        $identity = md5((string) config('services.cloudmail.email'));

        return "cloudmail:{$identity}:{$suffix}";
    }

    protected function cacheTtl(): int
    {
        return (int) config('services.cloudmail.cache_ttl', 3600);
    }
}
