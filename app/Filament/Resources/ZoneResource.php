<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Currency;
use App\Models\Zone;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use UnitEnum;

final class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;
    
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-globe-alt';
    
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::System;
    
    protected static ?int $navigationSort = 4;
    
    public static function getNavigationLabel(): string
    {
        return __('admin.zones.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.zones.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.zones.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.zones.basic_information'))
                    ->description(__('admin.zones.basic_information_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.zones.name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label(__('admin.zones.code'))
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(Zone::class, 'code', ignoreRecord: true)
                                    ->helperText(__('admin.zones.code_helper')),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('currency_id')
                                    ->label(__('admin.zones.currency'))
                                    ->options(Currency::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Toggle::make('is_enabled')
                                    ->label(__('admin.zones.is_enabled'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.zones.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label(__('admin.zones.code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('currency.name')
                    ->label(__('admin.zones.currency'))
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_enabled')
                    ->label(__('admin.zones.is_enabled'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('countries_count')
                    ->label(__('admin.zones.countries_count'))
                    ->counts('countries')
                    ->sortable(),

                TextColumn::make('cities_count')
                    ->label(__('admin.zones.cities_count'))
                    ->counts('cities')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('currency_id')
                    ->label(__('admin.zones.currency'))
                    ->options(Currency::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_enabled')
                    ->label(__('admin.zones.is_enabled'))
                    ->boolean(),
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
                        ->label(__('admin.zones.enable_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each->update(['is_enabled' => true]);
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('disable')
                        ->label(__('admin.zones.disable_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            $records->each->update(['is_enabled' => false]);
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'view' => Pages\ViewZone::route('/{record}'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
