<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixFilamentV4Command extends Command
{
    protected $signature = 'filament:fix-v4 {--dry-run : Show what would be changed without making changes}';
    protected $description = 'Update all Filament resources to v4 syntax';

    private array $changes = [];
    private array $errors = [];

    public function handle(): int
    {
        $this->info('🚀 Starting Filament v4 migration...');

        $this->updateResources();
        $this->updatePages();
        $this->updateWidgets();

        $this->displayResults();

        return Command::SUCCESS;
    }

    private function updateResources(): void
    {
        $this->info('📁 Updating Resources...');
        
        $resourcePath = app_path('Filament/Resources');
        $files = File::allFiles($resourcePath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && !str_contains($file->getPathname(), '/Pages/')) {
                $this->updateFile($file->getPathname(), 'Resource');
            }
        }
    }

    private function updatePages(): void
    {
        $this->info('📄 Updating Pages...');
        
        $pagesPath = app_path('Filament');
        $files = File::allFiles($pagesPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && str_contains($file->getPathname(), '/Pages/')) {
                $this->updateFile($file->getPathname(), 'Page');
            }
        }
    }

    private function updateWidgets(): void
    {
        $this->info('🎛️ Updating Widgets...');
        
        $widgetsPath = app_path('Filament/Widgets');
        if (File::exists($widgetsPath)) {
            $files = File::allFiles($widgetsPath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $this->updateFile($file->getPathname(), 'Widget');
                }
            }
        }
    }

    private function updateFile(string $filePath, string $type): void
    {
        $content = File::get($filePath);
        $originalContent = $content;
        
        // Skip if already updated
        if (str_contains($content, 'use Filament\Schemas\Schema;')) {
            return;
        }

        // Update imports
        $content = $this->updateImports($content);
        
        // Update method signatures
        $content = $this->updateMethodSignatures($content);
        
        // Update deprecated components
        $content = $this->updateDeprecatedComponents($content);
        
        // Update navigation properties
        $content = $this->updateNavigationProperties($content);

        if ($content !== $originalContent) {
            if (!$this->option('dry-run')) {
                File::put($filePath, $content);
            }
            $this->changes[] = "✅ Updated {$type}: " . basename($filePath);
        }
    }

    private function updateImports(string $content): string
    {
        // Replace Forms\Form with Schemas\Schema
        $content = str_replace(
            'use Filament\\Forms\\Form;',
            'use Filament\\Schemas\\Schema;',
            $content
        );

        // Replace Infolists\Infolist with Schemas\Schema
        $content = str_replace(
            'use Filament\\Infolists\\Infolist;',
            'use Filament\\Schemas\\Schema;',
            $content
        );

        // Update Forms\Components to Schemas\Components
        $content = str_replace(
            'use Filament\\Forms\\Components\\',
            'use Filament\\Schemas\\Components\\',
            $content
        );

        // Update Infolists\Components to Schemas\Components
        $content = str_replace(
            'use Filament\\Infolists\\Components\\',
            'use Filament\\Schemas\\Components\\',
            $content
        );

        return $content;
    }

    private function updateMethodSignatures(string $content): string
    {
        // Update form method signature
        $content = str_replace(
            'public static function form(Form $form): Form',
            'public static function form(Schema $schema): Schema',
            $content
        );

        // Update infolist method signature
        $content = str_replace(
            'public static function infolist(Infolist $infolist): Infolist',
            'public static function infolist(Schema $schema): Schema',
            $content
        );

        // Update parameter names in method bodies
        $content = str_replace('$form->', '$schema->', $content);
        $content = str_replace('$infolist->', '$schema->', $content);

        return $content;
    }

    private function updateDeprecatedComponents(string $content): string
    {
        // Remove IconColumn and BadgeColumn imports
        $content = str_replace('use Filament\\Tables\\Columns\\IconColumn;', '', $content);
        $content = str_replace('use Filament\\Tables\\Columns\\BadgeColumn;', '', $content);

        return $content;
    }

    private function updateNavigationProperties(string $content): string
    {
        // Add UnitEnum import if needed
        if (str_contains($content, 'NavigationGroup') && !str_contains($content, 'use UnitEnum;')) {
            $lines = explode("\n", $content);
            $insertIndex = 0;
            foreach ($lines as $index => $line) {
                if (str_starts_with(trim($line), 'use ') && str_ends_with(trim($line), ';')) {
                    $insertIndex = $index + 1;
                }
            }
            array_splice($lines, $insertIndex, 0, ['use UnitEnum;']);
            $content = implode("\n", $lines);
        }

        // Update navigation property type hints
        $content = str_replace(
            'protected static ?NavigationGroup $navigationGroup',
            '/** @var UnitEnum|string|null */' . "\n    protected static \$navigationGroup",
            $content
        );

        return $content;
    }

    private function displayResults(): void
    {
        $this->line('');
        $this->line(str_repeat('=', 50));
        $this->info('📊 MIGRATION RESULTS');
        $this->line(str_repeat('=', 50));

        if (!empty($this->changes)) {
            $this->info('✅ SUCCESSFULLY UPDATED FILES:');
            foreach ($this->changes as $change) {
                $this->line("   {$change}");
            }
        }

        if (!empty($this->errors)) {
            $this->error('❌ ERRORS:');
            foreach ($this->errors as $error) {
                $this->line("   {$error}");
            }
        }

        $this->line('');
        $this->info('🎉 Migration completed!');
        $this->info('📝 Total files updated: ' . count($this->changes));
        
        if (empty($this->changes)) {
            $this->info('ℹ️  No files needed updating - all resources are already v4 compatible!');
        }
    }
}
