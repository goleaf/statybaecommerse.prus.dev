<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Domain\DomainException;
use App\Services\TranslationService;
use App\Support\ApiErrorResponse;
use App\Support\ErrorCodes;
use App\Support\RequestContext;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Throwable;
use TypeError;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     * Keeping the guard in place prevents sensitive credentials from leaking into session flashes.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     * The closure allows us to hook additional logging or reporting later without
     * changing Laravel's default behaviour while restoring the missing handler class.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            // Enhanced boot error handling with actionable messages
            // Only process if boot error detection is enabled and this looks like a boot error
            if ($this->shouldProcessBootError($e)) {
                $this->logBootError($e);

                // Track metrics if enabled
                if (config('exception-handling.performance.track_boot_errors', false)) {
                    $this->trackBootErrorMetrics($e);
                }
            }
        });
    }

    /**
     * Cached boot error detection configuration.
     */
    private static ?bool $bootErrorDetectionEnabled = null;

    /**
     * Determine if we should process this exception for boot error detection.
     * Optimized to fail fast for common non-boot exceptions.
     */
    private function shouldProcessBootError(Throwable $e): bool
    {
        // Cache the config value to avoid repeated config() calls
        if (self::$bootErrorDetectionEnabled === null) {
            self::$bootErrorDetectionEnabled = config('exception-handling.boot_error_detection.enabled', true);
        }

        // Fast exit if boot error detection is disabled
        if (! self::$bootErrorDetectionEnabled) {
            return false;
        }

        // Fast exit for common non-boot exceptions (most frequent first)
        if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
            return false;
        }

        return $this->isBootError($e);
    }

    /**
     * Determine if an exception is a boot-related error.
     */
    private function isBootError(Throwable $e): bool
    {
        return $this->matchesBootErrorPatterns($e) || $this->isBootRelatedFile($e);
    }

    /**
     * Cached boot error patterns for performance.
     */
    private static ?array $bootErrorPatterns = null;

    /**
     * Check if exception matches known boot error patterns.
     * Uses cached patterns for performance, optimized for production.
     */
    private function matchesBootErrorPatterns(Throwable $e): bool
    {
        // Initialize patterns once per request cycle
        if (self::$bootErrorPatterns === null) {
            self::$bootErrorPatterns = config('exception-handling.boot_error_detection.patterns', [
                'Interface',
                'not found',
                'undefined method',
                'Cannot declare class',
                'Fatal error',
                'Parse error',
                'Syntax error',
                'translations()',
                'TranslatableRecord',
            ]);
        }

        $message = $e->getMessage();

        // Optimized pattern matching - check most common patterns first
        // Use str_contains for better performance than regex
        foreach (self::$bootErrorPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cached boot-related paths for performance.
     */
    private static ?array $bootRelatedPaths = null;

    /**
     * Check if exception occurs in boot-related files.
     * Uses cached paths for optimal performance.
     */
    private function isBootRelatedFile(Throwable $e): bool
    {
        // Initialize paths once per request cycle
        if (self::$bootRelatedPaths === null) {
            self::$bootRelatedPaths = config('exception-handling.boot_error_detection.paths', [
                '/Models/',
                '/Contracts/',
                '/Providers/',
                '/bootstrap/',
            ]);
        }

        $file = $e->getFile();

        // Optimized path checking - most common paths first
        foreach (self::$bootRelatedPaths as $path) {
            if (str_contains($file, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Track boot error metrics for monitoring.
     */
    private function trackBootErrorMetrics(Throwable $e): void
    {
        // This could integrate with monitoring services like Sentry, New Relic, etc.
        // For now, we'll just log the metric
        Log::info('Boot error metric tracked', [
            'exception_type' => get_class($e),
            'error_pattern'  => $this->identifyErrorPattern($e),
            'file_type'      => $this->identifyFileType($e),
        ]);
    }

    /**
     * Identify the specific error pattern for metrics.
     */
    private function identifyErrorPattern(Throwable $e): string
    {
        $message = $e->getMessage();
        $patterns = config('exception-handling.boot_error_detection.patterns', []);

        foreach ($patterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return $pattern;
            }
        }

        return 'unknown';
    }

    /**
     * Identify the file type where the error occurred.
     */
    private function identifyFileType(Throwable $e): string
    {
        $file = $e->getFile();
        $paths = config('exception-handling.boot_error_detection.paths', []);

        foreach ($paths as $path) {
            if (str_contains($file, $path)) {
                return trim($path, '/');
            }
        }

        return 'other';
    }

    /**
     * Log boot errors with actionable debugging context.
     * Defensive against logging failures during boot issues.
     * Includes security measures to prevent information disclosure and injection attacks.
     */
    private function logBootError(Throwable $e): void
    {
        try {
            // Check rate limiting to prevent log spam attacks
            if ($this->isBootErrorRateLimited()) {
                return;
            }

            $context = $this->buildBootErrorContext($e);

            // Use structured logging with consistent format
            Log::error('Application boot failure detected', $context);

            // Use configured log channel for boot errors if available
            $channel = $this->getSecureLogChannel();
            if ($channel !== 'stack' && $channel !== null) {
                try {
                    Log::channel($channel)->critical('Boot failure', $context);
                } catch (Throwable) {
                    // Ignore channel-specific logging failures during boot issues
                }
            }
        } catch (Throwable) {
            // If logging fails completely, try a minimal fallback
            try {
                Log::emergency('Boot error logging failed', [
                    'original_error' => $this->sanitizeMessage($e->getMessage()),
                    'file'           => $this->sanitizeFilePath($e->getFile()),
                    'line'           => $e->getLine(),
                ]);
            } catch (Throwable) {
                // Complete logging failure - nothing more we can do
            }
        }
    }

    /**
     * Build comprehensive context for boot error logging.
     * Optimized to minimize object creation and method calls.
     * Includes security sanitization to prevent information disclosure.
     *
     * @return array<string, mixed>
     */
    private function buildBootErrorContext(Throwable $e): array
    {
        $message = $this->sanitizeMessage($e->getMessage());
        $isTranslatableError = $this->isTranslatableRecordError($e);

        $context = [
            'error_type'         => 'boot_failure',
            'exception_class'    => get_class($e),
            'message'            => $message,
            'file'               => $this->sanitizeFilePath($e->getFile()),
            'line'               => $e->getLine(),
            'actionable_message' => $this->getActionableMessage($e),
            'timestamp'          => now()->toISOString(),
            'environment'        => app()->environment(),
            'request_id'         => request()->header('X-Request-ID') ?? uniqid('req_', true),
        ];

        // Add specific context for interface implementation errors
        if ($isTranslatableError) {
            $context['fix_suggestion'] = 'Ensure all models implementing TranslatableRecord have a public translations(): HasMany method';
            $context['affected_models'] = ['Product', 'Brand', 'Collection', 'ProductVariant'];
            $context['interface_issue'] = true;
        }

        return $context;
    }

    /**
     * Check if exception is related to TranslatableRecord interface.
     */
    private function isTranslatableRecordError(Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'translations()') || str_contains($message, 'TranslatableRecord');
    }

    /**
     * Generate actionable error messages for common boot failures.
     * Sanitizes messages to prevent information disclosure.
     */
    private function getActionableMessage(Throwable $e): string
    {
        $message = $this->sanitizeMessage($e->getMessage());

        // Use match expression for cleaner pattern matching (PHP 8.0+)
        return match (true) {
            str_contains($message, 'translations()') => 'Missing translations() method in model implementing TranslatableRecord interface. Add: public function translations(): HasMany { return $this->hasMany(...); }',

            str_contains($message, 'TranslatableRecord') => 'TranslatableRecord interface implementation issue. Ensure all required methods are implemented.',

            str_contains($message, 'Class') && str_contains($message, 'not found') => 'Class autoloading issue. Run "composer dump-autoload" and check namespace declarations.',

            str_contains($message, 'Call to undefined method') => 'Method not found. Check method name spelling and ensure the method exists in the class or its traits.',

            str_contains($message, 'Parse error') || str_contains($message, 'Syntax error') => 'Syntax error detected. Check for missing semicolons, brackets, or invalid PHP syntax.',

            str_contains($message, 'Cannot declare class') => 'Class declaration conflict. Check for duplicate class names or namespace issues.',

            default => 'Boot error detected. Check the error message and stack trace for specific details.'
        };
    }

    /**
     * Sanitize error messages to prevent information disclosure and log injection.
     */
    private function sanitizeMessage(string $message): string
    {
        // Get maximum message length from config
        $maxLength = config('exception-handling.security.max_message_length', 2000);
        
        // Truncate if too long
        if (strlen($message) > $maxLength) {
            $message = substr($message, 0, $maxLength) . '... [truncated]';
        }

        // Remove potential secrets (passwords, keys, tokens)
        $sensitivePatterns = [
            '/password[:\s=]+[^\s\n]+/i',
            '/secret[:\s=]+[^\s\n]+/i',
            '/key[:\s=]+[^\s\n]+/i',
            '/token[:\s=]+[^\s\n]+/i',
            '/api[_-]?key[:\s=]+[^\s\n]+/i',
        ];

        foreach ($sensitivePatterns as $pattern) {
            $message = preg_replace($pattern, '[REDACTED]', $message) ?? $message;
        }

        // Remove null bytes and control characters that could cause log injection
        $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message) ?? $message;

        // Remove newlines that could inject fake log entries
        $message = str_replace(["\n", "\r"], ' ', $message);

        // Ensure valid UTF-8 encoding
        if (!mb_check_encoding($message, 'UTF-8')) {
            $message = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
        }

        return $message;
    }

    /**
     * Sanitize file paths to prevent information disclosure.
     */
    private function sanitizeFilePath(string $filePath): string
    {
        // Remove sensitive directory information
        $basePath = base_path();
        if (str_starts_with($filePath, $basePath)) {
            $filePath = str_replace($basePath, '[APP_ROOT]', $filePath);
        }

        // Remove potential path traversal attempts
        $filePath = str_replace(['../', '..\\'], '', $filePath);

        // Normalize path separators
        $filePath = str_replace('\\', '/', $filePath);

        return $filePath;
    }

    /**
     * Get secure log channel, validating against path traversal.
     */
    private function getSecureLogChannel(): ?string
    {
        $channel = config('exception-handling.boot_error_detection.log_channel', 'stack');
        
        if (!is_string($channel)) {
            return 'stack';
        }

        // Prevent path traversal in channel names
        if (str_contains($channel, '..') || str_contains($channel, '/') || str_contains($channel, '\\')) {
            return 'stack';
        }

        return $channel;
    }

    /**
     * Rate limiting for boot error logging to prevent spam attacks.
     */
    private static array $bootErrorCounts = [];
    
    private function isBootErrorRateLimited(): bool
    {
        if (!config('exception-handling.security.rate_limit_enabled', true)) {
            return false;
        }

        $key = 'boot_errors_' . date('Y-m-d-H-i');
        $maxErrors = config('exception-handling.security.max_boot_errors_per_minute', 10);
        
        if (!isset(self::$bootErrorCounts[$key])) {
            self::$bootErrorCounts[$key] = 0;
        }

        self::$bootErrorCounts[$key]++;

        // Clean old entries to prevent memory leaks
        if (count(self::$bootErrorCounts) > 60) {
            $currentMinute = date('Y-m-d-H-i');
            self::$bootErrorCounts = array_filter(
                self::$bootErrorCounts,
                fn($k) => $k >= $currentMinute,
                ARRAY_FILTER_USE_KEY
            );
        }

        return self::$bootErrorCounts[$key] > $maxErrors;
    }

    /**
     * Report or log an exception.
     *
     * For API requests, avoid treating validation issues as application errors
     * and downgrade type errors arising from bad route parameters to warnings.
     */
    public function report(Throwable $e): void
    {
        if ($e instanceof ValidationException) {
            // Don't spam logs for user input errors.
            return;
        }

        if ($e instanceof TypeError) {
            // Surface a concise warning to aid debugging without full stack traces.
            Log::warning('Type error triggered by request parameters.', [
                'message' => $e->getMessage(),
            ]);

            return;
        }

        parent::report($e);
    }

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            // APIs should continue receiving structured JSON payloads instead of redirects.
            return response()->json([
                'message' => $exception->getMessage(),
            ], 401);
        }

        if (Route::has('filament.admin.auth.login')) {
            // Filament routes in tests expect a redirect to the admin login screen instead of an exception.
            return redirect()->guest(route('filament.admin.auth.login'));
        }

        $fallback = Route::has('login') ? route('login') : '/login';

        // Preserve Laravel's default redirect behaviour for any other web guard.
        return redirect()->guest($fallback);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * Keep API responses consistent with our RFC 7807 problem format.
     */
    public function render($request, Throwable $e)
    {
        // Only customize API responses; defer to the framework for web views.
        if ($request instanceof Request && RequestContext::isApiRequest($request)) {
            $locale = RequestContext::resolveLocale($request);

            if ($e instanceof DomainException) {
                $errorCode = $e->errorCode()->value;
                $detail = TranslationService::get($e->translationKey(), $e->context(), $locale);

                return ApiErrorResponse::problem(
                    request: $request,
                    errorCode: $errorCode,
                    detail: $detail,
                    status: $e->status(),
                    title: ApiErrorResponse::titleFor($errorCode, $locale),
                    context: $e->context(),
                    locale: $locale,
                );
            }

            if ($e instanceof ValidationException) {
                $violations = collect($e->errors())
                    ->map(static function (array $messages, string $field): array {
                        $localizedMessages = array_values($messages);

                        return [
                            'field'    => $field,
                            'messages' => $localizedMessages,
                            'reason'   => $localizedMessages[0] ?? 'Invalid value.',
                        ];
                    })
                    ->values()
                    ->all();

                $detail = $e->getMessage() !== ''
                    ? $e->getMessage()
                    : (ErrorCodes::message(ErrorCodes::VALIDATION_FAILED, $locale) ?? 'The given data was invalid.');

                return ApiErrorResponse::problem(
                    request: $request,
                    errorCode: ErrorCodes::VALIDATION_FAILED,
                    detail: $detail,
                    status: $e->status,
                    title: ApiErrorResponse::titleFor(ErrorCodes::VALIDATION_FAILED, $locale),
                    context: ['violations' => $violations],
                    locale: $locale,
                );
            }

            if ($e instanceof TypeError) {
                // Treat parameter type mismatches as a bad request rather than a server error.
                $reason = $e->getMessage();

                // Trim noisy details to avoid leaking internals in responses.
                if (is_string($reason)) {
                    $reason = preg_replace('/ in \/.*$/', '', $reason) ?? $reason;
                }

                $detail = ErrorCodes::message(ErrorCodes::VALIDATION_FAILED, $locale)
                    ?? 'Invalid request parameters.';

                return ApiErrorResponse::problem(
                    request: $request,
                    errorCode: ErrorCodes::VALIDATION_FAILED,
                    detail: $detail,
                    status: 400,
                    title: ApiErrorResponse::titleFor(ErrorCodes::VALIDATION_FAILED, $locale),
                    context: ['reason' => $reason],
                    locale: $locale,
                );
            }
        }

        return parent::render($request, $e);
    }
}
