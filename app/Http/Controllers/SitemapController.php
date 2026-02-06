<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * SitemapController
 *
 * HTTP controller handling SitemapController related web requests, responses, and business logic with proper validation and error handling.
 */
final class SitemapController extends Controller
{
    public function __construct(
        private readonly SitemapService $sitemapService
    ) {}

    /**
     * Display the root sitemap index.
     */
    public function index(): Response
    {
        $sitemaps = $this->sitemapService->getIndex();

        return response()
            ->view('sitemap.index', ['sitemaps' => $sitemaps])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * Display a locale-specific sitemap with canonical and alternate URLs.
     */
    public function locale(string $locale): Response
    {
        // Normalize the provided locale to a safe slug that matches the
        // supported configuration while preventing path traversal attempts.
        $normalizedLocale = (string) Str::of($locale)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9\-_]+/', '');

        if ($normalizedLocale === '') {
            abort(404);
        }

        try {
            // Delegate to the sitemap service using the sanitized locale so we
            // avoid cache fragmentation from mixed-case or malformed inputs.
            $urls = $this->sitemapService->getLocaleUrls($normalizedLocale);
        } catch (InvalidArgumentException $exception) {
            // Hide the underlying error message to keep 404 responses concise
            // while signalling the requested locale is not available.
            abort(404);
        }

        return response()
            ->view('sitemap.locale', ['urls' => $urls, 'locale' => $normalizedLocale])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
