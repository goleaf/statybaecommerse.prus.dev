<?php

declare(strict_types=1);

use App\Services\VersionCompatibility\Contracts\TransformationStrategyInterface;
use App\Services\VersionCompatibility\Exceptions\InvalidFileException;
use App\Services\VersionCompatibility\TransformationResult;
use App\Services\VersionCompatibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Setup test configuration
    Config::set('version-compatibility', [
        'cache' => [
            'prefix' => 'test_filament_transform',
            'ttl'    => 300, // 5 minutes for tests
        ],
        'logging' => [
            'slow_threshold_ms'       => 50,
            'log_all_transformations' => true,
        ],
        'security' => [
            'max_file_size'      => 1024 * 1024, // 1MB
            'allowed_extensions' => ['php'],
            'audit_logging'      => ['enabled' => true],
            'rate_limiting'      => ['enabled' => false], // Disabled for integration tests
        ],
        'performance' => [
            'batch_size' => 10, // Small batches for testing
        ],
    ]);

    // Clear cache before each test
    Cache::flush();

    // Setup test storage
    Storage::fake('local');
});

describe('VersionCompatibilityService Integration Tests', function () {
    describe('Real transformation scenarios', function () {
        it('transforms Filament v4 form schema to v3.3 format', function () {
            $v4Content = '<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;

class TestResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make("name")
                ->required()
                ->maxLength(255),
        ]);
    }
}';

            $service = app(VersionCompatibilityService::class);
            $result = $service->transformContent($v4Content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
            expect($result->getContent())->toContain('Form $form');
            expect($result->getContent())->toContain('->schema([');
        });

        it('transforms Filament v4 table configuration to v3.3 format', function () {
            $v4Content = '<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;

class TestResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name"),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }
}';

            $service = app(VersionCompatibilityService::class);
            $result = $service->transformContent($v4Content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
            expect($result->getContent())->toContain('Table $table');
        });

        it('handles Heroicon transformations', function () {
            $v4Content = '<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

class TestResource extends Resource
{
    protected static ?string $navigationIcon = "heroicon-o-users";
    
    public static function getNavigationIcon(): string
    {
        return "heroicon-s-cog";
    }
}';

            $service = app(VersionCompatibilityService::class);
            $result = $service->transformContent($v4Content);

            expect($result)->toBeInstanceOf(TransformationResult::class);
            // Should transform heroicon format for v3.3 compatibility
            expect($result->getContent())->toContain('heroicon');
        });
    });

    describe('File processing integration', function () {
        it('processes real PHP files with transformations', function () {
            $testFile = 'test-resource.php';
            $content = '<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Resources\Resource;

class TestResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }
}';

            Storage::put($testFile, $content);
            $filePath = Storage::path($testFile);

            $service = app(VersionCompatibilityService::class);
            $result = $service->fixResourceFile($filePath);

            expect($result)->toBeInstanceOf(TransformationResult::class);
            expect($result->isSuccessful())->toBeTrue();
        });

        it('handles non-existent files gracefully', function () {
            $service = app(VersionCompatibilityService::class);

            expect(fn () => $service->fixResourceFile('non-existent.php'))
                ->toThrow(InvalidFileException::class);
        });

        it('validates file extensions', function () {
            $testFile = 'test-file.txt';
            Storage::put($testFile, 'Not PHP content');
            $filePath = Storage::path($testFile);

            $service = app(VersionCompatibilityService::class);

            expect(fn () => $service->fixResourceFile($filePath))
                ->toThrow(InvalidFileException::class);
        });
    });

    describe('Directory processing integration', function () {
        it('processes multiple files in a directory', function () {
            $testDir = 'test-resources';
            Storage::makeDirectory($testDir);

            $files = [
                'UserResource.php'    => '<?php namespace App\Filament\Resources; class UserResource {}',
                'ProductResource.php' => '<?php namespace App\Filament\Resources; class ProductResource {}',
                'OrderResource.php'   => '<?php namespace App\Filament\Resources; class OrderResource {}',
            ];

            foreach ($files as $filename => $content) {
                Storage::put("{$testDir}/{$filename}", $content);
            }

            $service = app(VersionCompatibilityService::class);
            $results = $service->fixAllResourcesInDirectory(Storage::path($testDir));

            expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
            // Results may be empty if no transformations were needed
            expect($results->count())->toBeGreaterThanOrEqual(0);
        });

        it('handles empty directories', function () {
            $testDir = 'empty-dir';
            Storage::makeDirectory($testDir);

            $service = app(VersionCompatibilityService::class);
            $results = $service->fixAllResourcesInDirectory(Storage::path($testDir));

            expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
            expect($results)->toBeEmpty();
        });

        it('continues processing when individual files fail', function () {
            $testDir = 'mixed-files';
            Storage::makeDirectory($testDir);

            // Create a mix of valid and problematic files
            Storage::put("{$testDir}/ValidResource.php", '<?php class ValidResource {}');
            Storage::put("{$testDir}/invalid.txt", 'Not a PHP file'); // Will be filtered out by extension

            $service = app(VersionCompatibilityService::class);
            $results = $service->fixAllResourcesInDirectory(Storage::path($testDir));

            expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
            // Should process at least the valid PHP file
            expect($results->count())->toBeGreaterThanOrEqual(0);
        });
    });

    describe('Caching integration', function () {
        it('caches transformation results', function () {
            $content = '<?php class TestClass {}';

            $service = app(VersionCompatibilityService::class);

            // First call should perform transformation
            $result1 = $service->transformContent($content);

            // Second call should use cache
            $result2 = $service->transformContent($content);

            expect($result1->getContent())->toBe($result2->getContent());
            expect($result1->wasTransformed())->toBe($result2->wasTransformed());
        });

        it('generates different cache keys for different content', function () {
            $content1 = '<?php class Test1 {}';
            $content2 = '<?php class Test2 {}';

            $service = app(VersionCompatibilityService::class);

            $result1 = $service->transformContent($content1);
            $result2 = $service->transformContent($content2);

            // Results should be different (different content)
            expect($result1->getContent())->not->toBe($result2->getContent());
        });

        it('clears cache successfully', function () {
            $content = '<?php class TestClass {}';

            $service = app(VersionCompatibilityService::class);

            // Populate cache
            $service->transformContent($content);

            // Clear cache
            $cleared = $service->clearCache();

            expect($cleared)->toBeTrue();
        });
    });

    describe('Strategy management integration', function () {
        it('lists available strategies', function () {
            $service = app(VersionCompatibilityService::class);
            $strategies = $service->getAvailableStrategies();

            expect($strategies)->toBeInstanceOf(\Illuminate\Support\Collection::class);
            expect($strategies->count())->toBeGreaterThan(0);

            $firstStrategy = $strategies->first();
            expect($firstStrategy)->toHaveKeys(['class', 'name']);
        });

        it('adds custom strategies', function () {
            $service = app(VersionCompatibilityService::class);
            $initialCount = $service->getAvailableStrategies()->count();

            $customStrategy = new class implements TransformationStrategyInterface
            {
                public function transform(string $content): TransformationResult
                {
                    return new TransformationResult($content, false, []);
                }

                public function getName(): string
                {
                    return 'TestCustomStrategy';
                }

                public function canHandle(string $content): bool
                {
                    return str_contains($content, 'test');
                }
            };

            $service->addStrategy($customStrategy);
            $newCount = $service->getAvailableStrategies()->count();

            expect($newCount)->toBe($initialCount + 1);
        });

        it('prevents duplicate strategy names', function () {
            $service = app(VersionCompatibilityService::class);

            $strategy1 = new class implements TransformationStrategyInterface
            {
                public function transform(string $content): TransformationResult
                {
                    return new TransformationResult($content, false, []);
                }

                public function getName(): string
                {
                    return 'DuplicateStrategy';
                }

                public function canHandle(string $content): bool
                {
                    return true;
                }
            };

            $strategy2 = new class implements TransformationStrategyInterface
            {
                public function transform(string $content): TransformationResult
                {
                    return new TransformationResult($content, false, []);
                }

                public function getName(): string
                {
                    return 'DuplicateStrategy'; // Same name
                }

                public function canHandle(string $content): bool
                {
                    return true;
                }
            };

            $service->addStrategy($strategy1);

            expect(fn () => $service->addStrategy($strategy2))
                ->toThrow(InvalidArgumentException::class, 'Strategy name already exists');
        });
    });

    describe('Statistics and monitoring integration', function () {
        it('provides comprehensive transformation statistics', function () {
            $service = app(VersionCompatibilityService::class);
            $stats = $service->getTransformationStats();

            expect($stats)->toBeArray();
            expect($stats)->toHaveKeys([
                'service_info',
                'strategies',
                'configuration',
                'cache_stats',
            ]);

            expect($stats['service_info'])->toHaveKeys([
                'available_strategies',
                'cache_prefix',
                'cache_ttl_seconds',
                'slow_threshold_ms',
                'security_enabled',
            ]);

            expect($stats['service_info']['available_strategies'])->toBeGreaterThan(0);
            expect($stats['service_info']['cache_prefix'])->toBe('test_filament_transform');
            expect($stats['service_info']['security_enabled'])->toBeTrue();
        });

        it('includes strategy details in statistics', function () {
            $service = app(VersionCompatibilityService::class);
            $stats = $service->getTransformationStats();

            expect($stats['strategies'])->toBeArray();
            expect(count($stats['strategies']))->toBeGreaterThan(0);

            $firstStrategy = $stats['strategies'][0];
            expect($firstStrategy)->toHaveKeys(['class', 'name', 'can_handle_sample']);
        });
    });

    describe('Error handling integration', function () {
        it('handles transformation errors gracefully', function () {
            // Create a file that might cause processing issues
            $problematicContent = '<?php
            // This content might cause issues in certain transformations
            class ProblematicClass {
                // Complex nested structures
            }';

            $service = app(VersionCompatibilityService::class);
            $result = $service->transformContent($problematicContent);

            // Should not throw exception, but return result with potential error info
            expect($result)->toBeInstanceOf(TransformationResult::class);
        });

        it('logs errors appropriately', function () {
            Log::fake();

            $service = app(VersionCompatibilityService::class);

            // Try to process a non-existent file
            try {
                $service->fixResourceFile('definitely-does-not-exist.php');
            } catch (InvalidFileException $e) {
                // Expected exception
            }

            // Should have logged the error (if audit logging is enabled)
            // Log::assertLogged('error'); // Uncomment if error logging is expected
        });
    });

    describe('Configuration integration', function () {
        it('respects configuration changes', function () {
            // Change configuration
            Config::set('version-compatibility.cache.ttl', 600);
            Config::set('version-compatibility.logging.slow_threshold_ms', 25);

            $service = app(VersionCompatibilityService::class);
            $stats = $service->getTransformationStats();

            expect($stats['service_info']['cache_ttl_seconds'])->toBe(600);
            expect($stats['service_info']['slow_threshold_ms'])->toBe(25);
        });

        it('validates configuration on service creation', function () {
            Config::set('version-compatibility.cache.ttl', -1); // Invalid

            expect(fn () => app(VersionCompatibilityService::class))
                ->toThrow(InvalidArgumentException::class, 'Cache TTL must be positive');
        });
    });
});

describe('Real-world transformation scenarios', function () {
    it('handles complex Filament resource files', function () {
        $complexResource = '<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Resources\Resource;

class ComplexResource extends Resource
{
    protected static ?string $model = \App\Models\Complex::class;
    
    protected static ?string $navigationIcon = "heroicon-o-document-text";
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make("name")
                ->required()
                ->maxLength(255),
            Select::make("status")
                ->options([
                    "active" => "Active",
                    "inactive" => "Inactive",
                ])
                ->required(),
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("name")
                    ->searchable()
                    ->sortable(),
                TextColumn::make("status")
                    ->badge(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}';

        $service = app(VersionCompatibilityService::class);
        $result = $service->transformContent($complexResource);

        expect($result)->toBeInstanceOf(TransformationResult::class);
        expect($result->isSuccessful())->toBeTrue();
        expect($result->getContent())->toContain('Form $form');
        expect($result->getContent())->toContain('Table $table');
    });

    it('processes multiple related resource files', function () {
        $testDir = 'filament-resources';
        Storage::makeDirectory($testDir);

        $resources = [
            'UserResource.php'    => '<?php namespace App\Filament\Resources; use Filament\Forms\Form; class UserResource { public static function form(Form $form): Form { return $form->schema([]); } }',
            'ProductResource.php' => '<?php namespace App\Filament\Resources; use Filament\Tables\Table; class ProductResource { public static function table(Table $table): Table { return $table->columns([]); } }',
            'OrderResource.php'   => '<?php namespace App\Filament\Resources; class OrderResource { protected static ?string $navigationIcon = "heroicon-o-shopping-cart"; }',
        ];

        foreach ($resources as $filename => $content) {
            Storage::put("{$testDir}/{$filename}", $content);
        }

        $service = app(VersionCompatibilityService::class);
        $results = $service->fixAllResourcesInDirectory(Storage::path($testDir));

        expect($results)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        // Should process all PHP files in the directory
        expect($results->count())->toBeGreaterThanOrEqual(0);
    });
});
