<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use App\Support\Filament\Components\Flatpickr;
use BackedEnum;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use LaraZeus\Quantity\Components\Quantity;
use UnitEnum;
use App\Support\Filament\Components\Flatpickr;
use Filament\Schemas\Schema;

/**
 * StockMovementResource
 *
 * Filament v4 resource for StockMovement management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class StockMovementResource extends Resource
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $model = StockMovement::class;

    public static function getNavigationLabel(): string
    {
        return __('stock_movement.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('stock_movement.plural');
    }

    public static function getModelLabel(): string
    {
        return __('stock_movement.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('stock_movement.sections.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('variant_inventory_id')
                                ->label(__('stock_movement.fields.variant_inventory'))
                                ->relationship('variantInventory', 'id')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                            Select::make('user_id')
                                ->label(__('stock_movement.fields.user'))
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                        ]),
                    Grid::make(2)
                        ->components([
                            Quantity::make('quantity')
                                ->label(__('stock_movement.fields.quantity'))
                                ->minValue(1)
                                ->steps(1)
                                ->default(1)
                                ->required(),
                            Select::make('type')
                                ->label(__('stock_movement.fields.type'))
                                ->options([
                                    'in'         => __('stock_movement.types.in'),
                                    'out'        => __('stock_movement.types.out'),
                                    'adjustment' => __('stock_movement.types.adjustment'),
                                    'transfer'   => __('stock_movement.types.transfer'),
                                ])
                                ->required(),
                        ]),
                ])
                ->columns(1),
            Section::make(__('stock_movement.sections.details'))
                ->components([
                    TextInput::make('reason')
                        ->label(__('stock_movement.fields.reason'))
                        ->maxLength(255),
                    TextInput::make('reference')
                        ->label(__('stock_movement.fields.reference'))
                        ->maxLength(255),
                    Textarea::make('notes')
                        ->label(__('stock_movement.fields.notes'))
                        ->maxLength(1000)
                        ->rows(3),
                    Flatpickr::makeDateTime('moved_at')
                        ->label(__('stock_movement.fields.moved_at'))
                        ->required()
                        ->default(now()),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variantInventory.variant.name')
                    ->label(__('stock_movement.fields.variant'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('stock_movement.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('stock_movement.fields.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('stock_movement.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in'         => 'success',
                        'out'        => 'danger',
                        'adjustment' => 'warning',
                        'transfer'   => 'info',
                        default      => 'gray',
                    }),
                TextColumn::make('reason')
                    ->label(__('stock_movement.fields.reason'))
                    ->toggleable(),
                TextColumn::make('reference')
                    ->label(__('stock_movement.fields.reference'))
                    ->toggleable(),
                TextColumn::make('moved_at')
                    ->label(__('stock_movement.fields.moved_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'in'         => __('stock_movement.types.in'),
                        'out'        => __('stock_movement.types.out'),
                        'adjustment' => __('stock_movement.types.adjustment'),
                        'transfer'   => __('stock_movement.types.transfer'),
                    ]),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->preload(),
                Filter::make('moved_at')
                    ->form([
                        Flatpickr::makeDate('moved_from')
                            ->label(__('stock_movement.fields.moved_at'))
                            ->placeholder(__('stock_movement.filters.from')),
                        Flatpickr::makeDate('moved_to')
                            ->label(__('stock_movement.fields.moved_at'))
                            ->placeholder(__('stock_movement.filters.to')),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        return $query
                            ->when($data['moved_from'] ?? null, fn ($q, $date) => $q->whereDate('moved_at', '>=', $date))
                            ->when($data['moved_to'] ?? null, fn ($q, $date) => $q->whereDate('moved_at', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('moved_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'view'   => Pages\ViewStockMovement::route('/{record}'),
            'edit'   => Pages\EditStockMovement::route('/{record}/edit'),
        ];
    }
}
