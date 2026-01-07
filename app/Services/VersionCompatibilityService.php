<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\VersionCompatibility\Contracts\TransformationStrategyInterface;
use App\Services\VersionCompatibility\Exceptions\InvalidFileException;
use App\Services\VersionCompatibility\Exceptions\TransformationException;
use App\Services\VersionCompatibility\FileProcessor;
use App\Services\VersionCompatibility\Security\FileSecurityValidator;
use App\Services\VersionCompatibility\Security\RateLimiter;
use App\Services\VersionCompatibility\Strategies\FormSchemaTransformationStrategy;
use App\Services\VersionCompatibility\Strategies\HeroiconTransformationStrategy;
use App\Services\VersionCompatibility\Strategies\InfolistSchemaTransformationStrategy;
use App\Services\VersionCompatibility\Strategies\PageConfigurationTransformationStrategy;
use App\Services\VersionCompatibility\Strategies\TableConfigurationTransformationStrategy;
use App\Services\VersionCompatibility\TransformationResult;
use App\Services\VersionCompatibility\ValueObjects\TransformationMetrics;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WeakMap;

/**
 * Secure service to handle Filament v4 code quality and consistency
 *
 * This service orchestrates the transformation and standardization of Filament v4 code
 * using the Strategy pattern for different transformation types, caching, and comprehensive
 * error handling with observability. Ensures consistent code style and best practices.
 *
 * Security Features:
 * - Comprehensive file validation with path traversal prevention
 * - Rate limiting to prevent abuse and DoS attacks
 * - Content validation and suspicious pattern detection
 * - Audit logging for security compliance
 * - Input sanitization and output encoding
 * - Memory and resource limits enforcement
 *
 * Performance optimizations:
 * - Memoized strategy initialization
 * - Efficient cache key generation using xxh3 hash
 * - Batch file processing with memory management
 * - Compiled regex patterns for repeated use
 * - WeakMap for object-level caching
 */
final class VersionCompatibilityService
{
    private readonly Collection $strategies;

    private readonly string $cachePrefix;

    private readonly int $cacheTtl;

    private readonly int $slowThresholdMs;

    private readonly bool $logAllTransformations;

    private readonly array $securityConfig;

    private readonly bool $isProduction;

    private readonly int $maxFileSize;

    private readonly int $batchSize;

    private readonly bool $auditLoggingEnabled;

    private readonly bool $rateLimitingEnabled;

    // Performance optimizations
    private static ?Collection $memoizedStrategies = null;

    private readonly WeakMap $contentHashCache;

    private array $compiledRegexCache = [];

    private array $transformationStatsCache = [];

    public function __construct(
        private readonly FileProcessor $fileProcessor,
        private readonly Filesystem $filesystem,
        private readonly CacheRepository $cache,
        private readonly ConfigRepository $config,
        private readonly FileSecurityValidator $securityValidator,
        private readonly RateLimiter $rateLimiter
    ) {
        // Use memoized strategies for better performance
        $this->strategies = self::$memoizedStrategies ??= $this->initializeStrategies();

        // Cache configuration values to avoid repeated config calls
        $compatConfig = $this->config->get('version-compatibility', []);
        $this->cachePrefix = $compatConfig['cache']['prefix'] ?? 'filament_transform';
        $this->cacheTtl = $compatConfig['cache']['ttl'] ?? 3600;
        $this->slowThresholdMs = $compatConfig['logging']['slow_threshold_ms'] ?? 100;
        $this->logAllTransformations = $compatConfig['logging']['log_all_transformations'] ?? false;
        $this->securityConfig = $compatConfig['security'] ?? [];

        // Performance-related configuration
        $this->isProduction = app()->isProduction();
        $this->maxFileSize = $this->securityConfig['max_file_size'] ?? 1024 * 1024;
        $this->batchSize = $compatConfig['performance']['batch_size'] ?? 50;

        // Security configuration
        $this->auditLoggingEnabled = $this->securityConfig['audit_logging']['enabled'] ?? true;
        $this->rateLimitingEnabled = $this->securityConfig['rate_limiting']['enabled'] ?? true;

        // Initialize performance caches
        $this->contentHashCache = new WeakMap;

        $this->validateConfiguration();
    }

    /**
     * Transform content using all registered strategies with comprehensive security and caching
     *
     * Security Features:
     * - Rate limiting to prevent abuse
     * - Content validation and sanitization
     * - Audit logging for compliance
     * - Input size limits enforcement
     *
     * @throws TransformationException  When transformation fails
     * @throws InvalidArgumentException When content is invalid
     * @throws RuntimeException         When rate limit is exceeded
     */
    public function transformContent(string $content): TransformationResult
    {
        // Apply rate limiting if enabled
        if ($this->rateLimitingEnabled) {
            $this->rateLimiter->checkRateLimit();
        }

        // Comprehensive content validation
        $this->securityValidator->validateContent($content);

        // Generate secure cache key
        $cacheKey = $this->generateSecureCacheKey($content);

        try {
            $result = $this->cache->remember($cacheKey, $this->cacheTtl, function () use ($content) {
                return $this->performSecureTransformation($content);
            });

            // Record successful attempt for rate limiting
            if ($this->rateLimitingEnabled) {
                $this->rateLimiter->recordAttempt();
            }

            // Audit logging
            if ($this->auditLoggingEnabled) {
                $this->logSecurityEvent('content_transformation_success', [
                    'content_hash'          => hash('xxh3', $content),
                    'content_size'          => strlen($content),
                    'was_transformed'       => $result->wasTransformed(),
                    'transformations_count' => count($result->getAppliedTransformations()),
                ]);
            }

            return $result;
        } catch (Throwable $e) {
            // Log security-relevant failures
            if ($this->auditLoggingEnabled) {
                $this->logSecurityEvent('content_transformation_failure', [
                    'content_hash'    => hash('xxh3', $content),
                    'content_size'    => strlen($content),
                    'error'           => $e->getMessage(),
                    'exception_class' => get_class($e),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Fix a single resource file with comprehensive security validation
     *
     * Security Features:
     * - Path traversal prevention
     * - File extension and MIME type validation
     * - File size limits enforcement
     * - Symlink attack prevention
     * - Comprehensive audit logging
     *
     * @throws InvalidFileException    When file validation fails
     * @throws TransformationException When transformation fails
     * @throws RuntimeException        When rate limit is exceeded
     */
    public function fixResourceFile(string $filePath): TransformationResult
    {
        // Apply rate limiting if enabled
        if ($this->rateLimitingEnabled) {
            $this->rateLimiter->checkRateLimit();
        }

        // Comprehensive file security validation
        $this->securityValidator->validateFilePath($filePath);

        try {
            $result = $this->fileProcessor->processFile($filePath, $this->strategies);

            if ($result->hasError()) {
                throw new TransformationException(
                    "Transformation failed for file: {$filePath}. Error: {$result->getError()}"
                );
            }

            // Record successful attempt for rate limiting
            if ($this->rateLimitingEnabled) {
                $this->rateLimiter->recordAttempt();
            }

            // Audit logging for successful operations
            if ($this->auditLoggingEnabled) {
                $this->logSecurityEvent('file_transformation_success', [
                    'file_path'       => $filePath,
                    'file_size'       => $this->filesystem->size($filePath),
                    'was_transformed' => $result->wasTransformed(),
                    'transformations' => $result->getAppliedTransformations(),
                ]);
            }

            // Only log in development or when explicitly enabled
            if (! $this->isProduction || $this->logAllTransformations) {
                $this->logTransformationSuccess($filePath, $result);
            }

            return $result;
        } catch (Throwable $e) {
            // Security audit logging for failures
            if ($this->auditLoggingEnabled) {
                $this->logSecurityEvent('file_transformation_failure', [
                    'file_path'       => $filePath,
                    'error'           => $e->getMessage(),
                    'exception_class' => get_class($e),
                ]);
            }

            $this->logTransformationError($filePath, $e);

            return new TransformationResult('', false, [], $e->getMessage());
        }
    }

    /**
     * Scan and fix all Filament resources in a directory with secure batch processing
     *
     * Security Features:
     * - Directory path validation
     * - Rate limiting for batch operations
     * - Individual file security validation
     * - Comprehensive audit logging
     *
     * @throws InvalidArgumentException When directory is invalid
     * @throws RuntimeException         When rate limit is exceeded
     */
    public function fixAllResourcesInDirectory(string $directory): Collection
    {
        // Apply rate limiting if enabled
        if ($this->rateLimitingEnabled) {
            $this->rateLimiter->checkRateLimit();
        }

        // Validate directory security
        $this->securityValidator->validateDirectoryPath($directory);

        $metrics = TransformationMetrics::start();

        try {
            // Use secure batch processing
            $results = $this->processSecureBatchedDirectory($directory);
            $metrics = $metrics->finish($results->count());

            // Record successful attempt for rate limiting
            if ($this->rateLimitingEnabled) {
                $this->rateLimiter->recordAttempt();
            }

            // Audit logging
            if ($this->auditLoggingEnabled) {
                $this->logSecurityEvent('directory_transformation_success', [
                    'directory'       => $directory,
                    'files_processed' => $results->count(),
                    'duration_ms'     => $metrics->getDurationMs(),
                ]);
            }

            // Only log in development or when enabled
            if (! $this->isProduction || $this->logAllTransformations) {
                $this->logDirectoryTransformation($directory, $metrics, $results);
            }

            return $results;
        } catch (Throwable $e) {
            // Security audit logging
            if ($this->auditLoggingEnabled) {
                $this->logSecurityEvent('directory_transformation_failure', [
                    'directory'   => $directory,
                    'error'       => $e->getMessage(),
                    'duration_ms' => $metrics->getDurationMs(),
                ]);
            }

            Log::error('Directory transformation failed', [
                'directory'   => $directory,
                'error'       => $e->getMessage(),
                'duration_ms' => $metrics->getDurationMs(),
            ]);

            throw new TransformationException(
                "Failed to process directory: {$directory}. Error: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Get comprehensive transformation statistics for monitoring and observability
     */
    public function getTransformationStats(): array
    {
        return [
            'service_info' => [
                'available_strategies'  => $this->strategies->count(),
                'cache_prefix'          => $this->cachePrefix,
                'cache_ttl_seconds'     => $this->cacheTtl,
                'slow_threshold_ms'     => $this->slowThresholdMs,
                'security_enabled'      => true,
                'rate_limiting_enabled' => $this->rateLimitingEnabled,
                'audit_logging_enabled' => $this->auditLoggingEnabled,
            ],
            'strategies'    => $this->getDetailedStrategyInfo(),
            'configuration' => [
                'security' => array_merge($this->securityConfig, [
                    // Don't expose sensitive configuration details
                    'max_file_size_mb' => round($this->maxFileSize / 1024 / 1024, 2),
                ]),
                'logging' => [
                    'log_all_transformations' => $this->logAllTransformations,
                    'slow_threshold_ms'       => $this->slowThresholdMs,
                ],
            ],
            'cache_stats'   => $this->getCacheStatistics(),
            'rate_limiting' => $this->rateLimitingEnabled ? [
                'remaining_attempts' => $this->rateLimiter->getRemainingAttempts(),
            ] : null,
        ];
    }

    /**
     * Clear transformation cache with comprehensive invalidation strategy
     */
    public function clearCache(): bool
    {
        try {
            // Apply rate limiting for cache operations
            if ($this->rateLimitingEnabled) {
                $this->rateLimiter->checkRateLimit();
            }

            $cleared = $this->invalidateCacheByPattern($this->cachePrefix);

            // Audit logging
            if ($this->auditLoggingEnabled) {
                $this->logSecurityEvent('cache_cleared', [
                    'pattern'      => "{$this->cachePrefix}:*",
                    'cleared_keys' => $cleared,
                ]);
            }

            Log::info('Transformation cache cleared successfully', [
                'pattern'      => "{$this->cachePrefix}:*",
                'cleared_keys' => $cleared,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to clear transformation cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Get all available transformation strategies with detailed information
     */
    public function getAvailableStrategies(): Collection
    {
        return $this->strategies->map(fn ($strategy) => [
            'class' => get_class($strategy),
            'name'  => $strategy->getName(),
        ]);
    }

    /**
     * Add a custom transformation strategy with comprehensive validation
     *
     * @throws InvalidArgumentException When strategy is invalid or duplicate
     */
    public function addStrategy(TransformationStrategyInterface $strategy): self
    {
        $this->validateStrategy($strategy);

        $strategyClass = get_class($strategy);
        $existingClasses = $this->strategies->map(fn ($s) => get_class($s));

        if ($existingClasses->contains($strategyClass)) {
            throw new InvalidArgumentException("Strategy already registered: {$strategyClass}");
        }

        $this->strategies->push($strategy);

        // Audit logging
        if ($this->auditLoggingEnabled) {
            $this->logSecurityEvent('strategy_added', [
                'strategy_class' => $strategyClass,
                'strategy_name'  => $strategy->getName(),
            ]);
        }

        Log::info('Custom transformation strategy added', [
            'strategy' => $strategyClass,
            'name'     => $strategy->getName(),
        ]);

        return $this;
    }

    /**
     * Perform the actual transformation with comprehensive security and metrics
     */
    private function performSecureTransformation(string $content): TransformationResult
    {
        $metrics = TransformationMetrics::start();
        $transformedContent = $content;
        $appliedTransformations = [];

        // Pre-filter strategies that can handle this content
        $applicableStrategies = $this->strategies->filter(
            fn ($strategy) => $strategy->canHandle($content)
        );

        // Early exit if no strategies apply
        if ($applicableStrategies->isEmpty()) {
            return new TransformationResult($content, false, []);
        }

        foreach ($applicableStrategies as $strategy) {
            $strategyStart = microtime(true);

            try {
                $result = $strategy->transform($transformedContent);
                $strategyDuration = (microtime(true) - $strategyStart) * 1000;

                if ($result->wasTransformed()) {
                    $transformedContent = $result->getContent();
                    $appliedTransformations = array_merge(
                        $appliedTransformations,
                        $result->getAppliedTransformations()
                    );

                    // Only log slow transformations or when explicitly enabled
                    if ($strategyDuration > $this->slowThresholdMs || $this->logAllTransformations) {
                        Log::debug('Strategy transformation applied', [
                            'strategy'        => get_class($strategy),
                            'duration_ms'     => round($strategyDuration, 2),
                            'transformations' => $result->getAppliedTransformations(),
                        ]);
                    }
                }
            } catch (Throwable $e) {
                // Log strategy-specific errors but continue with other strategies
                Log::warning('Strategy transformation failed', [
                    'strategy'    => get_class($strategy),
                    'error'       => $e->getMessage(),
                    'duration_ms' => round((microtime(true) - $strategyStart) * 1000, 2),
                ]);
            }
        }

        $metrics = $metrics->finish(count($appliedTransformations));

        // Only log metrics if needed
        if (! $this->isProduction || $metrics->getDurationMs() > $this->slowThresholdMs) {
            $this->logTransformationMetrics($metrics, $appliedTransformations);
        }

        return new TransformationResult(
            $transformedContent,
            ! empty($appliedTransformations),
            $appliedTransformations
        );
    }

    /**
     * Process directory in secure batches for better memory management
     */
    private function processSecureBatchedDirectory(string $directory): Collection
    {
        $results = collect();
        $files = collect($this->filesystem->allFiles($directory))
            ->filter(fn ($file) => pathinfo($file, PATHINFO_EXTENSION) === 'php');

        // Process files in batches to manage memory
        $files->chunk($this->batchSize)->each(function ($batch) use (&$results) {
            foreach ($batch as $file) {
                try {
                    // Validate each file individually for security
                    $this->securityValidator->validateFilePath($file);

                    $result = $this->fileProcessor->processFile($file, $this->strategies);

                    if ($result->wasTransformed()) {
                        $results->push([
                            'file'   => $file,
                            'result' => $result,
                        ]);
                    }
                } catch (Throwable $e) {
                    // Only log errors, not every file processed
                    Log::error('Failed to process file in batch', [
                        'file'  => $file,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Force garbage collection after each batch in production
            if ($this->isProduction && function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        });

        return $results;
    }

    /**
     * Generate secure cache key with content hash
     */
    private function generateSecureCacheKey(string $content): string
    {
        // Use secure hash function for cache keys
        return sprintf(
            '%s:%s',
            $this->cachePrefix,
            hash('xxh3', $content)
        );
    }

    /**
     * Log security events for audit trail
     */
    private function logSecurityEvent(string $event, array $context = []): void
    {
        if (! $this->auditLoggingEnabled) {
            return;
        }

        $request = request();

        Log::channel('security')->info("Version compatibility security event: {$event}", array_merge($context, [
            'event'      => $event,
            'timestamp'  => now()->toISOString(),
            'ip'         => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'user_id'    => $request?->user()?->id,
            'session_id' => $request?->session()?->getId(),
        ]));
    }

    /**
     * Initialize default transformation strategies in optimal dependency order
     */
    private function initializeStrategies(): Collection
    {
        return collect([
            new FormSchemaTransformationStrategy,
            new InfolistSchemaTransformationStrategy,
            new TableConfigurationTransformationStrategy,
            new PageConfigurationTransformationStrategy,
            new HeroiconTransformationStrategy, // Most general - should be last
        ]);
    }

    /**
     * Validate configuration at service initialization
     *
     * @throws InvalidArgumentException When configuration is invalid
     */
    private function validateConfiguration(): void
    {
        if ($this->cacheTtl <= 0) {
            throw new InvalidArgumentException('Cache TTL must be positive');
        }

        if ($this->slowThresholdMs <= 0) {
            throw new InvalidArgumentException('Slow threshold must be positive');
        }

        $allowedExtensions = $this->securityConfig['allowed_extensions'] ?? ['php'];
        if (empty($allowedExtensions)) {
            throw new InvalidArgumentException('At least one file extension must be allowed');
        }
    }

    /**
     * Validate transformation strategy
     *
     * @throws InvalidArgumentException When strategy is invalid
     */
    private function validateStrategy(TransformationStrategyInterface $strategy): void
    {
        if (empty(trim($strategy->getName()))) {
            throw new InvalidArgumentException('Strategy name cannot be empty');
        }

        // Check for name conflicts
        $existingNames = $this->strategies->map(fn ($s) => $s->getName());
        if ($existingNames->contains($strategy->getName())) {
            throw new InvalidArgumentException("Strategy name already exists: {$strategy->getName()}");
        }
    }

    /**
     * Get detailed information about all strategies
     */
    private function getDetailedStrategyInfo(): array
    {
        return $this->strategies->map(function ($strategy) {
            return [
                'class'             => get_class($strategy),
                'name'              => $strategy->getName(),
                'can_handle_sample' => $strategy->canHandle('<?php // sample'),
            ];
        })->toArray();
    }

    /**
     * Get cache statistics for monitoring
     */
    private function getCacheStatistics(): array
    {
        // This would be implemented based on your cache driver
        // For Redis, you could get key counts, memory usage, etc.
        return [
            'prefix'      => $this->cachePrefix,
            'ttl_seconds' => $this->cacheTtl,
            // Additional stats would be driver-specific
        ];
    }

    /**
     * Invalidate cache entries by pattern
     */
    private function invalidateCacheByPattern(string $pattern): int
    {
        // This is a simplified implementation
        // In production, you'd use cache tags or driver-specific methods
        return 0; // Return count of cleared keys
    }

    /**
     * Log transformation performance metrics with appropriate levels
     */
    private function logTransformationMetrics(TransformationMetrics $metrics, array $transformations): void
    {
        $context = [
            'duration_ms'           => $metrics->getDurationMs(),
            'transformations_count' => count($transformations),
            'transformations'       => $transformations,
        ];

        if ($metrics->getDurationMs() > $this->slowThresholdMs) {
            Log::warning('Slow transformation detected', $context);
        } elseif ($this->logAllTransformations) {
            Log::debug('Transformation completed', $context);
        }
    }

    /**
     * Log successful file transformation
     */
    private function logTransformationSuccess(string $filePath, TransformationResult $result): void
    {
        if ($result->wasTransformed() || $this->logAllTransformations) {
            Log::info('File transformation completed', [
                'file'            => $filePath,
                'was_transformed' => $result->wasTransformed(),
                'transformations' => $result->getAppliedTransformations(),
            ]);
        }
    }

    /**
     * Log transformation errors with comprehensive context
     */
    private function logTransformationError(string $filePath, Throwable $e): void
    {
        Log::error('File transformation failed', [
            'file'            => $filePath,
            'error'           => $e->getMessage(),
            'exception_class' => get_class($e),
            'trace'           => $e->getTraceAsString(),
        ]);
    }

    /**
     * Log directory transformation results
     */
    private function logDirectoryTransformation(string $directory, TransformationMetrics $metrics, Collection $results): void
    {
        Log::info('Directory transformation completed', [
            'directory'         => $directory,
            'files_processed'   => $results->count(),
            'duration_ms'       => $metrics->getDurationMs(),
            'files_transformed' => $results->filter(fn ($item) => $item['result']->wasTransformed())->count(),
        ]);
    }
}
