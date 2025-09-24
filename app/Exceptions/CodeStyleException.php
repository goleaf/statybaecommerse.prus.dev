<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * CodeStyleException
 *
 * Custom exception class for CodeStyleException error handling with detailed error information and context.
 */
final class CodeStyleException extends Exception
{
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(string $message = 'Code style violation detected', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Handle invalidImportOrder functionality with proper error handling.
     */
    public static function invalidImportOrder(string $file, array $expectedOrder, array $actualOrder): self
    {
        $message = sprintf('Invalid import order in %s. Expected: %s, Actual: %s', $file, implode(', ', $expectedOrder), implode(', ', $actualOrder));

        return new self($message);
    }

    /**
     * Handle missingSpaceInUnionTypes functionality with proper error handling.
     */
    public static function missingSpaceInUnionTypes(string $file, int $line): self
    {
        $message = sprintf('Missing space in union type declaration in %s at line %d', $file, $line);

        return new self($message);
    }

    /**
     * Handle invalidClosureSpacing functionality with proper error handling.
     */
    public static function invalidClosureSpacing(string $file, int $line): self
    {
        $message = sprintf('Invalid closure spacing in %s at line %d', $file, $line);

        return new self($message);
    }

    /**
     * Handle trailingWhitespace functionality with proper error handling.
     */
    public static function trailingWhitespace(string $file, int $line): self
    {
        $message = sprintf('Trailing whitespace detected in %s at line %d', $file, $line);

        return new self($message);
    }

    /**
     * Handle missingFinalNewline functionality with proper error handling.
     */
    public static function missingFinalNewline(string $file): self
    {
        $message = sprintf('Missing final newline in %s', $file);

        return new self($message);
    }

    /**
     * Handle inconsistentIndentation functionality with proper error handling.
     */
    public static function inconsistentIndentation(string $file, int $line): self
    {
        $message = sprintf('Inconsistent indentation in %s at line %d', $file, $line);

        return new self($message);
    }

    /**
     * Handle invalidNumericFormatting functionality with proper error handling.
     */
    public static function invalidNumericFormatting(string $file, int $line, string $value): self
    {
        $message = sprintf('Invalid numeric formatting in %s at line %d: %s', $file, $line, $value);

        return new self($message);
    }
}
