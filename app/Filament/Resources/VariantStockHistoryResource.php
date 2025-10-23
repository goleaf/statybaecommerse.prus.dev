<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\VariantStockHistoryResource\Pages;
use App\Models\VariantStockHistory;
use App\Support\Filament\Components\Flatpickr;
use App\Support\Filament\Filters\DateRangeFilter;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class VariantStockHistoryResource extends Resource
{
    use HasNav;

    

    protected static ?string $model = VariantStockHistory::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('admin.variant_stock_histories.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.variant_stock_histories.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.variant_stock_histories.single');
    }

    public static function form(Schema $schema): Schema
    {

        $form = $schema; // Preserve legacy variable naming for existing schema definitions.

        return $form->schema([
            Section::make(__('admin.variant_stock_histories.sections.basic_info'))
                ->description(__('admin.variant_stock_histories.sections.basic_info_description'))
                ->schema([
                    Select::make('variant_id')
                        ->label(__('admin.variant_stock_histories.fields.variant'))
                        ->relationship('variant', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('old_quantity')
                                ->label(__('admin.variant_stock_histories.fields.old_quantity'))
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->live(debounce: 500)
                                ->afterStateUpdated(function (Set $set, $state, Get $get): void {
                                    $set(
                                        'quantity_change',
                                        self::calculateQuantityChange($state, $get('new_quantity')),
                                    );
                                })
                                ->validationMessages([
                                    'min' => __('The previous quantity must be zero or positive.'),
                                ]),
                            TextInput::make('new_quantity')
                                ->label(__('admin.variant_stock_histories.fields.new_quantity'))
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->live(debounce: 500)
                                ->afterStateUpdated(function (Set $set, $state, Get $get): void {
                                    $set(
                                        'quantity_change',
                                        self::calculateQuantityChange($get('old_quantity'), $state),
                                    );
                                }),
                        ]),
                    TextInput::make('quantity_change')
                        ->label(__('admin.variant_stock_histories.fields.quantity_change'))
                        ->disabled(),
                    Grid::make(2)
                        ->schema([
                            Select::make('change_type')
                                ->label(__('admin.variant_stock_histories.fields.change_type'))
                                ->options(self::getChangeTypeOptions())
                                ->required(),
                            Select::make('change_reason')
                                ->label(__('admin.variant_stock_histories.fields.change_reason'))
                                ->options(self::getChangeReasonOptions())
                                ->required(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('changed_by')
                                ->label(__('admin.variant_stock_histories.fields.changed_by'))
                                ->relationship('changedBy', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('reference_type')
                                ->label(__('admin.variant_stock_histories.fields.reference_type'))
                                ->options([
                                    'order'       => 'Order',
                                    'reservation' => 'Reservation',
                                ]),
                        ]),
                    TextInput::make('reference_id')
                        ->label(__('admin.variant_stock_histories.fields.reference_id'))
                        ->numeric(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                TextColumn::make('variant.name')
                    ->label(__('admin.variant_stock_histories.fields.variant'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('old_quantity')
                    ->label(__('admin.variant_stock_histories.fields.old_quantity'))
                    ->sortable()
                    ->numeric(),
                TextColumn::make('new_quantity')
                    ->label(__('admin.variant_stock_histories.fields.new_quantity'))
                    ->sortable()
                    ->numeric(),
                TextColumn::make('quantity_change')
                    ->label(__('admin.variant_stock_histories.fields.quantity_change'))
                    ->formatStateUsing(fn (?int $state): string => (($state ?? 0) >= 0 ? '+' : '') . (string) ($state ?? 0))
                    ->color(fn (?int $state): string => ($state ?? 0) >= 0 ? 'success' : 'danger'),
                BadgeColumn::make('change_type')
                    ->label(__('admin.variant_stock_histories.fields.change_type'))
                    ->formatStateUsing(fn (string $state): string => __('admin.variant_stock_histories.change_types.' . $state))
                    ->colors([
                        'success' => ['increase', 'unreserve'],
                        'danger'  => ['decrease', 'reserve'],
                        'warning' => ['adjustment'],
                    ]),
                BadgeColumn::make('change_reason')
                    ->label(__('admin.variant_stock_histories.fields.change_reason'))
                    ->formatStateUsing(fn (string $state): string => __('admin.variant_stock_histories.change_reasons.' . $state))
                    ->colors([
                        'success' => ['sale'],
                        'info'    => ['return', 'reserve', 'unreserve'],
                        'warning' => ['adjustment', 'expired'],
                        'danger'  => ['damage', 'theft'],
                        'gray'    => ['manual'],
                    ]),
                TextColumn::make('changedBy.name')
                    ->label(__('admin.variant_stock_histories.fields.changed_by'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference_type')
                    ->label(__('admin.variant_stock_histories.fields.reference_type'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reference_id')
                    ->label(__('admin.variant_stock_histories.fields.reference_id'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.variant_stock_histories.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('change_type')
                    ->label(__('admin.variant_stock_histories.filters.change_type'))
                    ->options(self::getChangeTypeOptions()),
                SelectFilter::make('change_reason')
                    ->label(__('admin.variant_stock_histories.filters.change_reason'))
                    ->options(self::getChangeReasonOptions()),
                SelectFilter::make('variant_id')
                    ->label(__('admin.variant_stock_histories.filters.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->label(__('admin.variant_stock_histories.filters.created_at'))
                    ->form([
                        Flatpickr::makeRange('range')
                            ->label(__('admin.variant_stock_histories.filters.created_at'))

                            ->format('Y-m-d')
                            ->displayFormat('Y-m-d'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => DateRangeFilter::apply(
                        $query,
                        $data['range'] ?? null,
                        'created_at',
                    )),
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
            ->defaultSort('created_at', 'desc');
    }

    private static function calculateQuantityChange(mixed $oldQuantity, mixed $newQuantity): int
    {
        return (int) ($newQuantity ?? 0) - (int) ($oldQuantity ?? 0);
    }

    /**
     * @return array<string, string>
     */
    private static function getChangeTypeOptions(): array
    {
        return [
            'increase'   => __('admin.variant_stock_histories.change_types.increase'),
            'decrease'   => __('admin.variant_stock_histories.change_types.decrease'),
            'adjustment' => __('admin.variant_stock_histories.change_types.adjustment'),
            'reserve'    => __('admin.variant_stock_histories.change_types.reserve'),
            'unreserve'  => __('admin.variant_stock_histories.change_types.unreserve'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getChangeReasonOptions(): array
    {
        return [
            'sale'       => __('admin.variant_stock_histories.change_reasons.sale'),
            'return'     => __('admin.variant_stock_histories.change_reasons.return'),
            'adjustment' => __('admin.variant_stock_histories.change_reasons.adjustment'),
            'reserve'    => __('admin.variant_stock_histories.change_reasons.reserve'),
            'unreserve'  => __('admin.variant_stock_histories.change_reasons.unreserve'),
            'damage'     => __('admin.variant_stock_histories.change_reasons.damage'),
            'theft'      => __('admin.variant_stock_histories.change_reasons.theft'),
            'expired'    => __('admin.variant_stock_histories.change_reasons.expired'),
            'manual'     => __('admin.variant_stock_histories.change_reasons.manual'),
        ];
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
            'index'  => Pages\ListVariantStockHistories::route('/'),
            'create' => Pages\CreateVariantStockHistory::route('/create'),
            'view'   => Pages\ViewVariantStockHistory::route('/{record}'),
            'edit'   => Pages\EditVariantStockHistory::route('/{record}/edit'),
        ];
    }
}
