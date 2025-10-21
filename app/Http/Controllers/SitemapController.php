<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;
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
        try {
            $urls = $this->sitemapService->getLocaleUrls($locale);
        } catch (InvalidArgumentException $exception) {
            abort(404, $exception->getMessage());
        }

        return response()
            ->view('sitemap.locale', ['urls' => $urls, 'locale' => $locale])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
