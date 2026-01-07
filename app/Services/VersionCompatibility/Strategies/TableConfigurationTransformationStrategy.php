<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Strategies;

use App\Services\VersionCompatibility\TransformationResult;

/**
 * Transforms Filament v4 table configuration to v3.3 format
 */
final class TableConfigurationTransformationStrategy extends AbstractTransformationStrategy
{
    public function getName(): string
    {
        return 'Table Configuration Transformation';
    }

    public function canHandle(string $content): bool
    {
        return str_contains($content, 'Filament\Actions\Action') ||
               str_contains($content, '->modalSubmitActionLabel(');
    }

    public function transform(string $content): TransformationResult
    {
        $originalContent = $content;

        // Apply table configuration transformations
        $content = $this->applyReplacements($content, $this->getTableReplacements());

        return $this->createResult($originalContent, $content);
    }

    /**
     * Get string replacements for table transformations
     */
    private function getTableReplacements(): array
    {
        return [
            'use Filament\Actions\Action;'       => 'use Filament\Tables\Actions\Action;',
            'use Filament\Actions\ViewAction;'   => 'use Filament\Tables\Actions\ViewAction;',
            'use Filament\Actions\EditAction;'   => 'use Filament\Tables\Actions\EditAction;',
            'use Filament\Actions\DeleteAction;' => 'use Filament\Tables\Actions\DeleteAction;',
            '->modalSubmitActionLabel('          => '->modalCancelActionLabel(',
        ];
    }
}
