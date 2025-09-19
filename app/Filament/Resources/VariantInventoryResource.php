<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\VariantInventoryResource\Pages;
use App\Models\ProductVariant;
use App\Models\Location;
use App\Models\VariantInventory;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;
/**
 * VariantInventoryResource
 *
 * Filament v4 resource for VariantInventory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantInventoryResource extends Resource
{
    protected static ?string $model = VariantInventory::class;    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Inventory;
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'variant_id';
    public static function getNavigationLabel(): string
    {
        return __('variant_inventory.title');
    }
    public static function getNavigationGroup(): ?string
        return NavigationGroup::Inventory->value;
    public static function getPluralModelLabel(): string
        return __('variant_inventory.plural');
    public static function getModelLabel(): string
        return __('variant_inventory.single');
    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
        return $schema->components([
            Section::make(__('variant_inventory.sections.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('variant_id')
                                ->label(__('variant_inventory.fields.variant_id'))
                                ->relationship('variant', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                            Select::make('location_id')
                                ->label(__('variant_inventory.fields.location_id'))
                                ->relationship('location', 'name')
                        ]),
                            TextInput::make('warehouse_code')
                                ->label(__('variant_inventory.fields.warehouse_code'))
                                ->maxLength(50)
                            TextInput::make('batch_number')
                                ->label(__('variant_inventory.fields.batch_number'))
                                ->maxLength(100)
                ])
                ->columns(1),
            Section::make(__('variant_inventory.sections.stock_levels'))
                    Grid::make(3)
                            TextInput::make('stock')
                                ->label(__('variant_inventory.fields.stock'))
                                ->numeric()
                                ->default(0)
                            TextInput::make('reserved')
                                ->label(__('variant_inventory.fields.reserved'))
                            TextInput::make('available')
                                ->label(__('variant_inventory.fields.available'))
                            TextInput::make('incoming')
                                ->label(__('variant_inventory.fields.incoming'))
                            TextInput::make('threshold')
                                ->label(__('variant_inventory.fields.threshold'))
                            TextInput::make('reorder_point')
                                ->label(__('variant_inventory.fields.reorder_point'))
            Section::make(__('variant_inventory.sections.pricing'))
                            TextInput::make('cost_per_unit')
                                ->label(__('variant_inventory.fields.cost_per_unit'))
                                ->step(0.01)
                                ->prefix('€')
                            DatePicker::make('expiry_date')
                                ->label(__('variant_inventory.fields.expiry_date'))
        ]);
     * Configure the Filament table with columns, filters, and actions.
    public static function table(Table $table): Table
        return $table
            ->columns([
                TextColumn::make('variant.name')
                    ->label(__('variant_inventory.fields.variant'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.name')
                    ->label(__('variant_inventory.fields.location'))
                TextColumn::make('warehouse_code')
                    ->label(__('variant_inventory.fields.warehouse_code'))
                    ->toggleable(),
                TextColumn::make('stock')
                    ->label(__('variant_inventory.fields.stock'))
                    ->numeric()
                TextColumn::make('reserved')
                    ->label(__('variant_inventory.fields.reserved'))
                TextColumn::make('available')
                    ->label(__('variant_inventory.fields.available'))
                TextColumn::make('threshold')
                    ->label(__('variant_inventory.fields.threshold'))
                TextColumn::make('cost_per_unit')
                    ->label(__('variant_inventory.fields.cost_per_unit'))
                    ->money('EUR')
                TextColumn::make('created_at')
                    ->label(__('variant_inventory.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('variant_id')
                    ->relationship('variant', 'name')
                    ->preload(),
                SelectFilter::make('location_id')
                    ->relationship('location', 'name')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ->defaultSort('created_at', 'desc');
    public static function getPages(): array
        return [
            'index' => Pages\ListVariantInventories::route('/'),
            'create' => Pages\CreateVariantInventory::route('/create'),
            'edit' => Pages\EditVariantInventory::route('/{record}/edit'),
        ];
}
