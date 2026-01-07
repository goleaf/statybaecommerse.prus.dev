<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\FileProcessor;
use App\Services\VersionCompatibility\Security\FileSecurityValidator;
use App\Services\VersionCompatibility\Security\RateLimiter;
use App\Services\VersionCompatibilityService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;

describe('VersionCompatibilityService Performance', function () {
    beforeEach(function () {
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->cache = Mockery::mock(CacheRepository::class);
        $this->config = Mockery::mock(ConfigRepository::class);
        $this->fileProcessor = Mockery::mock(FileProcessor::class);
        $this->securityValidator = Mockery::mock(FileSecurityValidator::class);
        $this->rateLimiter = Mockery::mock(RateLimiter::class);

        // Mock configuration
        $this->config->shouldReceive('get')
            ->with('version-compatibility', [])
            ->andReturn([
                'cache'       => ['prefix' => 'test', 'ttl' => 3600],
                'logging'     => ['slow_threshold_ms' => 100],
                'security'    => ['audit_logging' => ['enabled' => false]],
                'performance' => ['batch_size' => 50],
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

    it('processes single file transformation within performance threshold', function () {
        $content = '<?php class TestResource extends Resource {}';

        $this->rateLimiter->shouldReceive('checkRateLimit')->once();
        $this->rateLimiter->shouldReceive('recordAttempt')->once();
        $this->securityValidator->shouldReceive('validateContent')->once();

        $this->cache->shouldReceive('remember')
            ->once()
            ->andReturn(new \App\Services\VersionCompatibility\TransformationResult($content, false, []));

        $startTime = microtime(true);
        $result = $this->service->transformContent($content);
        $duration = (microtime(true) - $startTime) * 1000;

        expect($duration)->toBeLessThan(50); // Should complete within 50ms
        expect($result)->toBeInstanceOf(\App\Services\VersionCompatibility\TransformationResult::class);
    });

    it('handles batch file processing efficiently', function () {
        $files = collect(range(1, 100))->map(fn ($i) => "test_file_{$i}.php");

        $this->rateLimiter->shouldReceive('checkRateLimit')->once();
        $this->rateLimiter->shouldReceive('recordAttempt')->once();
        $this->securityValidator->shouldReceive('validateDirectoryPath')->once();

        $this->filesystem->shouldReceive('allFiles')
            ->once()
            ->andReturn($files->toArray());

        // Mock file processing for each file
        $files->each(function ($file) {
            $this->securityValidator->shouldReceive('validateFilePath')
                ->with($file)
                ->once();

            $this->fileProcessor->shouldReceive('processFile')
                ->with($file, Mockery::any())
                ->once()
                ->andReturn(new \App\Services\VersionCompatibility\TransformationResult('', false, []));
        });

        $startTime = microtime(true);
        $results = $this->service->fixAllResourcesInDirectory('/test/directory');
        $duration = (microtime(true) - $startTime) * 1000;

        expect($duration)->toBeLessThan(1000); // Should complete within 1 second for 100 files
        expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    });

    it('uses cache effectively to avoid redundant processing', function () {
        $content = '<?php class TestResource extends Resource {}';
        $cacheKey = 'test:' . hash('xxh3', $content);

        $this->rateLimiter->shouldReceive('checkRateLimit')->twice();
        $this->rateLimiter->shouldReceive('recordAttempt')->twice();
        $this->securityValidator->shouldReceive('validateContent')->twice();

        // First call - cache miss
        $this->cache->shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $ttl, $callback) {
                return $callback();
            });

        // Second call - cache hit (should be faster)
        $this->cache->shouldReceive('remember')
            ->once()
            ->andReturn(new \App\Services\VersionCompatibility\TransformationResult($content, false, []));

        // First transformation (cache miss)
        $startTime1 = microtime(true);
        $this->service->transformContent($content);
        $duration1 = (microtime(true) - $startTime1) * 1000;

        // Second transformation (cache hit)
        $startTime2 = microtime(true);
        $this->service->transformContent($content);
        $duration2 = (microtime(true) - $startTime2) * 1000;

        expect($duration2)->toBeLessThan($duration1 * 0.5); // Cache hit should be at least 50% faster
    });

    it('monitors memory usage during large batch operations', function () {
        $this->rateLimiter->shouldReceive('checkRateLimit')->once();
        $this->rateLimiter->shouldReceive('recordAttempt')->once();
        $this->securityValidator->shouldReceive('validateDirectoryPath')->once();

        $largeFileSet = collect(range(1, 1000))->map(fn ($i) => "large_file_{$i}.php");

        $this->filesystem->shouldReceive('allFiles')
            ->once()
            ->andReturn($largeFileSet->toArray());

        $largeFileSet->each(function ($file) {
            $this->securityValidator->shouldReceive('validateFilePath')
                ->with($file)
                ->once();

            $this->fileProcessor->shouldReceive('processFile')
                ->with($file, Mockery::any())
                ->once()
                ->andReturn(new \App\Services\VersionCompatibility\TransformationResult('', false, []));
        });

        $memoryBefore = memory_get_usage(true);
        $this->service->fixAllResourcesInDirectory('/test/large-directory');
        $memoryAfter = memory_get_usage(true);

        $memoryIncrease = ($memoryAfter - $memoryBefore) / 1024 / 1024; // MB

        expect($memoryIncrease)->toBeLessThan(50); // Should not use more than 50MB additional memory
    });
});
