<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingOptionResource\Pages;
use App\Models\ShippingOption;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * ShippingOptionResource
 *
 * Filament v4 resource for ShippingOption management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ShippingOptionResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Settings';
    }

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-truck';
    }

    protected static ?string $model = ShippingOption::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('admin.shipping_options.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.shipping_options.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.shipping_options.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.shipping_options.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.shipping_options.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', \Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->label(__('admin.shipping_options.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ShippingOption::class, 'slug', ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                                TextInput::make('carrier_name')
                                    ->label(__('admin.shipping_options.carrier_name'))
                                    ->maxLength(255),
                                Select::make('service_type')
                                    ->label(__('admin.shipping_options.service_type'))
                                    ->options([
                                        'standard' => __('admin.shipping_options.service_types.standard'),
                                        'express' => __('admin.shipping_options.service_types.express'),
                                        'overnight' => __('admin.shipping_options.service_types.overnight'),
                                        'economy' => __('admin.shipping_options.service_types.economy'),
                                    ])
                                    ->required()
                                    ->default('standard'),
                            ]),
                        Textarea::make('description')
                            ->label(__('admin.shipping_options.description'))
                            ->maxLength(1000)
                            ->rows(3),
                    ]),
                SchemaSection::make(__('admin.shipping_options.pricing'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label(__('admin.shipping_options.price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01)
                                    ->required(),
                                Select::make('currency_code')
                                    ->label(__('admin.shipping_options.currency_code'))
                                    ->options([
                                        'EUR' => 'EUR',
                                        'USD' => 'USD',
                                        'GBP' => 'GBP',
                                    ])
                                    ->default('EUR')
                                    ->required(),
                            ]),
                    ]),
                SchemaSection::make(__('admin.shipping_options.constraints'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('min_weight')
                                    ->label(__('admin.shipping_options.min_weight'))
                                    ->numeric()
                                    ->suffix('kg')
                                    ->step(0.01),
                                TextInput::make('max_weight')
                                    ->label(__('admin.shipping_options.max_weight'))
                                    ->numeric()
                                    ->suffix('kg')
                                    ->step(0.01),
                                TextInput::make('min_order_amount')
                                    ->label(__('admin.shipping_options.min_order_amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('max_order_amount')
                                    ->label(__('admin.shipping_options.max_order_amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                    ]),
                SchemaSection::make(__('admin.shipping_options.delivery'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('estimated_days_min')
                                    ->label(__('admin.shipping_options.estimated_days_min'))
                                    ->numeric()
                                    ->suffix(__('admin.shipping_options.days'))
                                    ->minValue(1),
                                TextInput::make('estimated_days_max')
                                    ->label(__('admin.shipping_options.estimated_days_max'))
                                    ->numeric()
                                    ->suffix(__('admin.shipping_options.days'))
                                    ->minValue(1),
                            ]),
                    ]),
                SchemaSection::make(__('admin.shipping_options.status'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label(__('admin.shipping_options.is_enabled'))
                                    ->default(true),
                                Toggle::make('is_default')
                                    ->label(__('admin.shipping_options.is_default'))
                                    ->default(false),
                                TextInput::make('sort_order')
                                    ->label(__('admin.shipping_options.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.shipping_options.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('carrier_name')
                    ->label(__('admin.shipping_options.carrier_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_type')
                    ->label(__('admin.shipping_options.service_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'success',
                        'express' => 'warning',
                        'overnight' => 'danger',
                        'economy' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('price')
                    ->label(__('admin.shipping_options.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('estimated_days_min')
                    ->label(__('admin.shipping_options.estimated_days'))
                    ->formatStateUsing(fn ($record) => $record->estimated_days_min && $record->estimated_days_max
                        ? "{$record->estimated_days_min}-{$record->estimated_days_max} ".__('admin.shipping_options.days')
                        : '-'),
                IconColumn::make('is_enabled')
                    ->label(__('admin.shipping_options.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('admin.shipping_options.is_default'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('service_type')
                    ->label(__('admin.shipping_options.service_type'))
                    ->options([
                        'standard' => __('admin.shipping_options.service_types.standard'),
                        'express' => __('admin.shipping_options.service_types.express'),
                        'overnight' => __('admin.shipping_options.service_types.overnight'),
                        'economy' => __('admin.shipping_options.service_types.economy'),
                    ]),
                TernaryFilter::make('is_enabled')
                    ->label(__('admin.shipping_options.is_enabled')),
                TernaryFilter::make('is_default')
                    ->label(__('admin.shipping_options.is_default')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
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
            'index' => Pages\ListShippingOptions::route('/'),
            'create' => Pages\CreateShippingOption::route('/create'),
            'view' => Pages\ViewShippingOption::route('/{record}'),
            'edit' => Pages\EditShippingOption::route('/{record}/edit'),
        ];
    }
}
