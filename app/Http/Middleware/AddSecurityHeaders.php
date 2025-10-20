<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Security\CspNonce;
use Closure;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AddSecurityHeaders
{
    public function __construct(private readonly ConfigRepository $config) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $this->config->get('security.headers.enabled', true)) {
            return $response;
        }

        $this->applyStaticHeaders($response);
        $this->applyPermissionsPolicy($response);
        $this->applyStrictTransportSecurity($response);
        $this->applyContentSecurityPolicy($response);

        return $response;
    }

    private function applyStaticHeaders(Response $response): void
    {
        $headers = $this->config->get('security.headers.values', []);
        if (! is_array($headers)) {
            return;
        }

        foreach ($headers as $header => $value) {
            if (! is_string($header) || $header === '' || strcasecmp($header, 'Permissions-Policy') === 0 || strcasecmp($header, 'Strict-Transport-Security') === 0) {
                continue;
            }

            $stringValue = is_string($value) ? $value : null;

            if ($stringValue === null || $stringValue === '') {
                continue;
            }

            $response->headers->set($header, $stringValue, true);
        }
    }

    private function applyPermissionsPolicy(Response $response): void
    {
        $policies = $this->config->get('security.headers.permissions_policy', []);
        if (! is_array($policies) || $policies === []) {
            return;
        }

        $compiled = [];

        foreach ($policies as $feature => $values) {
            if (! is_string($feature) || $feature === '') {
                continue;
            }

            $sources = $this->normalisePermissionSources($values);
            if ($sources === null) {
                continue;
            }

            $compiled[] = $feature.'='.$sources;
        }

        if ($compiled === []) {
            return;
        }

        $response->headers->set('Permissions-Policy', implode(', ', $compiled), true);
    }

    private function applyStrictTransportSecurity(Response $response): void
    {
        $config = $this->config->get('security.headers.hsts', []);
        if (! is_array($config) || empty($config['enabled'])) {
            return;
        }

        $maxAge = isset($config['max_age']) ? (int) $config['max_age'] : 0;
        if ($maxAge <= 0) {
            return;
        }

        $parts = ["max-age={$maxAge}"];

        if (! empty($config['include_subdomains'])) {
            $parts[] = 'includeSubDomains';
        }

        if (! empty($config['preload'])) {
            $parts[] = 'preload';
        }

        $response->headers->set('Strict-Transport-Security', implode('; ', $parts), true);
    }

    private function applyContentSecurityPolicy(Response $response): void
    {
        $directives = $this->config->get('security.headers.content_security_policy.directives', []);
        if (! is_array($directives) || $directives === []) {
            return;
        }

        $useNonce = (bool) $this->config->get('security.headers.content_security_policy.use_nonce', true);
        $nonce = $useNonce ? app(CspNonce::class) : null;
        $compiled = [];

        foreach ($directives as $directive => $values) {
            if (! is_string($directive) || $directive === '') {
                continue;
            }

            $sources = $this->normaliseSources($values, $nonce);
            if ($sources === null) {
                continue;
            }

            if ($sources === []) {
                $compiled[] = $directive;
                continue;
            }

            $compiled[] = $directive.' '.implode(' ', $sources);
        }

        if ($compiled === []) {
            return;
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $compiled), true);
    }

    private function normalisePermissionSources(mixed $values): ?string
    {
        if (is_string($values)) {
            $values = [$values];
        }

        if ($values === null) {
            return '()';
        }

        if (! is_array($values)) {
            return null;
        }

        $sources = [];

        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }

            $sources[] = $trimmed;
        }

        if ($sources === []) {
            return '()';
        }

        return '('.implode(' ', array_values(array_unique($sources))).')';
    }

    /**
     * @param  array<mixed, mixed>|string|null  $values
     * @return array<int, string>|null
     */
    private function normaliseSources(array|string|null $values, ?CspNonce $nonce): ?array
    {
        if (is_string($values)) {
            $values = [$values];
        }

        if ($values === null) {
            return [];
        }

        if (! is_array($values)) {
            return null;
        }

        $sources = [];
        $hadValues = false;
        $hadNoncePlaceholder = false;

        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }

            $hadValues = true;

            if ($trimmed === '@nonce') {
                $hadNoncePlaceholder = true;

                if ($nonce !== null) {
                    $sources[] = $nonce->headerValue();
                }

                continue;
            }

            $sources[] = $trimmed;
        }

        if ($sources === [] && $hadValues) {
            return $hadNoncePlaceholder && $nonce === null ? null : [];
        }

        return array_values(array_unique($sources));
    }
}
