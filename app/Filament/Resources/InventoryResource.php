<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * InventoryResource
 *
 * Filament v4 resource for Inventory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    /** @var UnitEnum|string|null */    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'product.name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.inventory.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.inventory.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.inventory.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.inventory.form.sections.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('product_id')
                                ->label(__('admin.inventory.form.fields.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(1),
                            Select::make('location_id')
                                ->label(__('admin.inventory.form.fields.location'))
                                ->relationship('location', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(1),
                        ]),
                    Grid::make(3)
                        ->components([
                            TextInput::make('quantity')
                                ->label(__('admin.inventory.form.fields.quantity'))
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->columnSpan(1),
                            TextInput::make('reserved')
                                ->label(__('admin.inventory.form.fields.reserved'))
                                ->numeric()
                                ->default(0)
                                ->columnSpan(1),
                            TextInput::make('incoming')
                                ->label(__('admin.inventory.form.fields.incoming'))
                                ->numeric()
                                ->default(0)
                                ->columnSpan(1),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('threshold')
                                ->label(__('admin.inventory.form.fields.threshold'))
                                ->numeric()
                                ->default(10)
                                ->columnSpan(1),
                            Toggle::make('is_tracked')
                                ->label(__('admin.inventory.form.fields.is_tracked'))
                                ->default(true)
                                ->columnSpan(1),
                        ]),
                ])
                ->columns(1),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('admin.inventory.form.fields.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.name')
                    ->label(__('admin.inventory.form.fields.location'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('admin.inventory.form.fields.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved')
                    ->label(__('admin.inventory.form.fields.reserved'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('incoming')
                    ->label(__('admin.inventory.form.fields.incoming'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('threshold')
                    ->label(__('admin.inventory.form.fields.threshold'))
                    ->numeric()
                    ->sortable(),
                BadgeColumn::make('stock_status')
                    ->label(__('admin.inventory.form.fields.stock_status'))
                    ->getStateUsing(function (Inventory $record): string {
                        $available = $record->quantity - $record->reserved;
                        if ($available <= 0) return 'out_of_stock';
                        if ($available <= $record->threshold) return 'low_stock';
                        return 'in_stock';
                    })
                    ->colors([
                        'success' => 'in_stock',
                        'warning' => 'low_stock',
                        'danger' => 'out_of_stock',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in_stock' => __('admin.inventory.stock_status.in_stock'),
                        'low_stock' => __('admin.inventory.stock_status.low_stock'),
                        'out_of_stock' => __('admin.inventory.stock_status.out_of_stock'),
                        default => $state,
                    }),
                IconColumn::make('is_tracked')
                    ->label(__('admin.inventory.form.fields.is_tracked'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('admin.inventory.form.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label(__('admin.inventory.form.fields.product'))
                    ->relationship('product', 'name'),
                SelectFilter::make('location')
                    ->label(__('admin.inventory.form.fields.location'))
                    ->relationship('location', 'name'),
                TernaryFilter::make('is_tracked')
                    ->label(__('admin.inventory.form.fields.is_tracked')),
                SelectFilter::make('stock_status')
                    ->label(__('admin.inventory.form.fields.stock_status'))
                    ->options([
                        'in_stock' => __('admin.inventory.stock_status.in_stock'),
                        'low_stock' => __('admin.inventory.stock_status.low_stock'),
                        'out_of_stock' => __('admin.inventory.stock_status.out_of_stock'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $value): Builder {
                                $threshold = match ($value) {
                                    'out_of_stock' => function ($query) {
                                        return $query->whereRaw('quantity - reserved <= 0');
                                    },
                                    'low_stock' => function ($query) {
                                        return $query->whereRaw('quantity - reserved <= threshold AND quantity - reserved > 0');
                                    },
                                    'in_stock' => function ($query) {
                                        return $query->whereRaw('quantity - reserved > threshold');
                                    },
                                };
                                return $threshold($query);
                            }
                        );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                \Filament\Actions\Action::make('adjust_stock')
                    ->label(__('admin.inventory.actions.adjust_stock'))
                    ->icon('heroicon-o-plus-minus')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('admin.inventory.form.fields.quantity'))
                            ->numeric()
                            ->required(),
                        TextInput::make('reserved')
                            ->label(__('admin.inventory.form.fields.reserved'))
                            ->numeric(),
                        TextInput::make('incoming')
                            ->label(__('admin.inventory.form.fields.incoming'))
                            ->numeric(),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->update($data);

                        FilamentNotification::make()
                            ->title(__('admin.inventory.stock_adjusted_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('adjust_stock')
                        ->label(__('admin.inventory.actions.adjust_stock'))
                        ->icon('heroicon-o-plus-minus')
                        ->form([
                            TextInput::make('quantity')
                                ->label(__('admin.inventory.form.fields.quantity'))
                                ->numeric()
                                ->required(),
                            TextInput::make('reserved')
                                ->label(__('admin.inventory.form.fields.reserved'))
                                ->numeric(),
                            TextInput::make('incoming')
                                ->label(__('admin.inventory.form.fields.incoming'))
                                ->numeric(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Inventory $record) use ($data): void {
                                $record->update($data);
                            });

                            FilamentNotification::make()
                                ->title(__('admin.inventory.stock_adjusted_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view' => Pages\ViewInventory::route('/{record}'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
