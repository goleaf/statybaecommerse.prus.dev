<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Number;

/**
 * CodeStyleService
 *
 * Service class containing CodeStyleService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class CodeStyleService
{
    private const IMPORT_ORDER_PATTERNS = ['use Illuminate\\', 'use Filament\\', 'use Spatie\\', 'use App\\'];

    private const UNION_TYPE_PATTERN = '/\|\s*([a-zA-Z\\\\]+)/';

    private const CLOSURE_PATTERN = '/fn\s*\(\s*([^)]*)\s*\)/';

    private const TRAILING_WHITESPACE_PATTERN = '/\s+$/m';

    private const NUMERIC_PATTERN = '/(\d+\.0+)(?![0-9])/';

    /**
     * Handle fixFile functionality with proper error handling.
     */
    public function fixFile(string $filePath): array
    {
        $fixes = [];
        if (! File::exists($filePath)) {
            return $fixes;
        }
        $content = File::get($filePath);
        $originalContent = $content;
        $lines = explode("\n", $content);
        // Fix import order
        $content = $this->fixImportOrder($content, $fixes, $filePath);
        // Fix union types spacing
        $content = $this->fixUnionTypeSpacing($content, $fixes, $filePath);
        // Fix closure spacing
        $content = $this->fixClosureSpacing($content, $fixes, $filePath);
        // Fix trailing whitespace
        $content = $this->fixTrailingWhitespace($content, $fixes, $filePath);
        // Fix numeric formatting
        $content = $this->fixNumericFormatting($content, $fixes, $filePath);
        // Fix final newline
        $content = $this->fixFinalNewline($content, $fixes, $filePath);
        // Fix inconsistent indentation
        $content = $this->fixIndentation($content, $fixes, $filePath);
        if ($content !== $originalContent) {
            File::put($filePath, $content);
            $fixes[] = ['type' => 'file_updated', 'file' => $filePath, 'message' => 'File updated with code style fixes'];
        }

        return $fixes;
    }

    /**
     * Handle fixDirectory functionality with proper error handling.
     */
    public function fixDirectory(string $directory, array $extensions = ['php']): array
    {
        $allFixes = [];
        $files = File::allFiles($directory);
        foreach ($files as $file) {
            $extension = $file->getExtension();
            if (in_array($extension, $extensions)) {
                $fixes = $this->fixFile($file->getPathname());
                $allFixes = array_merge($allFixes, $fixes);
            }
        }

        return $allFixes;
    }

    /**
     * Handle validateFile functionality with proper error handling.
     */
    public function validateFile(string $filePath): array
    {
        $violations = [];
        if (! File::exists($filePath)) {
            return $violations;
        }
        $content = File::get($filePath);
        $lines = explode("\n", $content);
        // Validate import order
        $this->validateImportOrder($content, $violations, $filePath);
        // Validate union types
        $this->validateUnionTypeSpacing($content, $violations, $filePath);
        // Validate closure spacing
        $this->validateClosureSpacing($content, $violations, $filePath);
        // Validate trailing whitespace
        $this->validateTrailingWhitespace($lines, $violations, $filePath);
        // Validate numeric formatting
        $this->validateNumericFormatting($content, $violations, $filePath);
        // Validate final newline
        $this->validateFinalNewline($content, $violations, $filePath);

        return $violations;
    }

    /**
     * Handle fixImportOrder functionality with proper error handling.
     */
    private function fixImportOrder(string $content, array &$fixes, string $filePath): string
    {
        $lines = explode("\n", $content);
        $useStatements = [];
        $otherLines = [];
        $inUseSection = false;
        foreach ($lines as $line) {
            if (preg_match('/^use\s+/', $line)) {
                $inUseSection = true;
                $useStatements[] = $line;
            } elseif ($inUseSection && trim($line) === '') {
                $useStatements[] = $line;
            } elseif ($inUseSection && ! preg_match('/^use\s+/', $line) && trim($line) !== '') {
                $inUseSection = false;
                $otherLines[] = $line;
            } else {
                $otherLines[] = $line;
            }
        }
        if (! empty($useStatements)) {
            $sortedUseStatements = $this->sortUseStatements($useStatements);
            if ($useStatements !== $sortedUseStatements) {
                $fixes[] = ['type' => 'import_order', 'file' => $filePath, 'message' => 'Fixed import order'];
            }
            $content = implode("\n", array_merge($sortedUseStatements, $otherLines));
        }

        return $content;
    }

    /**
     * Handle sortUseStatements functionality with proper error handling.
     */
    private function sortUseStatements(array $useStatements): array
    {
        $nonEmptyUseStatements = array_filter($useStatements, fn ($line) => trim($line) !== '');
        $emptyLines = array_filter($useStatements, fn ($line) => trim($line) === '');
        usort($nonEmptyUseStatements, function ($a, $b) {
            $aPattern = $this->getImportPattern($a);
            $bPattern = $this->getImportPattern($b);
            $aIndex = array_search($aPattern, self::IMPORT_ORDER_PATTERNS);
            $bIndex = array_search($bPattern, self::IMPORT_ORDER_PATTERNS);
            if ($aIndex === false) {
                $aIndex = 999;
            }
            if ($bIndex === false) {
                $bIndex = 999;
            }
            if ($aIndex === $bIndex) {
                return strcmp($a, $b);
            }

            return $aIndex - $bIndex;
        });
        // Add empty lines between different import groups
        $result = [];
        $lastPattern = null;
        foreach ($nonEmptyUseStatements as $statement) {
            $currentPattern = $this->getImportPattern($statement);
            if ($lastPattern !== null && $lastPattern !== $currentPattern) {
                $result[] = '';
            }
            $result[] = $statement;
            $lastPattern = $currentPattern;
        }

        return $result;
    }

    /**
     * Handle getImportPattern functionality with proper error handling.
     */
    private function getImportPattern(string $useStatement): string
    {
        foreach (self::IMPORT_ORDER_PATTERNS as $pattern) {
            if (str_starts_with($useStatement, "use {$pattern}")) {
                return $pattern;
            }
        }

        return 'other';
    }

    /**
     * Handle fixUnionTypeSpacing functionality with proper error handling.
     */
    private function fixUnionTypeSpacing(string $content, array &$fixes, string $filePath): string
    {
        $originalContent = $content;
        $content = preg_replace('/\|\s*([a-zA-Z\\\\]+)/', '|$1', $content);
        if ($content !== $originalContent) {
            $fixes[] = ['type' => 'union_type_spacing', 'file' => $filePath, 'message' => 'Fixed union type spacing'];
        }

        return $content;
    }

    /**
     * Handle fixClosureSpacing functionality with proper error handling.
     */
    private function fixClosureSpacing(string $content, array &$fixes, string $filePath): string
    {
        $originalContent = $content;
        $content = preg_replace('/fn\s*\(\s*([^)]*)\s*\)/', 'fn($1)', $content);
        if ($content !== $originalContent) {
            $fixes[] = ['type' => 'closure_spacing', 'file' => $filePath, 'message' => 'Fixed closure spacing'];
        }

        return $content;
    }

    /**
     * Handle fixTrailingWhitespace functionality with proper error handling.
     */
    private function fixTrailingWhitespace(string $content, array &$fixes, string $filePath): string
    {
        $originalContent = $content;
        $content = preg_replace(self::TRAILING_WHITESPACE_PATTERN, '', $content);
        if ($content !== $originalContent) {
            $fixes[] = ['type' => 'trailing_whitespace', 'file' => $filePath, 'message' => 'Removed trailing whitespace'];
        }

        return $content;
    }

    /**
     * Handle fixNumericFormatting functionality with proper error handling.
     */
    private function fixNumericFormatting(string $content, array &$fixes, string $filePath): string
    {
        $originalContent = $content;
        $content = preg_replace_callback(self::NUMERIC_PATTERN, function ($matches) {
            $number = Number::parseFloat($matches[1]);

            return $number == (int) $number ? (string) (int) $number : $matches[1];
        }, $content);
        if ($content !== $originalContent) {
            $fixes[] = ['type' => 'numeric_formatting', 'file' => $filePath, 'message' => 'Fixed numeric formatting'];
        }

        return $content;
    }

    /**
     * Handle fixFinalNewline functionality with proper error handling.
     */
    private function fixFinalNewline(string $content, array &$fixes, string $filePath): string
    {
        if (! str_ends_with($content, "\n")) {
            $content .= "\n";
            $fixes[] = ['type' => 'final_newline', 'file' => $filePath, 'message' => 'Added final newline'];
        }

        return $content;
    }

    /**
     * Handle fixIndentation functionality with proper error handling.
     */
    private function fixIndentation(string $content, array &$fixes, string $filePath): string
    {
        $lines = explode("\n", $content);
        $fixedLines = [];
        $hasIndentationIssues = false;
        foreach ($lines as $line) {
            if (preg_match('/^(\s*)/', $line, $matches)) {
                $indentation = $matches[1];
                $tabs = substr_count($indentation, "\t");
                $spaces = strlen($indentation) - $tabs;
                if ($spaces % 4 !== 0) {
                    $hasIndentationIssues = true;
                    $properSpaces = str_repeat(' ', floor($spaces / 4) * 4);
                    $properTabs = str_repeat("\t", $tabs);
                    $line = $properTabs.$properSpaces.ltrim($line);
                }
            }
            $fixedLines[] = $line;
        }
        if ($hasIndentationIssues) {
            $fixes[] = ['type' => 'indentation', 'file' => $filePath, 'message' => 'Fixed indentation'];
        }

        return implode("\n", $fixedLines);
    }

    /**
     * Handle validateImportOrder functionality with proper error handling.
     */
    private function validateImportOrder(string $content, array &$violations, string $filePath): void
    {
        $lines = explode("\n", $content);
        $useStatements = [];
        $inUseSection = false;
        foreach ($lines as $lineNumber => $line) {
            if (preg_match('/^use\s+/', $line)) {
                $inUseSection = true;
                $useStatements[] = ['line' => $lineNumber + 1, 'statement' => $line];
            } elseif ($inUseSection && trim($line) !== '' && ! preg_match('/^use\s+/', $line)) {
                break;
            }
        }
        if (count($useStatements) > 1) {
            $sortedStatements = $this->sortUseStatements(array_column($useStatements, 'statement'));
            $currentStatements = array_column($useStatements, 'statement');
            if ($currentStatements !== $sortedStatements) {
                $violations[] = ['type' => 'import_order', 'file' => $filePath, 'line' => $useStatements[0]['line'], 'message' => 'Import statements are not in correct order'];
            }
        }
    }

    /**
     * Handle validateUnionTypeSpacing functionality with proper error handling.
     */
    private function validateUnionTypeSpacing(string $content, array &$violations, string $filePath): void
    {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNumber => $line) {
            if (preg_match('/\|\s+([a-zA-Z\\\\]+)/', $line)) {
                $violations[] = ['type' => 'union_type_spacing', 'file' => $filePath, 'line' => $lineNumber + 1, 'message' => 'Union type should not have space after |'];
            }
        }
    }

    /**
     * Handle validateClosureSpacing functionality with proper error handling.
     */
    private function validateClosureSpacing(string $content, array &$violations, string $filePath): void
    {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNumber => $line) {
            if (preg_match('/fn\s+\(\s+([^)]*)\s+\)/', $line)) {
                $violations[] = ['type' => 'closure_spacing', 'file' => $filePath, 'line' => $lineNumber + 1, 'message' => 'Closure should not have spaces around parentheses'];
            }
        }
    }

    /**
     * Handle validateTrailingWhitespace functionality with proper error handling.
     */
    private function validateTrailingWhitespace(array $lines, array &$violations, string $filePath): void
    {
        foreach ($lines as $lineNumber => $line) {
            if (preg_match(self::TRAILING_WHITESPACE_PATTERN, $line)) {
                $violations[] = ['type' => 'trailing_whitespace', 'file' => $filePath, 'line' => $lineNumber + 1, 'message' => 'Line has trailing whitespace'];
            }
        }
    }

    /**
     * Handle validateNumericFormatting functionality with proper error handling.
     */
    private function validateNumericFormatting(string $content, array &$violations, string $filePath): void
    {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNumber => $line) {
            if (preg_match(self::NUMERIC_PATTERN, $line, $matches)) {
                $violations[] = ['type' => 'numeric_formatting', 'file' => $filePath, 'line' => $lineNumber + 1, 'message' => 'Numeric value should be formatted without unnecessary decimal places: '.$matches[1]];
            }
        }
    }

    /**
     * Handle validateFinalNewline functionality with proper error handling.
     */
    private function validateFinalNewline(string $content, array &$violations, string $filePath): void
    {
        if (! str_ends_with($content, "\n")) {
            $violations[] = ['type' => 'final_newline', 'file' => $filePath, 'line' => count(explode("\n", $content)), 'message' => 'File should end with a newline'];
        }
    }
}
