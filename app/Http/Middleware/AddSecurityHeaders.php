<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Security\CspNonce;
use Closure;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
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
        $nonce = $this->prepareNonce($request);

        /** @var Response $response */
        $response = $next($request);

        if (! $this->config->get('security.headers.enabled', true)) {
            return $response;
        }

        $this->applyStaticHeaders($response);
        $this->applyContentSecurityPolicy($response, $nonce);
        $this->injectNonceIntoResponse($response, $nonce);

        return $response;
    }

    private function prepareNonce(Request $request): string
    {
        $nonce = $request->attributes->get('csp_nonce');

        if (! is_string($nonce) || $nonce === '') {
            $nonce = $this->generateNonce();
            $request->attributes->set('csp_nonce', $nonce);
        }

        View::share('cspNonce', $nonce);

        return $nonce;
    }

    private function generateNonce(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function applyStaticHeaders(Response $response): void
    {
        $headers = $this->config->get('security.headers.values', []);
        if (! is_array($headers)) {
            return;
        }

        foreach ($headers as $header => $value) {
            if (! is_string($header) || $header === '') {
                continue;
            }

            $stringValue = is_string($value) ? $value : null;

            if ($stringValue === null || $stringValue === '') {
                continue;
            }

            $response->headers->set($header, $stringValue, true);
        }
    }

    private function applyContentSecurityPolicy(Response $response, string $nonce): void
    {
        $directives = $this->config->get('security.headers.content_security_policy', []);
        if (! is_array($directives) || $directives === []) {
            return;
        }

        $compiled = [];

        foreach ($directives as $directive => $values) {
            if (! is_string($directive) || $directive === '') {
                continue;
            }

            if (! is_string($values) && ! is_array($values)) {
                continue;
            }

            $sources = $this->normaliseSources($values, $nonce);
            if ($sources === []) {
                continue;
            }

            $compiled[] = $directive.' '.implode(' ', $sources);
        }

        if ($compiled === []) {
            return;
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $compiled), true);
    }

    private function injectNonceIntoResponse(Response $response, string $nonce): void
    {
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
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

        $scriptPattern = '/<script(?![^>]*\bsrc=)(?![^>]*\bnonce=)([^>]*)>/i';
        $stylePattern = '/<style(?![^>]*\bnonce=)([^>]*)>/i';

        $updated = preg_replace($scriptPattern, '<script$1 nonce="'.$nonce.'">', $content);
        if ($updated === null) {
            return;
        }

        $updated = preg_replace($stylePattern, '<style$1 nonce="'.$nonce.'">', $updated);
        if ($updated === null) {
            return;
        }

        $response->setContent($updated);
    }

    /**
     * @param  array<mixed, mixed>|string  $values
     * @return array<int, string>
     */
    private function normaliseSources(array|string $values, string $nonce): array
    {
        if (is_string($values)) {
            $values = [$values];
        }

        $sources = [];

        foreach ($values as $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            if ($value === '@nonce') {
                $sources[] = "'nonce-{$nonce}'";

                continue;
            }

            $sources[] = $value;
        }

        return array_values(array_unique($sources));
    }
}
