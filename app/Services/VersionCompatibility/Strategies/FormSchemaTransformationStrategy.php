<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Strategies;

use App\Services\VersionCompatibility\TransformationResult;

/**
 * Transforms Filament v4 form schema to v3.3 format
 */
final class FormSchemaTransformationStrategy extends AbstractTransformationStrategy
{
    public function getName(): string
    {
        return 'Form Schema Transformation';
    }

    public function canHandle(string $content): bool
    {
        return str_contains($content, 'Filament\Schemas\Schema') ||
               str_contains($content, 'function form(') ||
               str_contains($content, '$schema->schema(');
    }

    public function transform(string $content): TransformationResult
    {
        $originalContent = $content;

        // Apply form schema transformations
        $content = $this->applyReplacements($content, $this->getFormReplacements());
        $content = $this->applyRegexReplacements($content, $this->getFormRegexPatterns());
        $content = $this->addMissingImports($content);

        return $this->createResult($originalContent, $content);
    }

    /**
     * Get string replacements for form transformations
     */
    private function getFormReplacements(): array
    {
        return [
            'use Filament\Schemas\Schema;' => 'use Filament\Forms\Form;',
            'return $schema->schema(['     => 'return $form->schema([',
        ];
    }

    /**
     * Get regex patterns for form transformations
     */
    private function getFormRegexPatterns(): array
    {
        return [
            '/public static function form\(Form \$form\): Form/' => 'public static function form(Form $form): Form',
        ];
    }

    /**
     * Add missing Form import if needed
     */
    private function addMissingImports(string $content): string
    {
        if (str_contains($content, 'Form $form') && ! str_contains($content, 'use Filament\Forms\Form;')) {
            $content = str_replace(
                '<?php',
                "<?php\n\nuse Filament\Forms\Form;",
                $content
            );
        }

        return $content;
    }
}
