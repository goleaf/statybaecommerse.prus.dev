<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingOptions;

use App\Filament\Resources\ShippingOptions\Pages\CreateShippingOption;
use App\Filament\Resources\ShippingOptions\Pages\EditShippingOption;
use App\Filament\Resources\ShippingOptions\Pages\ListShippingOptions;
use App\Models\ShippingOption;
use App\Models\Zone;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShippingOptionResource extends Resource
{
    protected static ?string $model = ShippingOption::class;

    protected static ?string $navigationGroup = 'Shipping';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ShippingOption::class, 'slug', ignoreRecord: true),
                            ]),
                        Textarea::make('description')
                            ->rows(3),
                    ]),
                
                Section::make('Shipping Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('carrier_name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('service_type')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('€'),
                                TextInput::make('currency_code')
                                    ->required()
                                    ->maxLength(3)
                                    ->default('EUR'),
                            ]),
                    ]),
                
                Section::make('Configuration')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('zone_id')
                                    ->required()
                                    ->relationship('zone', 'name')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Checkbox::make('is_enabled')
                                    ->default(true),
                                Checkbox::make('is_default')
                                    ->default(false),
                            ]),
                    ]),
                
                Section::make('Weight & Order Limits')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_weight')
                                    ->numeric()
                                    ->suffix('kg'),
                                TextInput::make('max_weight')
                                    ->numeric()
                                    ->suffix('kg'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_order_amount')
                                    ->numeric()
                                    ->prefix('€'),
                                TextInput::make('max_order_amount')
                                    ->numeric()
                                    ->prefix('€'),
                            ]),
                    ]),
                
                Section::make('Delivery Times')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('estimated_days_min')
                                    ->numeric()
                                    ->suffix('days'),
                                TextInput::make('estimated_days_max')
                                    ->numeric()
                                    ->suffix('days'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('carrier_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_type')
                    ->searchable(),
                TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('zone.name')
                    ->searchable()
                    ->sortable(),
                BooleanColumn::make('is_enabled')
                    ->label('Enabled'),
                BooleanColumn::make('is_default')
                    ->label('Default'),
                TextColumn::make('estimated_delivery_text')
                    ->label('Delivery Time'),
                TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('zone_id')
                    ->relationship('zone', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('is_enabled')
                    ->options([
                        1 => 'Enabled',
                        0 => 'Disabled',
                    ]),
                SelectFilter::make('carrier_name')
                    ->options(function () {
                        return ShippingOption::distinct('carrier_name')
                            ->pluck('carrier_name', 'carrier_name')
                            ->toArray();
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => ListShippingOptions::route('/'),
            'create' => CreateShippingOption::route('/create'),
            'edit' => EditShippingOption::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
