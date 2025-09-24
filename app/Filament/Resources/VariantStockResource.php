<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantStockResource\Pages;
use App\Models\Location;
use App\Models\Partner;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class VariantStockResource extends Resource
{
    protected static ?string $model = VariantInventory::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Stock Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('variant_id')
                                ->label('Variant')
                                ->relationship('variant', 'name', modifyQueryUsing: fn (Builder $q) => $q->with('product'))
                                ->required()
                                ->searchable()
                                ->preload(),
                            Select::make('location_id')
                                ->label('Location')
                                ->relationship('location', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('stock')->numeric()->required(),
                            TextInput::make('reserved')->numeric()->default(0),
                            TextInput::make('incoming')->numeric()->default(0),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('threshold')->numeric()->default(0),
                            TextInput::make('reorder_point')->numeric()->default(0),
                            TextInput::make('max_stock_level')->numeric()->default(0),
                        ]),
                ]),
            Section::make('Procurement')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('cost_per_unit')->numeric()->step(0.01),
                            Select::make('supplier_id')
                                ->label('Supplier')
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload(),
                            TextInput::make('batch_number'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('expiry_date'),
                            Select::make('status')
                                ->options([
                                    'active' => 'active',
                                    'inactive' => 'inactive',
                                    'discontinued' => 'discontinued',
                                    'quarantine' => 'quarantine',
                                ])
                                ->default('active'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_tracked')->default(true),
                        ]),
                    Textarea::make('notes')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
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
                    'gray' => 'inactive',
                    'danger' => 'discontinued',
                ]),
            ])
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
                \Filament\Tables\Actions\Action::make('reserve_stock')
                    ->label('Reserve stock')
                    ->form([
                        TextInput::make('quantity')->numeric()->required()->minValue(1),
                    ])
                    ->action(function (VariantInventory $record, array $data): void {
                        $quantity = (int) ($data['quantity'] ?? 0);
                        if ($quantity <= 0) {
                            return;
                        }
                        if (($record->stock - $record->reserved) < $quantity) {
                            return; // insufficient available
                        }
                        $record->reserved += $quantity;
                        $record->available = max(0, $record->stock - $record->reserved);
                        $record->save();
                    }),
                \Filament\Tables\Actions\Action::make('unreserve_stock')
                    ->label('Unreserve stock')
                    ->form([
                        TextInput::make('quantity')->numeric()->required()->minValue(1),
                    ])
                    ->action(function (VariantInventory $record, array $data): void {
                        $quantity = (int) ($data['quantity'] ?? 0);
                        if ($quantity <= 0) {
                            return;
                        }
                        $record->reserved = max(0, $record->reserved - $quantity);
                        $record->available = max(0, $record->stock - $record->reserved);
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
                                'adjustment' => 'adjustment',
                            ])->required(),
                        ])
                        ->action(function (array $records, array $data): void {
                            $qty = (int) ($data['quantity'] ?? 0);
                            if ($qty <= 0) {
                                return;
                            }
                            /** @var VariantInventory $record */
                            foreach ($records as $record) {
                                $record->stock += $qty;
                                $record->available = max(0, $record->stock - $record->reserved);
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
            'index' => Pages\ListVariantStocks::route('/'),
            'create' => Pages\CreateVariantStock::route('/create'),
            'edit' => Pages\EditVariantStock::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = VariantInventory::query()
            ->whereColumn('stock', '<=', 'threshold')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $hasOut = VariantInventory::query()->where('stock', '=', 0)->exists();
        if ($hasOut) {
            return 'danger';
        }
        $hasLow = VariantInventory::query()->whereColumn('stock', '<=', 'threshold')->exists();
        if ($hasLow) {
            return 'warning';
        }

        return 'success';
    }
}
