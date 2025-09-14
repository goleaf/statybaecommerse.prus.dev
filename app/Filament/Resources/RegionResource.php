<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RegionResource\Pages;
use BackedEnum;
use App\Filament\Resources\RegionResource\Widgets;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

final /**
 * RegionResource
 * 
 * Filament resource for admin panel management.
 */
class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('regions.navigation_label');
    }

    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Content';

    public static function getModelLabel(): string
    {
        return __('regions.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('regions.plural_model_label');
    }

    public static function form(Schema $schema): Schema {
        return $schema->schema([
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
                        Forms\Components\Textarea::make('description')
                            ->label(__('regions.description'))
                            ->maxLength(1000)
                            ->rows(3),
                        Forms\Components\TextInput::make('code')
                            ->label(__('regions.code'))
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make(__('regions.hierarchy'))
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label(__('regions.parent_region'))
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('level')
                            ->label(__('regions.level'))
                            ->options([
                                0 => __('regions.level_root'),
                                1 => __('regions.level_state'),
                                2 => __('regions.level_county'),
                                3 => __('regions.level_district'),
                                4 => __('regions.level_municipality'),
                                5 => __('regions.level_village'),
                            ])
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('regions.geographic'))
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label(__('regions.country'))
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('zone_id')
                            ->label(__('regions.zone'))
                            ->relationship('zone', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make(__('regions.settings'))
                    ->schema([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('regions.is_enabled'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('regions.is_default'))
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('regions.sort_order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('regions.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('regions.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('regions.country'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('zone.name')
                    ->label(__('regions.zone'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->label(__('regions.level'))
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => __('regions.level_root'),
                        1 => __('regions.level_state'),
                        2 => __('regions.level_county'),
                        3 => __('regions.level_district'),
                        4 => __('regions.level_municipality'),
                        5 => __('regions.level_village'),
                        default => "Level {$state}"
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('regions.is_enabled'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('regions.is_default'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('regions.sort_order'))
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('country_id')
                    ->label(__('regions.country'))
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('zone_id')
                    ->label(__('regions.zone'))
                    ->relationship('zone', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('level')
                    ->label(__('regions.level'))
                    ->options([
                        0 => __('regions.level_root'),
                        1 => __('regions.level_state'),
                        2 => __('regions.level_county'),
                        3 => __('regions.level_district'),
                        4 => __('regions.level_municipality'),
                        5 => __('regions.level_village'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('regions.is_enabled')),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('regions.is_default')),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
