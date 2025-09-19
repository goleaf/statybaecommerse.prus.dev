<?php

/** Comprehensive script to fix all Filament v4 syntax errors */
echo "Starting comprehensive syntax error fixing...\n";

// Get all PHP files in Filament Resources
$filamentDir = 'app/Filament/Resources';
$files = [];

function getFilesRecursively($dir, &$files)
{
    if (is_dir($dir)) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                $path = $dir . '/' . $item;
                if (is_dir($path)) {
                    getFilesRecursively($path, $files);
                } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                    $files[] = $path;
                }
            }
        }
    }
}

getFilesRecursively($filamentDir, $files);

echo 'Found ' . count($files) . " PHP files to check...\n";

$fixedCount = 0;
$errorCount = 0;

foreach ($files as $file) {
    echo "Processing $file...\n";

    // Check syntax first
    $output = [];
    $returnCode = 0;
    exec("php -l \"$file\" 2>&1", $output, $returnCode);

    if ($returnCode !== 0) {
        echo "  Syntax errors found, fixing...\n";
        $errorCount++;

        $content = file_get_contents($file);
        $originalContent = $content;

        // Fix common syntax errors

        // 1. Fix navigation group type declarations
        $content = preg_replace(
            '/protected static string\s*\|\s*UnitEnum\s*\|\s*null\s+\$navigationGroup/',
            'protected static $navigationGroup',
            $content
        );

        // 2. Fix missing method signatures
        $content = preg_replace(
            '/public static function form\([^)]*\)\s*:\s*[^{]*\{/',
            'public static function form(Schema $schema): Schema' . "\n    {",
            $content
        );

        $content = preg_replace(
            '/public static function table\([^)]*\)\s*:\s*[^{]*\{/',
            'public static function table(Table $table): Table' . "\n    {",
            $content
        );

        // 3. Fix missing imports
        if (strpos($content, 'use Filament\Schemas\Schema;') === false && strpos($content, 'public static function form(') !== false) {
            $content = str_replace(
                'use Filament\Tables\Table;',
                "use Filament\Tables\Table;\nuse Filament\Schemas\Schema;",
                $content
            );
        }

        // 4. Fix malformed method bodies
        $content = preg_replace(
            '/public static function getNavigationGroup\(\):\s*\?string\s*\{\s*return\s*"[^"]*"->value;\s*\}/',
            'public static function getNavigationGroup(): ?string' . "\n    {\n        return 'System';\n    }",
            $content
        );

        // 5. Fix missing closing braces and brackets
        $content = preg_replace('/->required\(\),\s*->maxLength/', '->required()' . "\n                    ->maxLength", $content);
        $content = preg_replace('/->searchable\(\),\s*->sortable\(\),/', '->searchable()' . "\n                    ->sortable(),", $content);
        $content = preg_replace('/->sortable\(\),\s*->badge\(\),\s*->color/', '->sortable()' . "\n                    ->badge()" . "\n                    ->color", $content);
        $content = preg_replace('/->dateTime\(\),\s*->toggleable/', '->dateTime()' . "\n                    ->toggleable", $content);

        // 6. Fix double opening braces
        $content = preg_replace('/public function table\(Table \$table\): Table\s*\{\s*\{/', 'public function table(Table $table): Table' . "\n    {", $content);

        // 7. Fix unterminated comments
        $content = preg_replace('/\/\*[^*]*\*\/\s*$/', '', $content);

        // 8. Fix missing method signatures
        $content = preg_replace('/\*\s*Handle getNavigationGroup/', '    /**' . "\n     * Handle getNavigationGroup", $content);
        $content = preg_replace('/\*\s*Handle getPluralModelLabel/', '    /**' . "\n     * Handle getPluralModelLabel", $content);
        $content = preg_replace('/\*\s*Handle getModelLabel/', '    /**' . "\n     * Handle getModelLabel", $content);

        // 9. Fix missing return statements
        $content = preg_replace('/public static function getNavigationGroup\(\):\s*\?string\s*\{\s*return\s*"[^"]*";\s*\}/', 'public static function getNavigationGroup(): ?string' . "\n    {\n        return 'System';\n    }", $content);

        // 10. Fix missing closing brackets in arrays
        $content = preg_replace('/\]\s*->actions\(\[/', "]\n            ->actions([", $content);
        $content = preg_replace('/\]\s*->bulkActions\(\[/', "]\n            ->bulkActions([", $content);
        $content = preg_replace('/\]\s*->filters\(\[/', "]\n            ->filters([", $content);

        // 11. Fix missing semicolons and brackets
        $content = preg_replace('/->sortable\(\)\s*$/', '->sortable();', $content);
        $content = preg_replace('/->boolean\(\)\s*$/', '->boolean();', $content);
        $content = preg_replace('/->numeric\(\)\s*$/', '->numeric();', $content);

        // 12. Fix missing closing braces for methods
        $content = preg_replace("/->defaultSort\('[^']*'\);\s*\$/", "->defaultSort('sort_order');" . "\n    }", $content);

        // 13. Fix missing method bodies
        $content = preg_replace('/public static function getRelations\(\): array\s*\{\s*return \[\s*\/\/\s*\];\s*\}/', 'public static function getRelations(): array' . "\n    {\n        return [\n            //\n        ];\n    }", $content);

        // 14. Fix missing pages method
        $content = preg_replace("/public static function getPages\(\): array\s*\{\s*return \[\s*'index'\s*=>\s*Pages\\\\[^,]*,\s*'create'\s*=>\s*Pages\\\\[^,]*,\s*'view'\s*=>\s*Pages\\\\[^,]*,\s*'edit'\s*=>\s*Pages\\\\[^,]*,\s*\];\s*\}/", 'public static function getPages(): array' . "\n    {\n        return [\n            'index' => Pages\ListRecords::route('/'),\n            'create' => Pages\CreateRecord::route('/create'),\n            'view' => Pages\ViewRecord::route('/{record}'),\n            'edit' => Pages\EditRecord::route('/{record}/edit'),\n        ];\n    }", $content);

        // Write the fixed content back
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "  Fixed $file\n";
            $fixedCount++;
        }

        // Check syntax again
        $output = [];
        $returnCode = 0;
        exec("php -l \"$file\" 2>&1", $output, $returnCode);

        if ($returnCode === 0) {
            echo "  ✓ Syntax fixed successfully\n";
        } else {
            echo "  ✗ Still has syntax errors:\n";
            foreach ($output as $line) {
                echo "    $line\n";
            }
        }
    } else {
        echo "  ✓ No syntax errors\n";
    }
}

echo "\nSummary:\n";
echo 'Files processed: ' . count($files) . "\n";
echo "Files with errors: $errorCount\n";
echo "Files fixed: $fixedCount\n";
echo "Done!\n";
