<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Enums\NavigationIcon;
use App\Filament\Resources\RegionResource\Pages;
use App\Filament\Resources\RegionResource\Widgets;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static NavigationIcon $navigationIcon = NavigationIcon::Map;

    /**
     * @var UnitEnum|string|null
     */
    protected static $navigationGroup = NavigationGroup::Content;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Regions';

    protected static ?string $modelLabel = 'Region';

    protected static ?string $pluralModelLabel = 'Regions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('regions.basic_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('regions.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('regions.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash']),
                        Forms\Components\TextInput::make('code')
                            ->label(__('regions.code'))
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText(__('regions.code_help')),
                        Forms\Components\Textarea::make('description')
                            ->label(__('regions.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('regions.location'))
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label(__('regions.country'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(3),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Country::create($data)->getKey();
                            }),
                        Forms\Components\Select::make('zone_id')
                            ->label(__('regions.zone'))
                            ->relationship('zone', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(10),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Zone::create($data)->getKey();
                            }),
                        Forms\Components\Select::make('parent_id')
                            ->label(__('regions.parent_region'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText(__('regions.parent_region_help')),
                        Forms\Components\Select::make('level')
                            ->label(__('regions.level'))
                            ->options([
                                0 => 'Root',
                                1 => 'State/Province',
                                2 => 'County',
                                3 => 'District',
                                4 => 'Municipality',
                                5 => 'Village',
                            ])
                            ->default(0)
                            ->required()
                            ->helperText(__('regions.level_help')),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('regions.status'))
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('regions.is_enabled'))
                            ->default(true)
                            ->helperText(__('regions.is_enabled_help')),
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('regions.is_default'))
                            ->default(false)
                            ->helperText(__('regions.is_default_help')),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('regions.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText(__('regions.sort_order_help')),
                    ])
                    ->columns(3),
                Forms\Components\Section::make(__('regions.translations'))
                    ->schema([
                        Forms\Components\Repeater::make('translations')
                            ->label(__('regions.translations'))
                            ->relationship('translations')
                            ->schema([
                                Forms\Components\Select::make('locale')
                                    ->label(__('regions.locale'))
                                    ->options([
                                        'lt' => 'Lithuanian',
                                        'en' => 'English',
                                        'de' => 'German',
                                        'ru' => 'Russian',
                                        'pl' => 'Polish',
                                        'lv' => 'Latvian',
                                        'et' => 'Estonian',
                                    ])
                                    ->required()
                                    ->searchable(),
                                Forms\Components\TextInput::make('name')
                                    ->label(__('regions.name'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label(__('regions.description'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel(__('regions.add_translation'))
                            ->collapsible()
                            ->cloneable()
                            ->reorderable(),
                    ]),
                Forms\Components\Section::make(__('regions.metadata'))
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label(__('regions.metadata'))
                            ->keyLabel(__('regions.key'))
                            ->valueLabel(__('regions.value'))
                            ->helperText(__('regions.metadata_help')),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('regions.code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('regions.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Region $record): string => $record->translated_description ?: ''),
                Tables\Columns\TextColumn::make('full_path')
                    ->label(__('regions.full_path'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('regions.country'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('zone.name')
                    ->label(__('regions.zone'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('regions.parent_region'))
                    ->sortable()
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('level')
                    ->label(__('regions.level'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'success',
                        1 => 'primary',
                        2 => 'warning',
                        3 => 'info',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'Root',
                        1 => 'State/Province',
                        2 => 'County',
                        3 => 'District',
                        4 => 'Municipality',
                        5 => 'Village',
                        default => "Level {$state}"
                    }),
                Tables\Columns\TextColumn::make('cities_count')
                    ->label(__('regions.cities_count'))
                    ->counts('cities')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('addresses_count')
                    ->label(__('regions.addresses_count'))
                    ->counts('addresses')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('regions.is_enabled'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('regions.is_default'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('regions.sort_order'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('regions.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('regions.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('regions.is_enabled'))
                    ->placeholder(__('regions.all_regions'))
                    ->trueLabel(__('regions.enabled_regions'))
                    ->falseLabel(__('regions.disabled_regions')),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('regions.is_default'))
                    ->placeholder(__('regions.all_regions'))
                    ->trueLabel(__('regions.default_regions'))
                    ->falseLabel(__('regions.non_default_regions')),
                Tables\Filters\SelectFilter::make('country')
                    ->label(__('regions.country'))
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('zone')
                    ->label(__('regions.zone'))
                    ->relationship('zone', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('level')
                    ->label(__('regions.level'))
                    ->options([
                        0 => 'Root',
                        1 => 'State/Province',
                        2 => 'County',
                        3 => 'District',
                        4 => 'Municipality',
                        5 => 'Village',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('regions.parent_region'))
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('has_children')
                    ->label(__('regions.has_children'))
                    ->query(fn (Builder $query): Builder => $query->has('children')),
                Tables\Filters\Filter::make('has_cities')
                    ->label(__('regions.has_cities'))
                    ->query(fn (Builder $query): Builder => $query->has('cities')),
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('regions.created_from')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('created_until')
                    ->form([
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('regions.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggle_enabled')
                    ->label(__('regions.toggle_enabled'))
                    ->icon('heroicon-o-power')
                    ->action(function (Region $record): void {
                        $record->update(['is_enabled' => ! $record->is_enabled]);
                    })
                    ->color(fn (Region $record): string => $record->is_enabled ? 'warning' : 'success')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('enable')
                        ->label(__('regions.enable_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('disable')
                        ->label(__('regions.disable_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\RegionStatsWidget::class,
            Widgets\RegionsByCountryWidget::class,
            Widgets\RegionsByLevelWidget::class,
            Widgets\RecentRegionsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegions::route('/'),
            'create' => Pages\CreateRegion::route('/create'),
            'view' => Pages\ViewRegion::route('/{record}'),
            'edit' => Pages\EditRegion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
