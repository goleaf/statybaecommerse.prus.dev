<?php

declare(strict_types=1);

namespace App\Support\Exceptions;

use Throwable;

/**
 * Generates actionable error messages for common boot failures.
 */
final class ActionableMessageGenerator
{
    public function __construct(
        private readonly ErrorMessageSanitizer $sanitizer
    ) {}

    public function generate(Throwable $e): string
    {
        $message = $this->sanitizer->sanitizeMessage($e->getMessage());

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
}
