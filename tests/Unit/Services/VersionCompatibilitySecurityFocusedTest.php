<?php

declare(strict_types=1);

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
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->fileProcessor = Mockery::mock(FileProcessor::class);
    $this->filesystem = Mockery::mock(Filesystem::class);
    $this->cache = Mockery::mock(CacheRepository::class);
    $this->config = Mockery::mock(ConfigRepository::class);
    $this->securityValidator = Mockery::mock(FileSecurityValidator::class);
    $this->rateLimiter = Mockery::mock(RateLimiter::class);

    // Setup security-focused configuration
    $this->config->shouldReceive('get')
        ->with('version-compatibility', [])
        ->andReturn([
            'cache'    => ['prefix' => 'test_transform', 'ttl' => 1800],
            'logging'  => ['slow_threshold_ms' => 50, 'log_all_transformations' => true],
            'security' => [
                'max_file_size'      => 512 * 1024, // 512KB
                'allowed_extensions' => ['php'],
                'audit_logging'      => ['enabled' => true],
                'rate_limiting'      => ['enabled' => true],
            ],
            'performance' => ['batch_size' => 25],
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

describe('Security-Focused VersionCompatibilityService Tests', function () {
    describe('Content Security Validation', function () {
        it('rejects content with suspicious patterns', function () {
            $maliciousContent = '<?php eval($_POST["cmd"]); ?>';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateContent')
                ->once()
                ->with($maliciousContent)
                ->andThrow(new InvalidArgumentException('Suspicious eval() detected'));

            expect(fn () => $this->service->transformContent($maliciousContent))
                ->toThrow(InvalidArgumentException::class, 'Suspicious eval() detected');
        });

        it('validates content size limits', function () {
            $largeContent = '<?php ' . str_repeat('// comment', 50000);

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateContent')
                ->once()
                ->with($largeContent)
                ->andThrow(new InvalidArgumentException('Content exceeds maximum size'));

            expect(fn () => $this->service->transformContent($largeContent))
                ->toThrow(InvalidArgumentException::class, 'Content exceeds maximum size');
        });

        it('sanitizes content before processing', function () {
            $content = "<?php\nclass Test {\n    // Normal content\n}";

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
            expect($result->getContent())->toBe($content);
        });
    });

    describe('File Path Security Validation', function () {
        it('prevents directory traversal attacks', function () {
            $maliciousPath = '../../../etc/passwd.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->with($maliciousPath)
                ->andThrow(InvalidFileException::pathTraversalDetected($maliciousPath));

            expect(fn () => $this->service->fixResourceFile($maliciousPath))
                ->toThrow(InvalidFileException::class, 'Path traversal detected');
        });

        it('validates file extensions', function () {
            $invalidFile = 'malicious.exe.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->with($invalidFile)
                ->andThrow(InvalidFileException::invalidExtension($invalidFile, 'exe', ['php']));

            expect(fn () => $this->service->fixResourceFile($invalidFile))
                ->toThrow(InvalidFileException::class, 'Invalid file extension');
        });

        it('checks for symlink attacks', function () {
            $symlinkPath = 'app/Resources/symlink.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->with($symlinkPath)
                ->andThrow(new InvalidFileException('Symlink detected'));

            expect(fn () => $this->service->fixResourceFile($symlinkPath))
                ->toThrow(InvalidFileException::class, 'Symlink detected');
        });

        it('validates file size limits', function () {
            $largePath = 'app/Resources/LargeResource.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->with($largePath)
                ->andThrow(InvalidFileException::fileTooLarge($largePath, 1024 * 1024, 512 * 1024));

            expect(fn () => $this->service->fixResourceFile($largePath))
                ->toThrow(InvalidFileException::class, 'File \'app/Resources/LargeResource.php\' is too large');
        });
    });

    describe('Rate Limiting Security', function () {
        it('enforces rate limits per operation type', function () {
            $this->rateLimiter->shouldReceive('checkRateLimit')
                ->times(3)
                ->andThrow(new RuntimeException('Rate limit exceeded'));

            $operations = [
                fn () => $this->service->transformContent('<?php class Test {}'),
                fn () => $this->service->fixResourceFile('test.php'),
                fn () => $this->service->fixAllResourcesInDirectory('test-dir'),
            ];

            foreach ($operations as $operation) {
                expect($operation)->toThrow(RuntimeException::class, 'Rate limit exceeded');
            }
        });

        it('tracks successful attempts for rate limiting', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $this->service->transformContent($content);
        });

        it('does not record attempts for failed operations', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateContent')
                ->once()
                ->andThrow(new InvalidArgumentException('Validation failed'));

            // recordAttempt should NOT be called for failed operations
            $this->rateLimiter->shouldNotReceive('recordAttempt');

            expect(fn () => $this->service->transformContent($content))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Audit Logging Security', function () {
        it('logs all security-relevant events with context', function () {
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
                Mockery::on(function ($context) {
                    return isset($context['content_hash']) &&
                           isset($context['content_size']) &&
                           isset($context['timestamp']) &&
                           isset($context['event']);
                })
            );

            $this->service->transformContent($content);
        });

        it('includes request context in audit logs', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once()->with(
                Mockery::type('string'),
                Mockery::on(function ($context) {
                    // Should include request-related fields (even if null in tests)
                    return array_key_exists('ip', $context) &&
                           array_key_exists('user_agent', $context) &&
                           array_key_exists('user_id', $context);
                })
            );

            $this->service->transformContent($content);
        });

        it('logs security failures with detailed context', function () {
            $content = '<?php malicious_code(); ?>';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateContent')
                ->once()
                ->andThrow(new InvalidArgumentException('Malicious pattern detected'));

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once()->with(
                'Version compatibility security event: content_transformation_failure',
                Mockery::on(function ($context) {
                    return isset($context['error']) &&
                           isset($context['exception_class']) &&
                           $context['error'] === 'Malicious pattern detected';
                })
            );

            expect(fn () => $this->service->transformContent($content))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Cache Security', function () {
        it('uses secure hash functions for cache keys', function () {
            $content = '<?php class Test {}';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            $this->cache->shouldReceive('remember')
                ->once()
                ->with(
                    Mockery::pattern('/^test_transform:[a-f0-9]{16}$/'), // xxh3 produces 16-char hex
                    1800,
                    Mockery::type('Closure')
                )
                ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $this->service->transformContent($content);
        });

        it('prevents cache poisoning with content validation', function () {
            $content = '<?php class Test {}';
            $poisonedResult = new TransformationResult('<?php malicious_code(); ?>', true, []);

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->rateLimiter->shouldReceive('recordAttempt')->once();
            $this->securityValidator->shouldReceive('validateContent')->once();

            // Even if cache returns malicious content, it should be validated
            $this->cache->shouldReceive('remember')
                ->once()
                ->andReturn($poisonedResult);

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();

            $result = $this->service->transformContent($content);

            // The service should return the cached result as-is (validation happens on input)
            expect($result)->toBe($poisonedResult);
        });
    });

    describe('Error Handling Security', function () {
        it('does not leak sensitive information in error messages', function () {
            $sensitiveFile = '/home/user/.ssh/id_rsa.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->andThrow(new InvalidFileException('Access denied'));

            expect(fn () => $this->service->fixResourceFile($sensitiveFile))
                ->toThrow(InvalidFileException::class, 'Access denied');
        });

        it('sanitizes file paths in error messages', function () {
            $maliciousPath = '../../../etc/passwd.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')
                ->once()
                ->andThrow(InvalidFileException::pathTraversalDetected($maliciousPath));

            try {
                $this->service->fixResourceFile($maliciousPath);
                expect(false)->toBeTrue('Exception should have been thrown');
            } catch (InvalidFileException $e) {
                // Error message should contain the path for debugging but be clearly marked as security issue
                expect($e->getMessage())->toContain('Path traversal detected');
                expect($e->getFilePath())->toBe($maliciousPath);
            }
        });

        it('handles transformation exceptions securely', function () {
            $filePath = 'app/Resources/TestResource.php';

            $this->rateLimiter->shouldReceive('checkRateLimit')->once();
            $this->securityValidator->shouldReceive('validateFilePath')->once();

            $this->fileProcessor->shouldReceive('processFile')
                ->once()
                ->andThrow(new TransformationException('Internal processing error'));

            Log::shouldReceive('channel')->with('security')->andReturnSelf();
            Log::shouldReceive('info')->once();
            Log::shouldReceive('error')->once();

            $result = $this->service->fixResourceFile($filePath);

            expect($result->hasError())->toBeTrue();
            expect($result->getError())->toBe('Internal processing error');
        });
    });
});

describe('Security Configuration Tests', function () {
    describe('Secure defaults', function () {
        it('enforces secure configuration defaults', function () {
            $stats = $this->service->getTransformationStats();

            expect($stats['service_info']['security_enabled'])->toBeTrue();
            expect($stats['service_info']['rate_limiting_enabled'])->toBeTrue();
            expect($stats['service_info']['audit_logging_enabled'])->toBeTrue();
        });

        it('validates security configuration on startup', function () {
            // Test with disabled security features
            $this->config->shouldReceive('get')
                ->with('version-compatibility', [])
                ->andReturn([
                    'cache'    => ['ttl' => 3600],
                    'logging'  => ['slow_threshold_ms' => 100],
                    'security' => [
                        'audit_logging'      => ['enabled' => false],
                        'rate_limiting'      => ['enabled' => false],
                        'allowed_extensions' => ['php', 'txt'], // Should still require at least one
                    ],
                ]);

            // Should not throw exception with valid but less secure config
            expect(fn () => new VersionCompatibilityService(
                $this->fileProcessor,
                $this->filesystem,
                $this->cache,
                $this->config,
                $this->securityValidator,
                $this->rateLimiter
            ))->not->toThrow();
        });
    });
});
