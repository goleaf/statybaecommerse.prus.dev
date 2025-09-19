<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('currencies.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('currencies.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('currencies.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('currencies.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('currencies.code'))
                                ->maxLength(3)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha'])
                                ->helperText(__('currencies.code_help')),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('symbol')
                                ->label(__('currencies.symbol'))
                                ->maxLength(10)
                                ->helperText(__('currencies.symbol_help')),
                            TextInput::make('iso_code')
                                ->label(__('currencies.iso_code'))
                                ->helperText(__('currencies.iso_code_help')),
                        ]),
                    Textarea::make('description')
                        ->label(__('currencies.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('currencies.exchange_rates'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('exchange_rate')
                                ->label(__('currencies.exchange_rate'))
                                ->numeric()
                                ->step(0.000001)
                                ->minValue(0)
                                ->default(1)
                                ->helperText(__('currencies.exchange_rate_help')),
                            TextInput::make('base_currency')
                                ->label(__('currencies.base_currency'))
                                ->default('EUR')
                                ->helperText(__('currencies.base_currency_help')),
                        ]),
                ]),
            Section::make(__('currencies.formatting'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('decimal_places')
                                ->label(__('currencies.decimal_places'))
                                ->numeric()
                                ->maxValue(4)
                                ->default(2),
                            Select::make('symbol_position')
                                ->label(__('currencies.symbol_position'))
                                ->options([
                                    'before' => __('currencies.positions.before'),
                                    'after' => __('currencies.positions.after'),
                                ])
                                ->default('after'),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('thousands_separator')
                                ->label(__('currencies.thousands_separator'))
                                ->maxLength(1)
                                ->default(',')
                                ->helperText(__('currencies.thousands_separator_help')),
                            TextInput::make('decimal_separator')
                                ->label(__('currencies.decimal_separator'))
                                ->maxLength(1)
                                ->default('.')
                                ->helperText(__('currencies.decimal_separator_help')),
                        ]),
                ]),
            Section::make(__('currencies.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('currencies.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('currencies.is_default')),
                        ]),
                    Grid::make(2)
                        ->components([
                            TextInput::make('sort_order')
                                ->label(__('currencies.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                            Toggle::make('auto_update_rate')
                                ->label(__('currencies.auto_update_rate'))
                                ->default(false),
                        ]),
                ]),
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
                TextColumn::make('name')
                    ->label(__('currencies.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('code')
                    ->label(__('currencies.code'))
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('symbol')
                    ->label(__('currencies.symbol'))
                    ->color('blue'),
                TextColumn::make('iso_code')
                    ->label(__('currencies.iso_code'))
                    ->color('green')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('exchange_rate')
                    ->label(__('currencies.exchange_rate'))
                    ->numeric()
                    ->formatStateUsing(fn($state): string => number_format($state, 6)),
                TextColumn::make('base_currency')
                    ->label(__('currencies.base_currency'))
                    ->color('purple'),
                TextColumn::make('decimal_places')
                    ->label(__('currencies.decimal_places'))
                    ->numeric(),
                TextColumn::make('symbol_position')
                    ->label(__('currencies.symbol_position'))
                    ->formatStateUsing(fn(string $state): string => __("currencies.positions.{$state}"))
                    ->color('orange'),
                IconColumn::make('is_active')
                    ->label(__('currencies.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('currencies.is_default'))
                    ->boolean(),
                IconColumn::make('auto_update_rate')
                    ->label(__('currencies.auto_update_rate'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('currencies.sort_order'))
                    ->numeric(),
                TextColumn::make('created_at')
                    ->label(__('currencies.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('currencies.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->trueLabel(__('currencies.active_only'))
                    ->falseLabel(__('currencies.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_default')
                    ->trueLabel(__('currencies.default_only'))
                    ->falseLabel(__('currencies.non_default_only'))
                    ->native(false),
                TernaryFilter::make('auto_update_rate')
                    ->trueLabel(__('currencies.auto_update_only'))
                    ->falseLabel(__('currencies.manual_update_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(Currency $record): string => $record->is_active ? __('currencies.deactivate') : __('currencies.activate'))
                    ->icon(fn(Currency $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Currency $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Currency $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('currencies.activated_successfully') : __('currencies.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('set_default')
                    ->label(__('currencies.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(Currency $record): bool => !$record->is_default)
                    ->action(function (Currency $record): void {
                        // Remove default from other currencies
                        Currency::where('is_default', true)->update(['is_default' => false]);
                        // Set this currency as default
                        $record->update(['is_default' => true]);
                        Notification::make()
                            ->title(__('currencies.set_as_default_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('update_rate')
                    ->label(__('currencies.update_rate'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (Currency $record): void {
                        // Update exchange rate logic here
                        Notification::make()
                            ->title(__('currencies.rate_updated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('currencies.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('currencies.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('currencies.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('currencies.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('update_rates')
                        ->label(__('currencies.update_rates'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Update exchange rates logic here
                            Notification::make()
                                ->title(__('currencies.rates_updated_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'view' => Pages\ViewCurrency::route('/{record}'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
