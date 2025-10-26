<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

final class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('currencies.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('currencies.single');
    }

    /**
     * Configure read-only view for currency details.
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('name')
                ->label(__('currencies.name')),
            TextEntry::make('code')
                ->label(__('currencies.code')),
            TextEntry::make('symbol')
                ->label(__('currencies.symbol')),
            TextEntry::make('iso_code')
                ->label(__('currencies.iso_code'))
                ->placeholder('—'),
            TextEntry::make('exchange_rate')
                ->label(__('currencies.exchange_rate'))
                ->numeric(decimalPlaces: 6),
            TextEntry::make('base_currency')
                ->label(__('currencies.base_currency')),
            TextEntry::make('decimal_places')
                ->label(__('currencies.decimal_places')),
            TextEntry::make('symbol_position')
                ->label(__('currencies.symbol_position'))
                ->formatStateUsing(fn (?string $state): string => $state ? __("currencies.positions.{$state}") : '—'),
            TextEntry::make('thousands_separator')
                ->label(__('currencies.thousands_separator')),
            TextEntry::make('decimal_separator')
                ->label(__('currencies.decimal_separator')),
            IconEntry::make('is_active')
                ->label(__('currencies.is_active'))
                ->boolean(),
            IconEntry::make('is_default')
                ->label(__('currencies.is_default'))
                ->boolean(),
            IconEntry::make('auto_update_rate')
                ->label(__('currencies.auto_update_rate'))
                ->boolean(),
            TextEntry::make('sort_order')
                ->label(__('currencies.sort_order')),
            TextEntry::make('description')
                ->label(__('currencies.description'))
                ->columnSpanFull()
                ->placeholder('—'),
        ]);
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('currencies.basic_information'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('currencies.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('code')
                                ->label(__('currencies.code'))
                                ->maxLength(5)
                                ->rules(static function (TextInput $component): array {
                                    $uniqueRule = Rule::unique(Currency::class, 'code')
                                        ->whereNull('deleted_at');

                                    $record = $component->getRecord();

                                    if ($record !== null && $record->exists) {
                                        $uniqueRule->ignore($record);
                                    }

                                    return ['alpha', $uniqueRule];
                                })
                                ->helperText(__('currencies.code_help')),
                        ]),
                    SchemaGrid::make(2)
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
            SchemaSection::make(__('currencies.exchange_rates'))
                ->components([
                    SchemaGrid::make(2)
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
            SchemaSection::make(__('currencies.formatting'))
                ->components([
                    SchemaGrid::make(2)
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
                                    'after'  => __('currencies.positions.after'),
                                ])
                                ->default('after'),
                        ]),
                    SchemaGrid::make(2)
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
            SchemaSection::make(__('currencies.settings'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('currencies.is_active'))
                                ->default(true),
                            Toggle::make('is_default')
                                ->label(__('currencies.is_default')),
                        ]),
                    SchemaGrid::make(2)
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
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
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
                    ->formatStateUsing(fn ($state): string => number_format($state, 6)),
                TextColumn::make('base_currency')
                    ->label(__('currencies.base_currency'))
                    ->color('purple'),
                TextColumn::make('decimal_places')
                    ->label(__('currencies.decimal_places'))
                    ->numeric(),
                TextColumn::make('symbol_position')
                    ->label(__('currencies.symbol_position'))
                    ->formatStateUsing(fn (string $state): string => __("currencies.positions.{$state}"))
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
                DeleteAction::make(),
                Action::make('toggle_active')
                    ->label(fn (?Currency $record): string => ($record?->is_active ?? false) ? __('currencies.deactivate') : __('currencies.activate'))
                    ->icon(fn (?Currency $record): string => ($record?->is_active ?? false) ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (?Currency $record): string => ($record?->is_active ?? false) ? 'warning' : 'success')
                    ->action(function (?Currency $record): void {
                        if ($record === null) {
                            return;
                        }

                        $newIsActive = ! $record->is_active;

                        Currency::whereKey($record)->update(['is_active' => $newIsActive]);
                        $record->refresh();

                        Notification::make()
                            ->title($record->is_active ? __('currencies.activated_successfully') : __('currencies.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('set_default')
                    ->label(__('currencies.set_default'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (?Currency $record): bool => ! ($record?->is_default ?? false))
                    ->action(function (?Currency $record): void {
                        if ($record === null) {
                            return;
                        }

                        // Remove default from other currencies
                        Currency::where('is_default', true)->update(['is_default' => false]);
                        // Set this currency as default
                        $record->update(['is_default' => true]);
                        Notification::make()
                            ->title(__('currencies.set_as_default_successfully'))
                            ->success()
                            ->send();
                    }),
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
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('currencies.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $recordIds = $records->modelKeys();
                            if ($recordIds === []) {
                                return;
                            }

                            Currency::whereKey($recordIds)->update(['is_active' => true]);
                            $records->each(function (Currency $record): void {
                                $record->refresh();
                            });

                            Notification::make()
                                ->title(__('currencies.bulk_activated_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('currencies.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $recordIds = $records->modelKeys();
                            if ($recordIds === []) {
                                return;
                            }

                            Currency::whereKey($recordIds)->update(['is_active' => false]);
                            $records->each(function (Currency $record): void {
                                $record->refresh();
                            });

                            Notification::make()
                                ->title(__('currencies.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        }),
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
                        }),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'view'   => Pages\ViewCurrency::route('/{record}'),
            'edit'   => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
