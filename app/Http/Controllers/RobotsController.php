<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: request()->getHost();
        $locales = collect(explode(',', (string) config('app.supported_locales', 'en')))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values();

        $lines = [
            'User-agent: *',
            'Disallow: /cpanel/',
            'Disallow: /admin/',
            'Disallow: /horizon',
            'Disallow: /telescope',
        ];

        foreach ($locales as $locale) {
            $lines[] = 'Sitemap: https://'.$host.'/'.$locale.'/sitemap.xml';
        }

        $content = implode("\n", $lines)."\n";

        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}
