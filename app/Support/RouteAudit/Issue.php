<?php

declare(strict_types=1);

namespace App\Support\RouteAudit;

/**
 * Small value object helpers for representing route audit issues in a
 * consistent array structure for JSON and Markdown outputs.
 */
final class Issue
{
    public const SEVERITY_ERROR = 'error';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_INFO = 'info';

    /**
     * @param array<string, mixed> $context
     */
    public static function error(string $message, array $context = [], ?string $suggestion = null): array
    {
        return self::make(self::SEVERITY_ERROR, $message, $context, $suggestion);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function warning(string $message, array $context = [], ?string $suggestion = null): array
    {
        return self::make(self::SEVERITY_WARNING, $message, $context, $suggestion);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function info(string $message, array $context = [], ?string $suggestion = null): array
    {
        return self::make(self::SEVERITY_INFO, $message, $context, $suggestion);
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function make(string $severity, string $message, array $context = [], ?string $suggestion = null): array
    {
        $issue = [
            'severity' => $severity,
            'message'  => $message,
            'context'  => $context,
        ];

        if ($suggestion !== null) {
            $issue['suggestion'] = $suggestion;
        }

        return $issue;
    }
}
