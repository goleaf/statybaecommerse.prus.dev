<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Handles logging of boot errors with proper error handling and security.
 */
final class BootErrorLogger
{
    public function __construct(
        private readonly BootErrorRateLimiter $rateLimiter,
        private readonly BootErrorContextBuilder $contextBuilder
    ) {}

    public function log(Throwable $e): void
    {
        try {
            // Check rate limiting to prevent log spam attacks
            if ($this->rateLimiter->isRateLimited()) {
                return;
            }

            $context = $this->contextBuilder->buildContext($e);

            // Use structured logging with consistent format
            Log::error('Application boot failure detected', $context);

            // Use configured log channel for boot errors if available
            $this->logToSecureChannel($context);
        } catch (Throwable) {
            // If logging fails completely, try a minimal fallback
            $this->fallbackLog($e);
        }
    }

    private function logToSecureChannel(array $context): void
    {
        $channel = $this->getSecureLogChannel();
        if ($channel !== 'stack' && $channel !== null) {
            try {
                Log::channel($channel)->critical('Boot failure', $context);
            } catch (Throwable) {
                // Ignore channel-specific logging failures during boot issues
            }
        }
    }

    private function fallbackLog(Throwable $e): void
    {
        try {
            $sanitizer = new ErrorMessageSanitizer;
            Log::emergency('Boot error logging failed', [
                'original_error' => $sanitizer->sanitizeMessage($e->getMessage()),
                'file'           => $sanitizer->sanitizeFilePath($e->getFile()),
                'line'           => $e->getLine(),
            ]);
        } catch (Throwable) {
            // Complete logging failure - nothing more we can do
        }
    }

    private function getSecureLogChannel(): ?string
    {
        $channel = config('exception-handling.boot_error_detection.log_channel', 'stack');

        if (! is_string($channel)) {
            return 'stack';
        }

        // Prevent path traversal in channel names
        if (str_contains($channel, '..') || str_contains($channel, '/') || str_contains($channel, '\\')) {
            return 'stack';
        }

        return $channel;
    }
}
