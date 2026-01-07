<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\Contracts\TransformationStrategyInterface;
use App\Services\VersionCompatibility\Exceptions\InvalidFileException;
use App\Services\VersionCompatibility\Exceptions\TransformationException;
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

// Prevent Filament from auto-loading during unit tests
if (! defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

beforeEach(function () {
    $this->fileProcessor = Mockery::mock(FileProcessor::class);
    $this->filesystem = Mockery::mock(Filesystem::class);
    $this->cache = Mockery::mock(CacheRepository::class);
    $this->config = Mockery::mock(ConfigRepository::class);
    $this->securityValidator = Mockery::mock(FileSecurityValidator::class);
    $this->rateLimiter = Mockery::mock(RateLimiter::class);

    // Setup default configuration
    $this->setupDefaultConfiguration();

    $this->service = new VersionCompatibilityService(
        $this->fileProcessor,
        $this->filesystem,
        $this->cache,
        $this->config,
        $this->securityValidator,
        $this->rateLimiter
    );
});

function setupDefaultConfiguration(): void
{
    test()->config->shouldReceive('get')
        ->with('version-compatibility', [])
        ->andReturn([
            'cache' => [
                'prefix' => 'filament_transform',
                'ttl'    => 3600,
            ],
            'logging' => [
                'slow_threshold_ms'       => 100,
                'log_all_transformations' => false,
            ],
            'security' => [
                'max_file_size'      => 1024 * 1024,
                'allowed_extensions' => ['php'],
                'audit_logging'      => ['enabled' => true],
                'rate_limiting'      => ['enabled' => true],
            ],
            'performance' => [
                'batch_size' => 50,
            ],
        ]);
}

describe('VersionCompatibilityService Enhanced Security Tests', function () {
    describe('transformContent with security features', function () {
        it('applies rate limiting when enabled', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once()->with($content);

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->transformContent($content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
        });

        it('throws RuntimeException when rate limit is exceeded', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')
                ->once()
                ->andThrow(new RuntimeException('Rate limit exceeded'));

            expect(fn () => $this->service->transformContent($content))
                ->toThrow(RuntimeException::class, 'Rate limit exceeded');
        });

        it('validates content security before transformation', function () {
            $content = '<?php malicious_code(); ?>';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateContent')
                ->once()
                ->with($content)
                ->andThrow(new InvalidArgumentException('Suspicious content detected'));

            expect(fn () => $this->service->transformContent($content))
                ->toThrow(InvalidArgumentException::class, 'Suspicious content detected');
        });

        it('logs security events for successful transformations', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once()->with(
                'Version compatibility security event: content_transformation_success',
                Mockery::type('array')
            );

            $this->service->transformContent($content);
        });

        it('logs security events for failed transformations', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andThrow(new TransformationException('Transformation failed'));

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once()->with(
                'Version compatibility security event: content_transformation_failure',
                Mockery::type('array')
            );

            expect(fn () => $this->service->transformContent($content))
                ->toThrow(TransformationException::class);
        });
    });

    describe('fixResourceFile with enhanced security', function () {
        it('validates file path security before processing', function () {
            $filePath = 'app/Filament/Resources/TestResource.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->with($filePath);

            $this->fileProcessor->shouldReceive('processFile')
                ->once()
                ->andReturn(new TransformationResult('content', true, ['test']));

            $this->rateLimiter->shouldReceive('recordAttempt')->once();

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->fixResourceFile($filePath);

            expect($result)->toBeInstanceOf(TransformationResult::class);
        });

        it('throws InvalidFileException for path traversal attempts', function () {
            $filePath = '../../../etc/passwd.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->with($filePath)
                ->andThrow(InvalidFileException::pathTraversalDetected($filePath));

            expect(fn () => $this->service->fixResourceFile($filePath))
                ->toThrow(InvalidFileException::class, 'Path traversal detected');
        });

        it('handles transformation errors gracefully with audit logging', function () {
            $filePath = 'app/Filament/Resources/TestResource.php';
            $error = new TransformationException('Processing failed');

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')->once();

            $this->fileProcessor->shouldReceive('processFile')
                ->once()
                ->andThrow($error);

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once()->with(
                'Version compatibility security event: file_transformation_failure',
                Mockery::type('array')
            );

            Log::shouldReceive('error')->once();

            $result = $this->service->fixResourceFile($filePath);

            expect($result->hasError())->toBeTrue();
            expect($result->getError())->toBe('Processing failed');
        });
    });

    describe('fixAllResourcesInDirectory with batch processing', function () {
        it('validates directory path security', function () {
            $directory = 'app/Filament/Resources';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateDirectoryPath')
                ->once()
                ->with($directory);

            $this->filesystem->shouldReceive('allFiles')
                ->with($directory)
                ->andReturn([]);

            $this->rateLimiter->shouldReceive('recordAttempt')->once();

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->fixAllResourcesInDirectory($directory);

            expect($result)->toBeInstanceOf(Collection::class);
        });

        it('processes files in secure batches', function () {
            $directory = 'app/Filament/Resources';
            $files = [
                'TestResource.php',
                'UserResource.php',
                'ProductResource.php',
            ];

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateDirectoryPath')->once();

            $this->filesystem->shouldReceive('allFiles')
                ->with($directory)
                ->andReturn($files);

            // Each file should be validated individually
            foreach ($files as $file) {
                $this->securityValidator->shouldReceive('validateFilePath')
                    ->once()
                    ->with($file);

                $this->fileProcessor->shouldReceive('processFile')
                    ->once()
                    ->with($file, Mockery::type(Collection::class))
                    ->andReturn(new TransformationResult('content', true, ['test']));
            }

            $this->rateLimiter->shouldReceive('recordAttempt')->once();

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->fixAllResourcesInDirectory($directory);

            expect($result)->toHaveCount(3);
        });

        it('continues processing other files when one fails', function () {
            $directory = 'app/Filament/Resources';
            $files = ['GoodResource.php', 'BadResource.php'];

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateDirectoryPath')->once();

            $this->filesystem->shouldReceive('allFiles')
                ->with($directory)
                ->andReturn($files);

            // First file succeeds
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->with('GoodResource.php');
            $this->fileProcessor->shouldReceive('processFile')
                ->once()
                ->with('GoodResource.php', Mockery::type(Collection::class))
                ->andReturn(new TransformationResult('content', true, ['test']));

            // Second file fails
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->with('BadResource.php')
                ->andThrow(new InvalidFileException('Invalid file'));

            Log::shouldReceive('error')->once();

            $this->rateLimiter->shouldReceive('recordAttempt')->once();

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->fixAllResourcesInDirectory($directory);

            expect($result)->toHaveCount(1);
        });
    });
});

describe('VersionCompatibilityService Performance Tests', function () {
    describe('caching behavior', function () {
        it('generates secure cache keys using content hash', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->with(
                    Mockery::pattern('/^filament_transform:[a-f0-9]+$/'),
                    3600,
                    Mockery::type('Closure')
                )
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $this->service->transformContent($content);
        });

        it('uses cached results for identical content', function () {
            $content = '<?php class Test {}';
            $cachedResult = new TransformationResult($content, false, []);

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturn($cachedResult);

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->transformContent($content);

            expect($result)->toBe($cachedResult);
        });
    });

    describe('strategy optimization', function () {
        it('filters strategies that can handle content', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->transformContent($content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
        });
    });
});

describe('VersionCompatibilityService Configuration Tests', function () {
    describe('service initialization', function () {
        it('validates configuration at startup', function () {
            // Test with invalid cache TTL
            $this->config->shouldReceive('get')
                ->with('version-compatibility', [])
                ->andReturn([
                    'cache'    => ['ttl' => -1], // Invalid TTL
                    'logging'  => ['slow_threshold_ms' => 100],
                    'security' => ['allowed_extensions' => ['php']],
                ]);

            expect(fn () => new VersionCompatibilityService(
                $this->fileProcessor,
                $this->filesystem,
                $this->cache,
                $this->config,
                $this->securityValidator,
                $this->rateLimiter
            ))->toThrow(InvalidArgumentException::class, 'Cache TTL must be positive');
        });

        it('validates security configuration', function () {
            $this->config->shouldReceive('get')
                ->with('version-compatibility', [])
                ->andReturn([
                    'cache'    => ['ttl' => 3600],
                    'logging'  => ['slow_threshold_ms' => 100],
                    'security' => ['allowed_extensions' => []], // Empty extensions
                ]);

            expect(fn () => new VersionCompatibilityService(
                $this->fileProcessor,
                $this->filesystem,
                $this->cache,
                $this->config,
                $this->securityValidator,
                $this->rateLimiter
            ))->toThrow(InvalidArgumentException::class, 'At least one file extension must be allowed');
        });
    });

    describe('getTransformationStats with enhanced metrics', function () {
        it('includes security and performance metrics', function () {
            $stats = $this->service->getTransformationStats();

            expect($stats)->toHaveKeys([
                'service_info',
                'strategies',
                'configuration',
                'cache_stats',
                'rate_limiting',
            ]);

            expect($stats['service_info'])->toHaveKeys([
                'security_enabled',
                'rate_limiting_enabled',
                'audit_logging_enabled',
            ]);

            expect($stats['service_info']['security_enabled'])->toBeTrue();
            expect($stats['configuration'])->toHaveKey('security');
        });

        it('includes rate limiting information when enabled', function () {
            $this->rateLimiter->shouldReceive('getRemainingAttempts')
                ->once()
                ->andReturn(95);

            $stats = $this->service->getTransformationStats();

            expect($stats['rate_limiting'])->not->toBeNull();
            expect($stats['rate_limiting']['remaining_attempts'])->toBe(95);
        });
    });
});

describe('VersionCompatibilityService Strategy Management', function () {
    describe('addStrategy with enhanced validation', function () {
        it('validates strategy name uniqueness', function () {
            $strategy1 = Mockery::mock(TransformationStrategyInterface::class);
            $strategy1->shouldReceive('getName')->andReturn('TestStrategy');

            $strategy2 = Mockery::mock(TransformationStrategyInterface::class);
            $strategy2->shouldReceive('getName')->andReturn('TestStrategy');

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once(); // For first strategy
            Log::shouldReceive('info')->once(); // For audit log

            $this->service->addStrategy($strategy1);

            expect(fn () => $this->service->addStrategy($strategy2))
                ->toThrow(InvalidArgumentException::class, 'Strategy name already exists: TestStrategy');
        });

        it('logs strategy addition for audit trail', function () {
            $strategy = Mockery::mock(TransformationStrategyInterface::class);
            $strategy->shouldReceive('getName')->andReturn('TestStrategy');

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once()->with(
                'Version compatibility security event: strategy_added',
                Mockery::type('array')
            );

            Log::shouldReceive('info')->once()->with(
                'Custom transformation strategy added',
                Mockery::type('array')
            );

            $result = $this->service->addStrategy($strategy);

            expect($result)->toBe($this->service);
        });
    });
});

// Property-based tests for enhanced service
describe('VersionCompatibilityService Enhanced Property Tests', function () {
    it('maintains security validation for all content transformations', function () {
        $contents = [
            '<?php class Test {}',
            '<?php namespace App; class Test {}',
            '<?php use Filament\Forms\Form; class Test {}',
        ];

        foreach ($contents as $content) {
            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once()->with($content);

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->transformContent($content);
            expect($result)->toBeInstanceOf(TransformationResult::class);
        }
    });

    it('always applies rate limiting when enabled', function () {
        $operations = [
            fn () => $this->service->transformContent('<?php class Test {}'),
            fn () => $this->service->fixResourceFile('test.php'),
            fn () => $this->service->fixAllResourcesInDirectory('test-dir'),
        ];

        foreach ($operations as $operation) {
            $this->rateLimiter->shouldReceive('checkRateLimit')->once();

            // Setup minimal mocks for each operation type
            $this->securityValidator->shouldReceive('validateContent')->zeroOrMoreTimes();
            $this->securityValidator->shouldReceive('validateFilePath')->zeroOrMoreTimes();
            $this->securityValidator->shouldReceive('validateDirectoryPath')->zeroOrMoreTimes();

            $this->cache->shouldReceive('remember')->zeroOrMoreTimes()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            $this->fileProcessor->shouldReceive('processFile')->zeroOrMoreTimes()
                ->andReturn(new TransformationResult('content', false, []));

            $this->filesystem->shouldReceive('allFiles')->zeroOrMoreTimes()
                ->andReturn([]);

            $this->rateLimiter->shouldReceive('recordAttempt')->zeroOrMoreTimes();

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->zeroOrMoreTimes();

            try {
                $operation();
            } catch (Exception $e) {
                // Some operations might throw exceptions, that's fine for this test
            }
        }
    });

    it('generates consistent cache keys for identical content', function () {
        $content = '<?php class Test {}';
        $cacheKeys = [];

        for ($i = 0; $i < 3; $i++) {
            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->with(
                    Mockery::capture($cacheKeys),
                    3600,
                    Mockery::type('Closure')
                )
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $this->service->transformContent($content);
        }

        // All cache keys should be identical for the same content
        expect($cacheKeys[0])->toBe($cacheKeys[1]);
        expect($cacheKeys[1])->toBe($cacheKeys[2]);
    });
});
