<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Strategies;

use App\Services\VersionCompatibility\TransformationResult;

/**
 * Transforms Filament v4 infolist schema to v3.3 format
 */
final class InfolistSchemaTransformationStrategy extends AbstractTransformationStrategy
{
    public function getName(): string
    {
        return 'Infolist Schema Transformation';
    }

    public function canHandle(string $content): bool
    {
        return str_contains($content, 'function infolist(') ||
               str_contains($content, 'Schema $schema') ||
               str_contains($content, '$schema->schema(');
    }

    public function transform(string $content): TransformationResult
    {
        $originalContent = $content;

        // Apply infolist transformations
        $content = $this->applyRegexReplacements($content, $this->getInfolistRegexPatterns());
        $content = $this->applyReplacements($content, $this->getInfolistReplacements());
        $content = $this->addInfolistImports($content);

        return $this->createResult($originalContent, $content);
    }

    /**
     * Get regex patterns for infolist transformations
     */
    private function getInfolistRegexPatterns(): array
    {
        return [
            '/public static function infolist\(Schema \$schema\): Schema/' => 'public static function infolist(Infolist $infolist): Infolist',
        ];
    }

    /**
     * Get string replacements for infolist transformations
     */
    private function getInfolistReplacements(): array
    {
        return [
            'return $schema->schema([' => 'return $infolist->schema([',
        ];
    }

    /**
     * Add missing Infolist import if needed
     */
    private function addInfolistImports(string $content): string
    {
        if (str_contains($content, 'infolist(Infolist $infolist)') &&
            ! str_contains($content, 'use Filament\Infolists\Infolist;')) {

            $content = preg_replace(
                '/(use [^;]+;)(\s*\n\s*(?:final\s+)?class)/m',
                "$1\nuse Filament\Infolists\Infolist;$2",
                $content
            );
        }

        return $content;
    }
}
