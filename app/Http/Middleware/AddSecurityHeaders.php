<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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

    private function applyContentSecurityPolicy(Response $response): void
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

            $sources = $this->normaliseSources($values);
            if ($sources === []) {
                continue;
            }

            $compiled[] = $directive.' '.implode(' ', $sources);
        }

        if ($compiled === []) {
            return;
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $compiled));
    }

    /**
     * @param  array<mixed, mixed>|string  $values
     * @return array<int, string>
     */
    private function normaliseSources(array|string $values): array
    {
        if (is_string($values)) {
            $values = [$values];
        }

        $sources = [];

        foreach ($values as $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            $sources[] = $value;
        }

        return array_values(array_unique($sources));
    }
}
