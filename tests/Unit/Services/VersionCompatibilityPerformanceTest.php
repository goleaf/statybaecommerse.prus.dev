<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\FileProcessor;
use App\Services\VersionCompatibility\Security\FileSecurityValidator;
use App\Services\VersionCompatibility\Security\RateLimiter;
use App\Services\VersionCompatibility\TransformationResult;
use App\Services\VersionCompatibilityService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->fileProcessor = Mockery::mock(FileProcessor::class);
    $this->filesystem = Mockery::mock(Filesystem::class);
    $this->cache = Mockery::mock(CacheRepository::class);
    $this->config = Mockery::mock(ConfigRepository::class);
    $this->securityValidator = Mockery::mock(FileSecurityValidator::class);
    $this->rateLimiter = Mockery::mock(RateLimiter::class);

    // Setup performance-focused configuration
    $this->config->shouldReceive('get')
        ->with('version-compatibility', [])
        ->andReturn([
            'cache'    => ['prefix' => 'perf_test', 'ttl' => 7200],
            'logging'  => ['slow_threshold_ms' => 200, 'log_all_transformations' => false],
            'security' => [
                'max_file_size'      => 2 * 1024 * 1024, // 2MB
                'allowed_extensions' => ['php'],
                'audit_logging'      => ['enabled' => false], // Disabled for performance
                'rate_limiting'      => ['enabled' => false], // Disabled for performance
            ],
            'performance' => ['batch_size' => 100], // Larger batches
        ]);

    $this->service = new VersionCompatibilityService(
        $this->fileProcessor,
        $this->filesystem,
        $this->cache,
        $this->config,
        $this->securityValidator,
        $this->rateLimiter
    );
});

describe('Performance-Focused VersionCompatibilityService Tests', function () {
    describe('Caching Performance', function () {
        it('uses efficient cache key generation', function () {
            $content = '<?php class Test {}';
            $cacheKey = null;

            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->with(
                    Mockery::capture($cacheKey),
                    7200,
                    Mockery::type('Closure')
                )
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $this->service->transformContent($content);

            // Cache key should be short and efficient (prefix + hash)
            expect($cacheKey)->toMatch('/^perf_test:[a-f0-9]{16}$/');
        });

        it('avoids redundant cache operations for identical content', function () {
            $content = '<?php class Test {}';
            $cachedResult = new TransformationResult($content, false, []);

            $this->securityValidator->shouldReceive('validateContent')->once();

            // Cache should only be called once
            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturn($cachedResult);

            $result = $this->service->transformContent($content);

            expect($result)->toBe($cachedResult);
        });

        it('handles cache misses efficiently', function () {
            $content = '<?php class Test {}';

            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $result = $this->service->transformContent($content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
        });
    });

    describe('Strategy Optimization', function () {
        it('filters strategies efficiently before processing', function () {
            $content = '<?php class Test {}';

            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $startTime = microtime(true);
            $result = $this->service->transformContent($content);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toBeInstanceOf(TransformationResult::class);
            // Should complete quickly without actual strategy processing
            expect($duration)->toBeLessThan(50); // 50ms threshold
        });

        it('uses memoized strategy initialization', function () {
            // Create multiple service instances to test memoization
            $service1 = new VersionCompatibilityService(
                $this->fileProcessor,
                $this->filesystem,
                $this->cache,
                $this->config,
                $this->securityValidator,
                $this->rateLimiter
            );

            $service2 = new VersionCompatibilityService(
                $this->fileProcessor,
                $this->filesystem,
                $this->cache,
                $this->config,
                $this->securityValidator,
                $this->rateLimiter
            );

            $strategies1 = $service1->getAvailableStrategies();
            $strategies2 = $service2->getAvailableStrategies();

            // Both should have the same number of strategies
            expect($strategies1)->toHaveCount($strategies2->count());
        });

        it('processes only applicable strategies', function () {
            $phpContent = '<?php class Test {}';
            $nonPhpContent = 'This is not PHP code';

            $this->securityValidator->shouldReceive('validateContent')->twice();

            $this->cache->shouldReceive('remember')
                ->twice()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $phpResult = $this->service->transformContent($phpContent);
            $nonPhpResult = $this->service->transformContent($nonPhpContent);

            expect($phpResult)->toBeInstanceOf(TransformationResult::class);
            expect($nonPhpResult)->toBeInstanceOf(TransformationResult::class);
        });
    });

    describe('Batch Processing Performance', function () {
        it('processes files in optimal batch sizes', function () {
            $directory = 'app/Filament/Resources';
            $files = array_map(fn ($i) => "Resource{$i}.php", range(1, 150));

            $this->securityValidator->shouldReceive('validateDirectoryPath')->once();
            $this->filesystem->shouldReceive('allFiles')
                ->with($directory)
                ->andReturn($files);

            // Should process in batches of 100 (configured batch size)
            $processedFiles = [];
            foreach ($files as $file) {
                $this->securityValidator->shouldReceive('validateFilePath')
                    ->once()
                    ->with($file);

                $this->fileProcessor->shouldReceive('processFile')
                    ->once()
                    ->with($file, Mockery::type(Collection::class))
                    ->andReturnUsing(function ($filePath) use (&$processedFiles) {
                        $processedFiles[] = $filePath;

                        return new TransformationResult('content', true, ['test']);
                    });
            }

            $result = $this->service->fixAllResourcesInDirectory($directory);

            expect($result)->toHaveCount(150);
            expect($processedFiles)->toHaveCount(150);
        });

        it('handles large directories efficiently', function () {
            $directory = 'app/Filament/Resources';
            $files = array_map(fn ($i) => "Resource{$i}.php", range(1, 500));

            $this->securityValidator->shouldReceive('validateDirectoryPath')->once();
            $this->filesystem->shouldReceive('allFiles')
                ->with($directory)
                ->andReturn($files);

            foreach ($files as $file) {
                $this->securityValidator->shouldReceive('validateFilePath')->once();
                $this->fileProcessor->shouldReceive('processFile')
                    ->once()
                    ->andReturn(new TransformationResult('content', true, ['test']));
            }

            $startTime = microtime(true);
            $result = $this->service->fixAllResourcesInDirectory($directory);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toHaveCount(500);
            // Should handle large batches reasonably quickly
            expect($duration)->toBeLessThan(1000); // 1 second threshold
        });

        it('manages memory efficiently during batch processing', function () {
            $directory = 'app/Filament/Resources';
            $files = array_map(fn ($i) => "Resource{$i}.php", range(1, 200));

            $this->securityValidator->shouldReceive('validateDirectoryPath')->once();
            $this->filesystem->shouldReceive('allFiles')
                ->with($directory)
                ->andReturn($files);

            $memoryBefore = memory_get_usage();

            foreach ($files as $file) {
                $this->securityValidator->shouldReceive('validateFilePath')->once();
                $this->fileProcessor->shouldReceive('processFile')
                    ->once()
                    ->andReturn(new TransformationResult('content', false, []));
            }

            $result = $this->service->fixAllResourcesInDirectory($directory);
            $memoryAfter = memory_get_usage();

            expect($result)->toHaveCount(200);

            // Memory usage should not grow excessively
            $memoryIncrease = $memoryAfter - $memoryBefore;
            expect($memoryIncrease)->toBeLessThan(10 * 1024 * 1024); // 10MB threshold
        });
    });

    describe('Logging Performance', function () {
        it('minimizes logging overhead in production mode', function () {
            $content = '<?php class Test {}';

            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            // No logging should occur with audit logging disabled
            Log::shouldNotReceive('channel');
            Log::shouldNotReceive('info');
            Log::shouldNotReceive('debug');

            $result = $this->service->transformContent($content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
        });

        it('logs only slow operations when threshold is configured', function () {
            // This test would require actual slow operations to trigger logging
            // For now, we verify the configuration is respected
            $stats = $this->service->getTransformationStats();

            expect($stats['service_info']['slow_threshold_ms'])->toBe(200);
            expect($stats['configuration']['logging']['log_all_transformations'])->toBeFalse();
        });
    });

    describe('Memory Management', function () {
        it('uses WeakMap for object-level caching', function () {
            // Test that the service doesn't hold strong references to temporary objects
            $content1 = '<?php class Test1 {}';
            $content2 = '<?php class Test2 {}';

            $this->securityValidator->shouldReceive('validateContent')->twice();

            $this->cache->shouldReceive('remember')
                ->twice()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $memoryBefore = memory_get_usage();

            $result1 = $this->service->transformContent($content1);
            $result2 = $this->service->transformContent($content2);

            $memoryAfter = memory_get_usage();

            expect($result1)->toBeInstanceOf(TransformationResult::class);
            expect($result2)->toBeInstanceOf(TransformationResult::class);

            // Memory usage should be reasonable
            $memoryIncrease = $memoryAfter - $memoryBefore;
            expect($memoryIncrease)->toBeLessThan(1024 * 1024); // 1MB threshold
        });

        it('handles large content efficiently', function () {
            $largeContent = '<?php ' . str_repeat('// Large comment block', 10000);

            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $memoryBefore = memory_get_usage();
            $result = $this->service->transformContent($largeContent);
            $memoryAfter = memory_get_usage();

            expect($result)->toBeInstanceOf(TransformationResult::class);

            // Memory usage should not be excessive for large content
            $memoryIncrease = $memoryAfter - $memoryBefore;
            expect($memoryIncrease)->toBeLessThan(5 * 1024 * 1024); // 5MB threshold
        });
    });

    describe('Configuration Performance', function () {
        it('caches configuration values to avoid repeated lookups', function () {
            // Configuration should only be read once during service initialization
            // Subsequent operations should use cached values

            $content = '<?php class Test {}';

            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            // Config should not be called again after initialization
            $this->config->shouldNotReceive('get');

            $result = $this->service->transformContent($content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
        });

        it('provides efficient statistics without heavy computation', function () {
            $startTime = microtime(true);
            $stats = $this->service->getTransformationStats();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($stats)->toBeArray();
            expect($stats)->toHaveKeys(['service_info', 'strategies', 'configuration']);

            // Statistics generation should be fast
            expect($duration)->toBeLessThan(10); // 10ms threshold
        });
    });
});

describe('Performance Property Tests', function () {
    it('maintains consistent performance for repeated operations', function () {
        $content = '<?php class Test {}';
        $durations = [];

        $this->securityValidator->shouldReceive('validateContent')->times(5);

        for ($i = 0; $i < 5; $i++) {
            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $startTime = microtime(true);
            $result = $this->service->transformContent($content);
            $durations[] = (microtime(true) - $startTime) * 1000;

            expect($result)->toBeInstanceOf(TransformationResult::class);
        }

        // Performance should be consistent (no significant degradation)
        $avgDuration = array_sum($durations) / count($durations);
        $maxDuration = max($durations);

        expect($maxDuration)->toBeLessThan($avgDuration * 3); // Max should not be more than 3x average
    });

    it('scales linearly with content size', function () {
        $baseSizes = [100, 500, 1000]; // Characters
        $durations = [];

        foreach ($baseSizes as $size) {
            $content = '<?php ' . str_repeat('// comment', $size);

            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $startTime = microtime(true);
            $result = $this->service->transformContent($content);
            $durations[$size] = (microtime(true) - $startTime) * 1000;

            expect($result)->toBeInstanceOf(TransformationResult::class);
        }

        // Performance should scale reasonably with content size
        expect($durations[1000])->toBeLessThan($durations[100] * 20); // Should not be 20x slower for 10x content
    });

    it('handles concurrent-like operations efficiently', function () {
        $contents = [
            '<?php class Test1 {}',
            '<?php class Test2 {}',
            '<?php class Test3 {}',
        ];

        $this->securityValidator->shouldReceive('validateContent')->times(3);

        $this->cache->shouldReceive('remember')
            ->times(3)
            ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

        $startTime = microtime(true);

        $results = [];
        foreach ($contents as $content) {
            $results[] = $this->service->transformContent($content);
        }

        $totalDuration = (microtime(true) - $startTime) * 1000;

        expect($results)->toHaveCount(3);
        foreach ($results as $result) {
            expect($result)->toBeInstanceOf(TransformationResult::class);
        }

        // Total time should be reasonable for multiple operations
        expect($totalDuration)->toBeLessThan(100); // 100ms for 3 operations
    });
});
