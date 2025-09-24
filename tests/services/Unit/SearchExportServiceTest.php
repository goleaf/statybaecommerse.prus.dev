<?php

declare(strict_types=1);

use App\Services\SearchExportService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Cache::flush();
    // Simple route() stub for tests if routes are not defined
    if (! function_exists('route')) {
        function route($name, $params = [])
        {
            return '/test/'.$name.'/'.implode('-', array_values($params));
        }
    }
});

function sampleResults(): array
{
    return [
        ['id' => 1, 'type' => 'product', 'title' => 'Hammer'],
        ['id' => 2, 'type' => 'category', 'title' => 'Tools'],
    ];
}

it('exports results to JSON and stores metadata', function () {
    $svc = app(SearchExportService::class);

    $res = $svc->exportSearchResults(sampleResults(), 'hammer tools', 'json');

    expect($res['success'])->toBeTrue()
        ->and($res)->toHaveKeys(['export_id', 'download_url', 'expires_at'])
        ->and($svc->getExportData($res['export_id']))->not->toBeNull();
});

it('exports results to CSV and XML formats', function () {
    $svc = app(SearchExportService::class);

    $csv = (new ReflectionClass(SearchExportService::class))
        ->getMethod('formatExportData')->invokeArgs($svc, [sampleResults(), 'csv', []]);
    $xml = (new ReflectionClass(SearchExportService::class))
        ->getMethod('formatExportData')->invokeArgs($svc, [sampleResults(), 'xml', []]);

    expect($csv)->toContain('id,type,title')
        ->and($xml)->toContain('<?xml');
});

it('generates shareable link and persists share data', function () {
    $svc = app(SearchExportService::class);

    $share = $svc->generateShareableLink(sampleResults(), 'hammer');
    expect($share['success'])->toBeTrue()->and($share)->toHaveKeys(['share_id', 'share_url', 'preview']);

    $loaded = $svc->getSharedSearch($share['share_id']);
    expect($loaded)->not->toBeNull();
});
