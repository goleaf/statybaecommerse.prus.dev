<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CodeStyleException extends Exception
{
    public function __construct(string $message = 'Code style violation detected', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function invalidImportOrder(string $file, array $expectedOrder, array $actualOrder): self
    {
        $message = sprintf(
            'Invalid import order in %s. Expected: %s, Actual: %s',
            $file,
            implode(', ', $expectedOrder),
            implode(', ', $actualOrder)
        );

        return new self($message);
    }

    public static function missingSpaceInUnionTypes(string $file, int $line): self
    {
        $message = sprintf(
            'Missing space in union type declaration in %s at line %d',
            $file,
            $line
        );

        return new self($message);
    }

    public static function invalidClosureSpacing(string $file, int $line): self
    {
        $message = sprintf(
            'Invalid closure spacing in %s at line %d',
            $file,
            $line
        );

        return new self($message);
    }

    public static function trailingWhitespace(string $file, int $line): self
    {
        $message = sprintf(
            'Trailing whitespace detected in %s at line %d',
            $file,
            $line
        );

        return new self($message);
    }

    public static function missingFinalNewline(string $file): self
    {
        $message = sprintf(
            'Missing final newline in %s',
            $file
        );

        return new self($message);
    }

    public static function inconsistentIndentation(string $file, int $line): self
    {
        $message = sprintf(
            'Inconsistent indentation in %s at line %d',
            $file,
            $line
        );

        return new self($message);
    }

    public static function invalidNumericFormatting(string $file, int $line, string $value): self
    {
        $message = sprintf(
            'Invalid numeric formatting in %s at line %d: %s',
            $file,
            $line,
            $value
        );

        return new self($message);
    }
}
