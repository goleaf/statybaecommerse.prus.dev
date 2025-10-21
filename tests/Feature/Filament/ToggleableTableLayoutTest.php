<?php

declare(strict_types=1);

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Tables\Columns\GridLayoutColumn;
use App\Filament\Tables\Concerns\ConfiguresToggleableTableLayout;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

final class ToggleableTableTestModel extends Model
{
    protected $table = 'toggleable_table_test_models';

    protected $guarded = [];
}

final class ToggleableTableTestResource extends Resource
{
    protected static ?string $model = ToggleableTableTestModel::class;

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
            TextColumn::make('status'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function getPages(): array
    {
        return [
            'index' => ToggleableTableTestPage::route('/'),
        ];
    }

    /**
     * @param array<mixed> $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return '#';
    }
}

final class ToggleableTableTestPage extends BaseListRecords
{
    protected static string $resource = ToggleableTableTestResource::class;
}

RefreshDatabaseState::$migrated = true;

beforeEach(function (): void {
    Schema::dropIfExists('toggleable_table_test_models');
    Schema::dropIfExists('cart_items');

    Schema::create('toggleable_table_test_models', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('status')->nullable();
        $table->timestamps();
    });

    Schema::create('cart_items', function (Blueprint $table): void {
        $table->id();
        $table->string('session_id')->nullable();
        $table->foreignId('user_id')->nullable();
        $table->foreignId('product_id')->nullable();
        $table->foreignId('variant_id')->nullable();
        $table->foreignId('product_variant_id')->nullable();
        $table->unsignedInteger('quantity')->default(0);
        $table->unsignedInteger('minimum_quantity')->default(0);
        $table->decimal('unit_price', 10, 2)->nullable();
        $table->decimal('total_price', 10, 2)->nullable();
        $table->decimal('price', 10, 2)->nullable();
        $table->json('product_snapshot')->nullable();
        $table->json('attributes')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
});

describe('toggleable table layout integration', function (): void {
    it('boots a list page with the toggleable layout trait applied', function (): void {
        /** @var Panel $panel */
        $panel = Filament::getPanel('admin');

        Filament::setCurrentPanel($panel);
        Filament::setServingStatus(true);

        $component = Livewire::test(ToggleableTableTestPage::class);

        $component->assertOk();

        /** @var ToggleableTableTestPage $page */
        $page = $component->instance();

        $traits = class_uses_recursive($page);

        expect(array_keys($traits))
            ->toContain(ConfiguresToggleableTableLayout::class)
            ->toContain(HasToggleableTable::class);

        $component->call('changeLayoutView');

        // Reboot the table to apply the new layout configuration, mirroring the
        // re-render cycle that happens in the browser after the toggle action
        // dispatches the `layoutViewChanged` event.
        $component->call('bootedInteractsWithTable');

        /** @var ToggleableTableTestPage $gridPage */
        $gridPage = $component->instance();

        /** @var Table $table */
        $table = $gridPage->getTable();

        /** @var array<string, \Filament\Tables\Columns\Column> $columns */
        $columns = $table->getColumns();

        $hasGridColumn = false;

        foreach ($columns as $column) {
            if ($column instanceof GridLayoutColumn) {
                $hasGridColumn = true;
                break;
            }
        }

        expect($hasGridColumn)->toBeTrue();

        /** @var array<string, int|null>|null $contentGrid */
        $contentGrid = $table->getContentGrid();

        expect($contentGrid)
            ->toMatchArray([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ]);
    });
});
