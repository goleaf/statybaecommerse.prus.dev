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
        $rows = array_map(static fn (array $entry): array => [
            'loc'     => (string) ($entry['loc'] ?? ''),
            'lastmod' => (string) ($entry['lastmod'] ?? ''),
        ], $sitemaps);

        return response($this->toCsv($rows), 200, ['Content-Type' => 'text/csv; charset=utf-8']);
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

        $rows = array_map(static fn (array $entry): array => [
            'loc'        => (string) ($entry['loc'] ?? ''),
            'lastmod'    => (string) ($entry['lastmod'] ?? ''),
            'changefreq' => (string) ($entry['changefreq'] ?? ''),
            'priority'   => (string) ($entry['priority'] ?? ''),
        ], $urls);

        return response($this->toCsv($rows), 200, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    /**
     * @param  array<int, array<string, string>> $rows
     */
    private function toCsv(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        fputcsv($handle, array_keys($rows[0]));

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return is_string($csv) ? $csv : '';
    }
}
