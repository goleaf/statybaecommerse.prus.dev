<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Currency;
use App\Models\Zone;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components as Schemas;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
final class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-europe-africa';


    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.zones');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.zone');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.zones');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Schemas\Tabs::make('zone_tabs')
                    ->tabs([
                        Schemas\Tabs\Tab::make('general')
                            ->label(__('admin.tabs.general'))
                            ->icon('heroicon-m-cog-6-tooth')
                            ->schema([
                                Schemas\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name.lt')
                                            ->label(__('admin.fields.name_lt'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                                if (!$get('slug') && $state) {
                                                    $set('slug', str($state)->slug());
                                                }
                                            }),
                                        Forms\Components\TextInput::make('name.en')
                                            ->label(__('admin.fields.name_en'))
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('slug')
                                            ->label(__('admin.fields.slug'))
                                            ->required()
                                            ->unique(Zone::class, 'slug', ignoreRecord: true)
                                            ->maxLength(255)
                                            ->rules(['alpha_dash']),
                                        Forms\Components\TextInput::make('code')
                                            ->label(__('admin.fields.code'))
                                            ->required()
                                            ->unique(Zone::class, 'code', ignoreRecord: true)
                                            ->maxLength(10)
                                            ->rules(['alpha_dash'])
                                            ->placeholder('EU, US, ASIA'),
                                        Forms\Components\Select::make('currency_id')
                                            ->label(__('admin.fields.currency'))
                                            ->relationship('currency', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label(__('admin.fields.name'))
                                                    ->required(),
                                                Forms\Components\TextInput::make('code')
                                                    ->label(__('admin.fields.code'))
                                                    ->required()
                                                    ->length(3),
                                                Forms\Components\TextInput::make('symbol')
                                                    ->label(__('admin.fields.symbol'))
                                                    ->required(),
                                            ]),
                                        Forms\Components\TextInput::make('sort_order')
                                            ->label(__('admin.fields.sort_order'))
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),
                                    ]),
                                Schemas\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Textarea::make('description.lt')
                                            ->label(__('admin.fields.description_lt'))
                                            ->rows(3)
                                            ->maxLength(500),
                                        Forms\Components\Textarea::make('description.en')
                                            ->label(__('admin.fields.description_en'))
                                            ->rows(3)
                                            ->maxLength(500),
                                    ]),
                            ]),
                        Schemas\Tabs\Tab::make('settings')
                            ->label(__('admin.tabs.settings'))
                            ->icon('heroicon-m-adjustments-horizontal')
                            ->schema([
                                Schemas\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('tax_rate')
                                            ->label(__('admin.fields.tax_rate'))
                                            ->numeric()
                                            ->step(0.0001)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(0.0)
                                            ->suffix('%')
                                            ->helperText(__('admin.help.tax_rate')),
                                        Forms\Components\TextInput::make('shipping_rate')
                                            ->label(__('admin.fields.shipping_rate'))
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->default(0.0)
                                            ->prefix('€')
                                            ->helperText(__('admin.help.shipping_rate')),
                                    ]),
                                Schemas\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('is_enabled')
                                            ->label(__('admin.fields.is_enabled'))
                                            ->default(true)
                                            ->helperText(__('admin.help.zone_enabled')),
                                        Forms\Components\Toggle::make('is_default')
                                            ->label(__('admin.fields.is_default'))
                                            ->default(false)
                                            ->helperText(__('admin.help.zone_default')),
                                    ]),
                                Forms\Components\KeyValue::make('metadata')
                                    ->label(__('admin.fields.metadata'))
                                    ->keyLabel(__('admin.fields.key'))
                                    ->valueLabel(__('admin.fields.value'))
                                    ->addActionLabel(__('admin.actions.add_metadata'))
                                    ->helperText(__('admin.help.zone_metadata')),
                            ]),
                        Schemas\Tabs\Tab::make('countries')
                            ->label(__('admin.tabs.countries'))
                            ->icon('heroicon-m-flag')
                            ->schema([
                                Forms\Components\CheckboxList::make('countries')
                                    ->label(__('admin.fields.countries'))
                                    ->relationship('countries', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record): string => (string) ($record->name ?? $record->cca2 ?? $record->cca3 ?? $record->code ?? $record->id))
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->columns(3)
                                    ->helperText(__('admin.help.zone_countries')),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(?string $state): string =>
                        $state ? (is_array($state) ? ($state[app()->getLocale()] ?? $state['lt'] ?? 'N/A') : $state) : 'N/A'),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.table.code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('currency.code')
                    ->label(__('admin.table.currency'))
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('tax_rate')
                    ->label(__('admin.table.tax_rate'))
                    ->numeric(decimalPlaces: 4)
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_rate')
                    ->label(__('admin.table.shipping_rate'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('countries_count')
                    ->label(__('admin.table.countries'))
                    ->counts('countries')
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.table.enabled'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('admin.table.default'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.table.sort'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.table.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('admin.filters.enabled')),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('admin.filters.default')),
                Tables\Filters\SelectFilter::make('currency_id')
                    ->label(__('admin.filters.currency'))
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('enable')
                        ->label(__('admin.actions.enable'))
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn($records) => $records->each(fn($record) => $record->update(['is_enabled' => true])))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('disable')
                        ->label(__('admin.actions.disable'))
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn($records) => $records->each(fn($record) => $record->update(['is_enabled' => false])))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
            'view' => Pages\ViewZone::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['currency', 'countries']);
    }
}
