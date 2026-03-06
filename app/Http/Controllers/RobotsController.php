<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Stringable;

/**
 * RobotsController
 *
 * HTTP controller handling RobotsController related web requests, responses, and business logic with proper validation and error handling.
 */
class RobotsController extends Controller
{
    /**
     * Handle __invoke functionality with proper error handling.
     */
    public function __invoke(): Response
    {
        // Resolve the application URL so we can derive a stable host and scheme for the sitemap entry.
        $configuredAppUrl = config('app.url');

        // Safely coerce the application URL to a string because the configuration helper returns mixed values.
        if (is_string($configuredAppUrl)) {
            $appUrl = $configuredAppUrl;
        } elseif ($configuredAppUrl instanceof Stringable) {
            $appUrl = (string) $configuredAppUrl;
        } else {
            $appUrl = '';
        }

        // Extract the host, defaulting to the current request host when configuration is missing or malformed.
        $host = parse_url($appUrl, PHP_URL_HOST);
        $host = is_string($host) && $host !== '' ? $host : request()->getHost();

        // Extract the scheme, falling back to the incoming request scheme (or https) when necessary.
        $scheme = parse_url($appUrl, PHP_URL_SCHEME);
        $scheme = is_string($scheme) && $scheme !== '' ? $scheme : (request()->getScheme() ?: 'https');

        // Compose robots.txt directives with one locale-agnostic sitemap URL.
        $lines = [
            'User-agent: *',
            'Disallow: /admin/',
            sprintf('Sitemap: %s://%s/sitemap.xml', $scheme, $host),
        ];

        // Join the robots rules with Unix newlines and ensure a trailing newline as search engines expect.
        $content = implode(PHP_EOL, $lines) . PHP_EOL;

        // Return the plain-text response with the generated robots.txt body.
        return response($content, Response::HTTP_OK)->header('Content-Type', 'text/plain');
    }
}
