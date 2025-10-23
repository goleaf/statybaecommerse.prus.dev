<?php

declare(strict_types=1);

namespace App\Support\Security\Captcha;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use RuntimeException;

final class CaptchaManager
{
    private const SESSION_PREFIX = 'security:captcha:';

    private const REQUIRED_SUFFIX = ':required';

    private const CHALLENGE_SUFFIX = ':challenge';

    public function __construct(private readonly Session $session) {}

    public function shouldChallenge(string $key, string $context): bool
    {
        if (! $this->isEnabled($context)) {
            return false;
        }

        if ($this->session->get($this->requiredSessionKey($context, $key), false) === true) {
            return true;
        }

        $threshold = $this->threshold($context);

        if ($threshold === null) {
            return false;
        }

        if (RateLimiter::attempts($key) >= $threshold) {
            $this->markRequired($key, $context);

            return true;
        }

        return false;
    }

    public function markRequired(string $key, string $context): void
    {
        if (! $this->isEnabled($context)) {
            return;
        }

        $this->session->put($this->requiredSessionKey($context, $key), true);
    }

    public function clear(string $key, string $context): void
    {
        $this->session->forget($this->requiredSessionKey($context, $key));
        $this->session->forget($this->challengeSessionKey($context, $key));
    }

    public function challenge(string $key, string $context, bool $forceRefresh = false): ?CaptchaChallenge
    {
        if (! $this->shouldChallenge($key, $context)) {
            $this->session->forget($this->challengeSessionKey($context, $key));

            return null;
        }

        if ($forceRefresh) {
            $payload = $this->createChallengePayload($context);
            $this->storeChallenge($context, $key, $payload);

            return new CaptchaChallenge($payload['question'], $payload['token']);
        }

        $payload = $this->session->get($this->challengeSessionKey($context, $key));

        if (! $this->isValidPayload($payload) || $this->isExpired($payload)) {
            $payload = $this->createChallengePayload($context);
            $this->storeChallenge($context, $key, $payload);
        }

        return new CaptchaChallenge($payload['question'], $payload['token']);
    }

    public function verify(string $key, string $context, string $token, string $answer): bool
    {
        $payload = $this->session->get($this->challengeSessionKey($context, $key));

        if (! $this->isValidPayload($payload)) {
            return false;
        }

        if (! hash_equals($payload['token'], $token)) {
            return false;
        }

        if ($this->isExpired($payload)) {
            return false;
        }

        $expected = $payload['answer'];
        $actual = $this->hashAnswer($token, $answer);

        if (! hash_equals($expected, $actual)) {
            return false;
        }

        $this->session->forget($this->challengeSessionKey($context, $key));

        return true;
    }

    private function isEnabled(string $context): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        return (bool) data_get($this->config($context), 'enabled', true);
    }

    private function threshold(string $context): ?int
    {
        $threshold = data_get($this->config($context), 'threshold');

        if ($threshold === null) {
            return null;
        }

        $threshold = max(1, (int) $threshold);

        return $threshold > 0 ? $threshold : null;
    }

    private function ttl(string $context): int
    {
        return max(60, (int) data_get($this->config($context), 'ttl_seconds', 600));
    }

    private function createChallengePayload(string $context): array
    {
        $first = random_int(10, 99);
        $second = random_int(1, 9);
        $question = sprintf('What is %d + %d?', $first, $second);
        $token = (string) Str::orderedUuid();
        $answer = (string) ($first + $second);

        return [
            'question'   => $question,
            'token'      => $token,
            'answer'     => $this->hashAnswer($token, $answer),
            'expires_at' => now()->addSeconds($this->ttl($context))->getTimestamp(),
        ];
    }

    private function storeChallenge(string $context, string $key, array $payload): void
    {
        if (! $this->isValidPayload($payload)) {
            throw new RuntimeException('Invalid CAPTCHA payload.');
        }

        $this->session->put($this->challengeSessionKey($context, $key), $payload);
    }

    private function isValidPayload(mixed $payload): bool
    {
        if (! is_array($payload)) {
            return false;
        }

        $question = Arr::get($payload, 'question');
        $token = Arr::get($payload, 'token');
        $answer = Arr::get($payload, 'answer');
        $expiresAt = Arr::get($payload, 'expires_at');

        return is_string($question)
            && $question !== ''
            && is_string($token)
            && $token !== ''
            && is_string($answer)
            && $answer !== ''
            && is_int($expiresAt);
    }

    private function isExpired(array $payload): bool
    {
        $expiresAt = Arr::get($payload, 'expires_at');

        return ! is_int($expiresAt) || $expiresAt <= now()->getTimestamp();
    }

    private function hashAnswer(string $token, string $answer): string
    {
        $normalized = Str::of($answer)->trim()->lower()->value();

        return hash('sha256', $normalized . '|' . $token . '|' . config('app.key'));
    }

    private function requiredSessionKey(string $context, string $key): string
    {
        return $this->baseSessionKey($context, $key) . self::REQUIRED_SUFFIX;
    }

    private function challengeSessionKey(string $context, string $key): string
    {
        return $this->baseSessionKey($context, $key) . self::CHALLENGE_SUFFIX;
    }

    private function baseSessionKey(string $context, string $key): string
    {
        return Str::of(self::SESSION_PREFIX)
            ->append(Str::slug($context))
            ->append('|')
            ->append($key)
            ->value();
    }

    private function config(string $context): array
    {
        return (array) data_get(config('security.captcha'), $context, []);
    }
}
