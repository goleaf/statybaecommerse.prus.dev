<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

/**
 * Sanitizes error messages to prevent information disclosure and injection attacks.
 */
final class ErrorMessageSanitizer
{
    private const SENSITIVE_PATTERNS = [
        '/password[:\s=]+[^\s\n]+/i',
        '/secret[:\s=]+[^\s\n]+/i',
        '/key[:\s=]+[^\s\n]+/i',
        '/token[:\s=]+[^\s\n]+/i',
        '/api[_-]?key[:\s=]+[^\s\n]+/i',
        '/bearer\s+[a-zA-Z0-9\-_\.]+/i',
        '/authorization[:\s=]+[^\s\n]+/i',
        '/cookie[:\s=]+[^\s\n]+/i',
        '/session[:\s=]+[^\s\n]+/i',
        '/csrf[_-]?token[:\s=]+[^\s\n]+/i',
        '/x-api-key[:\s=]+[^\s\n]+/i',
        '/database[_-]?url[:\s=]+[^\s\n]+/i',
        '/redis[_-]?url[:\s=]+[^\s\n]+/i',
        '/mail[_-]?password[:\s=]+[^\s\n]+/i',
        '/aws[_-]?secret[:\s=]+[^\s\n]+/i',
        '/private[_-]?key[:\s=]+[^\s\n]+/i',
    ];

    private static ?int $maxLength = null;

    public function sanitizeMessage(string $message): string
    {
        // Get maximum message length from config
        if (app()->environment('testing')) {
            $maxLength = config('exception-handling.security.max_message_length', 2000);
        } else {
            if (self::$maxLength === null) {
                self::$maxLength = config('exception-handling.security.max_message_length', 2000);
            }
            $maxLength = self::$maxLength;
        }

        // Truncate if too long
        if (strlen($message) > $maxLength) {
            $message = substr($message, 0, $maxLength) . '... [truncated]';
        }

        // Remove potential secrets
        foreach (self::SENSITIVE_PATTERNS as $pattern) {
            $message = preg_replace($pattern, '[REDACTED]', $message) ?? $message;
        }

        // Remove null bytes and control characters that could cause log injection
        $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message) ?? $message;

        // Remove newlines that could inject fake log entries
        $message = str_replace(["\n", "\r"], ' ', $message);

        // Ensure valid UTF-8 encoding
        if (! mb_check_encoding($message, 'UTF-8')) {
            $message = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
        }

        return $message;
    }

    public function sanitizeFilePath(string $filePath): string
    {
        // Remove sensitive directory information
        $basePath = base_path();
        if (str_starts_with($filePath, $basePath)) {
            $filePath = str_replace($basePath, '[APP_ROOT]', $filePath);
        }

        // Also handle common server paths
        $commonPaths = [
            '/var/www/html'     => '[APP_ROOT]',
            '/home/forge'       => '[APP_ROOT]',
            '/var/www'          => '[APP_ROOT]',
            'C:\\xampp\\htdocs' => '[APP_ROOT]',
            'C:\\wamp\\www'     => '[APP_ROOT]',
        ];

        foreach ($commonPaths as $path => $replacement) {
            if (str_starts_with($filePath, $path)) {
                $filePath = str_replace($path, $replacement, $filePath);
                break;
            }
        }

        // Remove potential path traversal attempts
        $filePath = str_replace(['../', '..\\'], '', $filePath);

        // Normalize path separators
        return str_replace('\\', '/', $filePath);
    }

    /**
     * Reset cached configuration (for testing).
     */
    public static function resetCache(): void
    {
        self::$maxLength = null;
    }
}
