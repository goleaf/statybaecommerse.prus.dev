<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantStockResource\Pages;
use App\Models\Location;
use App\Models\VariantInventory;
use App\Support\Concerns\HasNav;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

final class VariantStockResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make('Stock Details')
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('variant_id')
                                ->label('Variant')
                                ->relationship('variant', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Select::make('location_id')
                                ->label('Location')
                                ->options(fn () => Location::query()->pluck('code', 'id'))
                                ->required()
                                ->native(false)
                                ->searchable()
                                ->preload(),
                        ]),
                    SchemaGrid::make(3)
                        ->schema([
                            TextInput::make('stock')->numeric()->required(),
                            TextInput::make('reserved')->numeric()->default(0),
                            TextInput::make('incoming')->numeric()->default(0),
                        ]),
                    SchemaGrid::make(3)
                        ->schema([
                            TextInput::make('threshold')->numeric()->default(0),
                            TextInput::make('reorder_point')->numeric()->default(0),
                            TextInput::make('max_stock_level')->numeric()->default(0),
                        ]),
                ]),
            SchemaSection::make('Procurement')
                ->schema([
                    SchemaGrid::make(3)
                        ->schema([
                            TextInput::make('cost_per_unit')->numeric()->step(0.01),
                            Select::make('supplier_id')
                                ->label('Supplier')
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload(),
                            TextInput::make('batch_number'),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            SupportFlatpickr::makeDate('expiry_date'),
                            Select::make('status')
                                ->options([
                                    'active'       => 'active',
                                    'inactive'     => 'inactive',
                                    'discontinued' => 'discontinued',
                                    'quarantine'   => 'quarantine',
                                ])
                                ->default('active'),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_tracked')->default(true),
                        ]),
                    Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * @return Builder<VariantInventory>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('variant.product.name')->label('Product')->searchable()->sortable(),
                TextColumn::make('variant.name')->label('Variant')->searchable()->sortable(),
                TextColumn::make('location.name')->label('Location')->searchable()->sortable(),
                TextColumn::make('stock')->sortable(),
                TextColumn::make('reserved')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('available_stock')
                    ->label('Available')
                    ->getStateUsing(fn (VariantInventory $record): int => max(0, (int) $record->stock - (int) $record->reserved))
                    ->sortable(),
                BadgeColumn::make('status')->colors([
                    'success' => 'active',
                    'gray'    => 'inactive',
                    'danger'  => 'discontinued',
                ]),
            ])
            ->deferLoading(false)
            ->deferFilters(false)
            ->filters([
                SelectFilter::make('location_id')->relationship('location', 'name')->label('Location')->searchable(),
                Filter::make('low_stock')
                    ->label('Low stock')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock', '<=', 'threshold')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('reserve_stock')
                    ->label('Reserve stock')
                    ->form([
                        TextInput::make('quantity')->numeric()->required()->minValue(1),
                    ])
                    ->action(function (array $data, ?VariantInventory $record): void {
                        if (! $record) {
                            return;
                        }
                        $rawQuantity = $data['quantity'] ?? null;
                        if (! is_numeric($rawQuantity)) {
                            return;
                        }
                        $quantity = (int) $rawQuantity;
                        if ($quantity <= 0) {
                            return;
                        }
                        if (($record->stock - $record->reserved) < $quantity) {
                            return;  // insufficient available
                        }
                        $record->reserved += $quantity;
                        $record->setAttribute('available', max(0, $record->stock - $record->reserved));
                        $record->save();
                    }),
                Action::make('unreserve_stock')
                    ->label('Unreserve stock')
                    ->form([
                        TextInput::make('quantity')->numeric()->required()->minValue(1),
                    ])
                    ->action(function (array $data, ?VariantInventory $record): void {
                        if (! $record) {
                            return;
                        }
                        $rawQuantity = $data['quantity'] ?? null;
                        if (! is_numeric($rawQuantity)) {
                            return;
                        }
                        $quantity = (int) $rawQuantity;
                        if ($quantity <= 0) {
                            return;
                        }
                        $record->reserved = max(0, $record->reserved - $quantity);
                        $record->setAttribute('available', max(0, $record->stock - $record->reserved));
                        $record->save();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_adjust_stock')
                        ->label('Bulk adjust stock')
                        ->form([
                            TextInput::make('quantity')->numeric()->required()->minValue(1),
                            Select::make('reason')->options([
                                'bulk_restock' => 'bulk_restock',
                                'adjustment'   => 'adjustment',
                            ])->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $rawQuantity = $data['quantity'] ?? null;
                            if (! is_numeric($rawQuantity)) {
                                return;
                            }
                            $qty = (int) $rawQuantity;
                            if ($qty <= 0) {
                                return;
                            }
                            /** @var VariantInventory $record */
                            foreach ($records as $record) {
                                $record->stock += $qty;
                                $record->setAttribute('available', max(0, $record->stock - $record->reserved));
                                $record->save();
                            }
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVariantStocks::route('/'),
            'create' => Pages\CreateVariantStock::route('/create'),
            'edit'   => Pages\EditVariantStock::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = VariantInventory::query()
            ->withoutGlobalScopes()
            ->whereColumn('stock', '<=', 'threshold')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $hasOut = VariantInventory::query()->withoutGlobalScopes()->where('stock', '=', 0)->exists();
        if ($hasOut) {
            return 'danger';
        }
        $hasLow = VariantInventory::query()->withoutGlobalScopes()->whereColumn('stock', '<=', 'threshold')->exists();
        if ($hasLow) {
            return 'warning';
        }

        return null;
    }
}
