<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class SaskaitaInvoiceClient
{
    private const DEFAULT_SELLER_WEBSITE = 'https://example.com';

    private const RETRY_SELLER_WEBSITE = 'https://www.example.com';

    /**
     * @param array<string, mixed> $payload
     */
    public function initiateInvoice(array $payload): string
    {
        $payload = $this->stripNullValues($payload);
        $payload = $this->normalizeSellerPayload($payload);
        $response = $this->postPdf('/api/initiate', $payload);

        if (! $response->successful() && $this->isSellerWebsiteValidationError($response->body())) {
            $fallbackPayload = $this->withFallbackSellerWebsite($payload);

            if ($fallbackPayload !== null) {
                $response = $this->postPdf('/api/initiate', $fallbackPayload);
            }
        }

        if (! $response->successful()) {
            throw $this->requestException(
                __('messages.invoice_initiate_request_failed'),
                $response->status(),
                $response->body(),
            );
        }

        $body = $response->body();

        if ($body === '') {
            throw new RuntimeException(__('messages.invoice_pdf_response_empty'));
        }

        return $body;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function postPdf(string $uri, array $payload): Response
    {
        return Http::baseUrl($this->baseUrl())
            ->accept('application/pdf')
            ->asJson()
            ->withHeaders($this->authorizationHeader())
            ->timeout($this->timeoutSeconds())
            ->retry($this->retryTimes(), $this->retrySleepMilliseconds(), null, false)
            ->post($uri, $payload);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listInvoices(string $apiToken): array
    {
        $response = $this->postJson('/api/actions/list-invoices', [
            'api_token' => $apiToken,
        ]);

        $invoices = $response['invoices'] ?? null;

        return is_array($invoices) ? array_values(array_filter($invoices, 'is_array')) : [];
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function postJson(string $uri, array $payload): array
    {
        $payload = $this->stripNullValues($payload);

        $response = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->withHeaders($this->authorizationHeader())
            ->timeout($this->timeoutSeconds())
            ->retry($this->retryTimes(), $this->retrySleepMilliseconds(), null, false)
            ->post($uri, $payload);

        if (! $response->successful()) {
            throw $this->requestException(
                __('messages.invoice_api_request_failed_for_uri', ['uri' => $uri]),
                $response->status(),
                $response->body(),
            );
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string, string>
     */
    private function authorizationHeader(): array
    {
        $token = trim((string) config('invoices.auth_bearer', ''));

        if ($token === '') {
            return [];
        }

        return ['Authorization' => $token];
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('invoices.base_url', 'https://saskaita.vercel.app'), '/');
    }

    private function timeoutSeconds(): int
    {
        return max(5, (int) config('invoices.timeout_seconds', 20));
    }

    private function retryTimes(): int
    {
        return max(1, (int) config('invoices.retry_times', 3));
    }

    private function retrySleepMilliseconds(): int
    {
        return max(50, (int) config('invoices.retry_sleep_ms', 250));
    }

    private function requestException(string $message, int $status, string $body): RuntimeException
    {
        $decodedBody = json_decode($body, true);
        $statusMessage = is_array($decodedBody) && isset($decodedBody['statusMessage']) && is_scalar($decodedBody['statusMessage'])
            ? trim((string) $decodedBody['statusMessage'])
            : '';
        $providerMessage = is_array($decodedBody) && isset($decodedBody['message']) && is_scalar($decodedBody['message'])
            ? trim((string) $decodedBody['message'])
            : '';

        $details = collect([$statusMessage, $providerMessage])
            ->filter(static fn (string $item): bool => $item !== '')
            ->unique()
            ->implode(' / ');

        return new RuntimeException(__('messages.invoice_api_http_error', [
            'message' => $message,
            'status'  => $status,
            'details' => $details !== '' ? " {$details}" : '',
        ]));
    }

    /**
     * @param  array<string, mixed>      $payload
     * @return array<string, mixed>|null
     */
    private function withFallbackSellerWebsite(array $payload): ?array
    {
        $seller = is_array($payload['seller'] ?? null) ? $payload['seller'] : [];
        $website = is_scalar($seller['website'] ?? null) ? trim((string) $seller['website']) : null;

        if ($website === self::RETRY_SELLER_WEBSITE) {
            return null;
        }

        $seller['website'] = self::RETRY_SELLER_WEBSITE;
        $payload['seller'] = $seller;

        return $payload;
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizeSellerPayload(array $payload): array
    {
        $seller = is_array($payload['seller'] ?? null) ? $payload['seller'] : [];
        $website = is_scalar($seller['website'] ?? null) ? trim((string) $seller['website']) : null;
        $normalizedWebsite = $this->normalizeAbsoluteUrl($website);

        if ($normalizedWebsite === null || ! $this->isProviderSafeWebsite($normalizedWebsite)) {
            $configured = is_scalar(config('invoices.seller_website')) ? trim((string) config('invoices.seller_website')) : null;
            $normalizedConfigured = $this->normalizeAbsoluteUrl($configured);
            $normalizedWebsite = $normalizedConfigured !== null && $this->isProviderSafeWebsite($normalizedConfigured)
                ? $normalizedConfigured
                : self::DEFAULT_SELLER_WEBSITE;
        }

        $seller['website'] = $normalizedWebsite;
        $payload['seller'] = $seller;

        return $payload;
    }

    private function isSellerWebsiteValidationError(string $body): bool
    {
        if ($body === '') {
            return false;
        }

        $decoded = json_decode($body, true);
        $statusMessage = is_array($decoded) && isset($decoded['statusMessage']) && is_scalar($decoded['statusMessage'])
            ? strtolower(trim((string) $decoded['statusMessage']))
            : '';
        $message = is_array($decoded) && isset($decoded['message']) && is_scalar($decoded['message'])
            ? strtolower(trim((string) $decoded['message']))
            : strtolower($body);

        if ($statusMessage !== 'validation-error') {
            return false;
        }

        return str_contains($message, 'seller')
            && str_contains($message, 'website')
            && (str_contains($message, 'invalid url') || str_contains($message, 'invalid_format'));
    }

    private function isProviderSafeWebsite(string $website): bool
    {
        if (filter_var($website, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $host = parse_url($website, PHP_URL_HOST);
        if (! is_string($host) || trim($host) === '') {
            return false;
        }

        $normalizedHost = strtolower(trim($host));

        if ($normalizedHost === 'localhost' || $normalizedHost === '127.0.0.1' || $normalizedHost === '::1') {
            return false;
        }

        return ! str_ends_with($normalizedHost, '.test')
            && ! str_ends_with($normalizedHost, '.local')
            && ! str_ends_with($normalizedHost, '.localhost');
    }

    private function normalizeAbsoluteUrl(?string $candidate): ?string
    {
        if ($candidate === null) {
            return null;
        }

        $normalized = trim($candidate);
        if ($normalized === '') {
            return null;
        }

        if (! str_starts_with(strtolower($normalized), 'http://') && ! str_starts_with(strtolower($normalized), 'https://')) {
            $normalized = 'https://' . ltrim($normalized, '/');
        }

        return filter_var($normalized, FILTER_VALIDATE_URL) !== false ? $normalized : null;
    }

    private function stripNullValues(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $result = [];

        foreach ($value as $key => $item) {
            if ($item === null) {
                continue;
            }

            $result[$key] = $this->stripNullValues($item);
        }

        return array_is_list($result) ? array_values($result) : $result;
    }
}
