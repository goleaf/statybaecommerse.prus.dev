<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('admin.currency.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.currency.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make(__('admin.currency.form.basic_info'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.currency.form.name'))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('code')
                            ->label(__('admin.currency.form.code'))
                            ->required()
                            ->maxLength(3)
                            ->unique(Currency::class, 'code', ignoreRecord: true)
                            ->helperText(__('admin.currency.form.code_help')),
                        
                        Forms\Components\TextInput::make('symbol')
                            ->label(__('admin.currency.form.symbol'))
                            ->required()
                            ->maxLength(10),
                        
                        Forms\Components\TextInput::make('exchange_rate')
                            ->label(__('admin.currency.form.exchange_rate'))
                            ->numeric()
                            ->step(0.0001)
                            ->default(1.0000)
                            ->helperText(__('admin.currency.form.exchange_rate_help')),
                        
                        Forms\Components\TextInput::make('precision')
                            ->label(__('admin.currency.form.precision'))
                            ->numeric()
                            ->default(2)
                            ->minValue(0)
                            ->maxValue(4),
                        
                        Forms\Components\Toggle::make('enabled')
                            ->label(__('admin.currency.form.enabled'))
                            ->default(true),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make(__('admin.currency.form.formatting'))
                    ->schema([
                        Forms\Components\TextInput::make('thousands_separator')
                            ->label(__('admin.currency.form.thousands_separator'))
                            ->default(',')
                            ->maxLength(1),
                        
                        Forms\Components\TextInput::make('decimal_separator')
                            ->label(__('admin.currency.form.decimal_separator'))
                            ->default('.')
                            ->maxLength(1),
                        
                        Forms\Components\Select::make('symbol_position')
                            ->label(__('admin.currency.form.symbol_position'))
                            ->options([
                                'before' => __('admin.currency.form.symbol_before'),
                                'after' => __('admin.currency.form.symbol_after'),
                            ])
                            ->default('before'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.currency.table.name'))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.currency.table.code'))
                    ->searchable()
                    ->sortable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('symbol')
                    ->label(__('admin.currency.table.symbol'))
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('exchange_rate')
                    ->label(__('admin.currency.table.exchange_rate'))
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('enabled')
                    ->label(__('admin.currency.table.enabled'))
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.currency.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.currency.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label(__('admin.currency.filters.enabled')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'view' => Pages\ViewCurrency::route('/{record}'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
