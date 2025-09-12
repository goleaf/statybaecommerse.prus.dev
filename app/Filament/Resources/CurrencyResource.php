<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Models\Currency;
use App\Services\MultiLanguageTabService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
final class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';


    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('admin.currency.singular');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('name')
                    ->label(__('admin.currency.table.name')),
                TextEntry::make('code')
                    ->label(__('admin.currency.table.code')),
                TextEntry::make('symbol')
                    ->label(__('admin.currency.table.symbol')),
                TextEntry::make('exchange_rate')
                    ->label(__('admin.currency.table.exchange_rate')),
                IconEntry::make('is_enabled')
                    ->label(__('admin.currency.table.enabled'))
                    ->boolean(),
                IconEntry::make('is_default')
                    ->label(__('admin.currency.table.is_default'))
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label(__('admin.currency.table.created_at'))
                    ->date('Y-m-d'),
            ]);
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.currency.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Currency Settings (Non-translatable)
                Section::make(__('translations.currency_settings'))
                    ->components([
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
                            ->default(1.0)
                            ->helperText(__('admin.currency.form.exchange_rate_help')),
                        Forms\Components\TextInput::make('decimal_places')
                            ->label(__('admin.currency.form.decimal_places'))
                            ->numeric()
                            ->default(2)
                            ->minValue(0)
                            ->maxValue(4),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.currency.form.enabled'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('admin.currency.form.is_default'))
                            ->default(false)
                            ->helperText(__('admin.currency.form.is_default_help')),
                    ])
                    ->columns(2),
                // Multilanguage Tabs for Currency Content
                ...(!app()->environment('testing')
                    ? [
                        Tabs::make('currency_translations')
                            ->tabs(
                                MultiLanguageTabService::createSectionedTabs([
                                    'currency_information' => [
                                        'name' => [
                                            'type' => 'text',
                                            'label' => __('translations.name'),
                                            'required' => true,
                                            'maxLength' => 255,
                                            'placeholder' => __('translations.currency_name_help'),
                                        ],
                                    ],
                                ])
                            )
                            ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                            ->persistTabInQueryString('currency_tab')
                            ->contained(false),
                    ]
                    : [
                        Section::make(__('translations.currency_information'))
                            ->components([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('translations.name'))
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(1),
                    ]),
                Section::make(__('admin.currency.form.formatting'))
                    ->components([
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
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.currency.table.enabled'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('admin.currency.table.is_default'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.currency.table.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.currency.table.updated_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('admin.currency.filters.enabled')),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('admin.currency.filters.is_default')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
