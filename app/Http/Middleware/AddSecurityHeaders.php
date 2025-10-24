<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Security\CspNonce;
use Closure;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AddSecurityHeaders
{
    public function __construct(private readonly ConfigRepository $config) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Resolve (or create) the nonce before the request proceeds so downstream handlers share the same value.
        $nonce = $this->resolveNonce($request);

        /** @var Response $response */
        $response = $next($request);

        if (! $this->config->get('security.headers.enabled', true)) {
            return $response;
        }

        $this->applyStaticHeaders($response);
        $this->applyPermissionsPolicy($response);
        $this->applyStrictTransportSecurity($response);
        $this->applyContentSecurityPolicy($response, $nonce);
        $this->injectNonceIntoResponse($response, $nonce);

        return $response;
    }

    private function resolveNonce(Request $request): CspNonce
    {
        $nonce = $request->attributes->get('csp_nonce');

        if ($nonce instanceof CspNonce) {
            View::share('cspNonce', $nonce->value());

            return $nonce;
        }

        $resolved = is_string($nonce) && $nonce !== ''
            ? new CspNonce($nonce)
            : app(CspNonce::class);

        // Share the nonce with both the current request context and Blade templates for compatibility.
        $request->attributes->set('csp_nonce', $resolved);
        View::share('cspNonce', $resolved->value());

        return $resolved;
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

            // Always merge headers so existing security values are replaced atomically.
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

            $compiled[] = $feature . '=' . $sources;
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

    private function applyContentSecurityPolicy(Response $response, ?CspNonce $nonce): void
    {
        $directives = $this->config->get('security.headers.content_security_policy.directives', []);
        if (! is_array($directives) || $directives === []) {
            return;
        }

        $useNonce = (bool) $this->config->get('security.headers.content_security_policy.use_nonce', true);
        $nonce = $useNonce ? $nonce : null;
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

            $compiled[] = $directive . ' ' . implode(' ', $sources);
        }

        if ($compiled === []) {
            return;
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $compiled), true);
    }

    private function injectNonceIntoResponse(Response $response, ?CspNonce $nonce): void
    {
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return;
        }

        if (app()->runningUnitTests()) {
            return;
        }

        $contentType = $response->headers->get('Content-Type');
        if (! is_string($contentType) || ! Str::contains(Str::lower($contentType), 'text/html')) {
            return;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '') {
            return;
        }

        $originalContent = null;
        $shouldRestoreOriginal = false;

        if ($response instanceof HttpResponse) {
            $originalContent = $response->getOriginalContent();
            $shouldRestoreOriginal = $originalContent instanceof Renderable;
        }

        $nonceValue = $nonce?->value();
        if (! is_string($nonceValue) || $nonceValue === '') {
            return;
        }

        // Inject nonces into inline scripts and styles so they satisfy strict CSP directives.
        $scriptPattern = '/<script(?![^>]*\\bsrc=)(?![^>]*\\bnonce=)([^>]*)>/i';
        $stylePattern = '/<style(?![^>]*\\bnonce=)([^>]*)>/i';

        $updated = preg_replace($scriptPattern, '<script$1 nonce="' . $nonceValue . '">', $content);
        if ($updated === null) {
            return;
        }

        $updated = preg_replace($stylePattern, '<style$1 nonce="' . $nonceValue . '">', $updated);
        if ($updated === null) {
            return;
        }

        $response->setContent($updated);

        if ($shouldRestoreOriginal && $response instanceof HttpResponse) {
            $response->original = $originalContent;
        }
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

        return '(' . implode(' ', array_values(array_unique($sources))) . ')';
    }

    /**
     * @param  array<mixed, mixed>|string|null $values
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
