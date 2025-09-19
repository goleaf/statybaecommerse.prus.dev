<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\PriceResource\Pages;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
/**
 * PriceResource
 *
 * Filament v4 resource for Price management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PriceResource extends Resource
{
    protected static ?string $model = Price::class;
    /**
     * @var UnitEnum|string|null
     */    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = "Products";
    protected static ?int $navigationSort = 6;
    protected static ?string $recordTitleAttribute = 'priceable_type';
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('prices.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Products";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('prices.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('prices.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
        return $schema->components([
            Section::make(__('prices.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('priceable_type')
                                ->label(__('prices.priceable_type'))
                                ->options([
                                    'product' => __('prices.types.product'),
                                    'product_variant' => __('prices.types.product_variant'),
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $set('priceable_id', null);
                                }),
                            Select::make('priceable_id')
                                ->label(__('prices.priceable_item'))
                                ->options(function (Forms\Get $get) {
                                    $type = $get('priceable_type');
                                    if ($type === 'product') {
                                        return Product::pluck('name', 'id');
                                    } elseif ($type === 'product_variant') {
                                        return ProductVariant::pluck('name', 'id');
                                    }
                                    return [];
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                        ]),
                            TextInput::make('price')
                                ->label(__('prices.price'))
                                ->numeric()
                                ->prefix('€')
                                ->step(0.01)
                                ->minValue(0),
                            Select::make('currency_id')
                                ->label(__('prices.currency'))
                                ->relationship('currency', 'code')
                                ->default(fn() => Currency::where('is_default', true)->first()?->id),
                ]),
            Section::make(__('prices.pricing_details'))
                            TextInput::make('compare_price')
                                ->label(__('prices.compare_price'))
                            TextInput::make('cost_price')
                                ->label(__('prices.cost_price'))
                            TextInput::make('sale_price')
                                ->label(__('prices.sale_price'))
                            TextInput::make('wholesale_price')
                                ->label(__('prices.wholesale_price'))
            Section::make(__('prices.settings'))
                            Toggle::make('is_active')
                                ->label(__('prices.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('prices.is_default')),
                            DateTimePicker::make('valid_from')
                                ->label(__('prices.valid_from'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            DateTimePicker::make('valid_until')
                                ->label(__('prices.valid_until'))
                    Textarea::make('notes')
                        ->label(__('prices.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
        ]);
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
    public static function table(Table $table): Table
        return $table
            ->columns([
                TextColumn::make('priceable_type')
                    ->label(__('prices.type'))
                    ->formatStateUsing(fn(string $state): string => __("prices.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'product' => 'blue',
                        'product_variant' => 'green',
                        default => 'gray',
                    }),
                TextColumn::make('priceable.name')
                    ->label(__('prices.item'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('price')
                    ->label(__('prices.price'))
                    ->money('EUR')
                    ->weight('bold'),
                TextColumn::make('compare_price')
                    ->label(__('prices.compare_price'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_price')
                    ->label(__('prices.cost_price'))
                TextColumn::make('sale_price')
                    ->label(__('prices.sale_price'))
                TextColumn::make('currency.code')
                    ->label(__('prices.currency'))
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label(__('prices.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('prices.is_default'))
                TextColumn::make('valid_from')
                    ->label(__('prices.valid_from'))
                    ->dateTime()
                TextColumn::make('valid_until')
                    ->label(__('prices.valid_until'))
                TextColumn::make('created_at')
                    ->label(__('prices.created_at'))
                TextColumn::make('updated_at')
                    ->label(__('prices.updated_at'))
            ])
            ->filters([
                SelectFilter::make('priceable_type')
                    ->label(__('prices.priceable_type'))
                    ->options([
                        'product' => __('prices.types.product'),
                        'product_variant' => __('prices.types.product_variant'),
                    ]),
                SelectFilter::make('currency_id')
                    ->relationship('currency', 'code')
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('prices.active_only'))
                    ->falseLabel(__('prices.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->trueLabel(__('prices.default_only'))
                    ->falseLabel(__('prices.non_default_only'))
                Filter::make('valid_from')
                    ->form([
                        Forms\Components\DatePicker::make('valid_from')
                            ->label(__('prices.valid_from')),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label(__('prices.valid_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['valid_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('valid_from', '>=', $date),
                            )
                                $data['valid_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('valid_until', '<=', $date),
                            );
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(Price $record): string => $record->is_active ? __('prices.deactivate') : __('prices.activate'))
                    ->icon(fn(Price $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Price $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Price $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('prices.activated_successfully') : __('prices.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('prices.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(Price $record): bool => !$record->is_default)
                        // Remove default from other prices for the same item
                        Price::where('priceable_type', $record->priceable_type)
                            ->where('priceable_id', $record->priceable_id)
                            ->where('is_default', true)
                            ->update(['is_default' => false]);
                        // Set this price as default
                        $record->update(['is_default' => true]);
                            ->title(__('prices.set_as_default_successfully'))
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('prices.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('prices.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('prices.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                            $records->each->update(['is_active' => false]);
                                ->title(__('prices.bulk_deactivated_success'))
            ->defaultSort('created_at', 'desc');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'view' => Pages\ViewPrice::route('/{record}'),
            'edit' => Pages\EditPrice::route('/{record}/edit'),
}
