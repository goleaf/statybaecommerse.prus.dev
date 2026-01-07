<?php

declare(strict_types=1);

use App\Services\VersionCompatibilityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

describe('VersionCompatibilityService Performance Tests', function () {
    beforeEach(function () {
        Cache::flush();

        // Configure for performance testing
        Config::set('version-compatibility.performance.batch_size', 25);
        Config::set('version-compatibility.logging.log_all_transformations', false);
    });

    it('processes large content efficiently', function () {
        $service = app(VersionCompatibilityService::class);

        // Generate large but realistic PHP content
        $content = "<?php\n\nnamespace App\\Filament\\Resources;\n\n";
        $content .= str_repeat("use Filament\\Forms\\Components\\TextInput;\n", 100);
        $content .= "\nclass TestResource extends Resource\n{\n";
        $content .= str_repeat("    // Comment line\n", 500);
        $content .= "}\n";

        $startTime = microtime(true);
        $result = $service->transformContent($content);
        $duration = (microtime(true) - $startTime) * 1000;

        expect($duration)->toBeLessThan(500); // Should complete within 500ms
        expect(strlen($content))->toBeGreaterThan(10000); // Ensure we're testing large content
    });

    it('benefits from caching on repeated calls', function () {
        $service = app(VersionCompatibilityService::class);
        $content = 'use Filament\Schemas\Schema;';

        // First call (cache miss)
        $startTime = microtime(true);
        $result1 = $service->transformContent($content);
        $firstCallDuration = (microtime(true) - $startTime) * 1000;

        // Second call (cache hit)
        $startTime = microtime(true);
        $result2 = $service->transformContent($content);
        $secondCallDuration = (microtime(true) - $startTime) * 1000;

        expect($secondCallDuration)->toBeLessThan($firstCallDuration * 0.5); // At least 50% faster
        expect($result1->getContent())->toBe($result2->getContent());
    });

    it('handles batch processing efficiently', function () {
        $service = app(VersionCompatibilityService::class);

        // Create temporary directory with test files
        $testDir = storage_path('app/test-batch');
        if (! is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }

        // Create multiple test files
        for ($i = 0; $i < 20; $i++) {
            file_put_contents(
                "{$testDir}/TestResource{$i}.php",
                "<?php\nuse Filament\\Schemas\\Schema;\nclass TestResource{$i} {}\n"
            );
        }

        $startTime = microtime(true);
        $results = $service->fixAllResourcesInDirectory($testDir);
        $duration = (microtime(true) - $startTime) * 1000;

        expect($duration)->toBeLessThan(2000); // Should complete within 2 seconds
        expect($results->count())->toBeGreaterThan(0);

        // Cleanup
        array_map('unlink', glob("{$testDir}/*.php"));
        rmdir($testDir);
    });

    it('uses memory efficiently for large files', function () {
        $service = app(VersionCompatibilityService::class);

        $memoryBefore = memory_get_usage(true);

        // Process multiple large content blocks
        for ($i = 0; $i < 10; $i++) {
            $content = str_repeat("<?php\n// Large content block {$i}\n", 1000);
            $service->transformContent($content);
        }

        $memoryAfter = memory_get_usage(true);
        $memoryIncrease = $memoryAfter - $memoryBefore;

        // Memory increase should be reasonable (less than 10MB)
        expect($memoryIncrease)->toBeLessThan(10 * 1024 * 1024);
    });
});
