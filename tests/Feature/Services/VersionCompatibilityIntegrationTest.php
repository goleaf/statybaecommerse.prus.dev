<?php

declare(strict_types=1);

use App\Services\VersionCompatibilityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

describe('VersionCompatibilityService Integration', function () {
    beforeEach(function () {
        // Clear cache before each test
        Cache::flush();

        // Set test configuration
        Config::set('version-compatibility.cache.prefix', 'test_transform');
        Config::set('version-compatibility.cache.ttl', 60);
    });

    it('integrates with real cache and filesystem', function () {
        $service = app(VersionCompatibilityService::class);

        $content = 'use Filament\Schemas\Schema;';

        // First transformation should hit the strategies
        $result1 = $service->transformContent($content);

        // Second transformation should hit the cache
        $result2 = $service->transformContent($content);

        expect($result1->wasTransformed())->toBeTrue();
        expect($result2->wasTransformed())->toBeTrue();
        expect($result1->getContent())->toBe($result2->getContent());
    });

    it('provides accurate transformation statistics', function () {
        $service = app(VersionCompatibilityService::class);

        $stats = $service->getTransformationStats();

        expect($stats)->toHaveKeys([
            'available_strategies',
            'cache_prefix',
            'cache_ttl',
            'strategies',
        ]);

        expect($stats['available_strategies'])->toBe(5);
        expect($stats['cache_prefix'])->toBe('test_transform');
        expect($stats['strategies'])->toHaveCount(5);
    });

    it('handles real file transformation', function () {
        $service = app(VersionCompatibilityService::class);

        // Create a temporary test file
        $testContent = '<?php
use Filament\Schemas\Schema;

class TestResource
{
    public static function form(Form $form): Form
    {
        return $schema->schema([
            // form fields
        ]);
    }
}';

        $tempFile = storage_path('app/test_resource.php');
        File::put($tempFile, $testContent);

        try {
            $result = $service->fixResourceFile($tempFile);

            expect($result->wasTransformed())->toBeTrue();
            expect($result->getContent())->toContain('use Filament\Forms\Form;');
            expect($result->getContent())->toContain('return $form->schema([');
        } finally {
            // Clean up
            if (File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    });

    it('clears cache successfully', function () {
        $service = app(VersionCompatibilityService::class);

        // Add something to cache first
        $content = 'use Filament\Schemas\Schema;';
        $service->transformContent($content);

        $result = $service->clearCache();

        expect($result)->toBeTrue();
    });
});
