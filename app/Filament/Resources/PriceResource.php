<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class PriceResource extends BaseResource
{
    protected static ?string $model = Price::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-euro';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $activeNavigationItem = ProductResource::class;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 15;

    public static function getNavigationLabel(): string
    {
        return __('admin.prices.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.prices.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.prices.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.prices.basic_information'))
                ->description(__('admin.prices.basic_information_description'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            MorphToSelect::make('priceable')
                                ->label(__('messages.Product'))
                                ->types([
                                    MorphToSelect\Type::make(Product::class)
                                        ->titleAttribute('name')
                                        ->label(__('messages.Product'))
                                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->getFullDisplayName()),
                                    MorphToSelect\Type::make(ProductVariant::class)
                                        ->titleAttribute('name')
                                        ->label(__('messages.Variant'))
                                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->getVariantDisplayName()),
                                ])
                                ->required()
                                ->searchable()
                                ->preload(),
                            Select::make('currency_id')
                                ->label(__('messages.currency'))
                                ->relationship('currency', 'code')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('amount')
                                ->label(__('messages.amount') !== 'messages.amount' ? __('messages.amount') : 'Amount')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->step(0.0001),
                            TextInput::make('cost_amount')
                                ->label(__('messages.cost_amount') !== 'messages.cost_amount' ? __('messages.cost_amount') : 'Cost Amount')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.0001),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('type')
                                ->label(__('messages.Type'))
                                ->placeholder('default, sale, wholesale, etc.'),
                            Toggle::make('is_enabled')
                                ->label(__('messages.enabled'))
                                ->default(true),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make(__('admin.prices.validity'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            DateTimePicker::make('starts_at')
                                ->label(__('admin.prices.valid_from')),
                            DateTimePicker::make('ends_at')
                                ->label(__('admin.prices.valid_until')),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priceable_type')
                    ->label(__('messages.Type'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('priceable.name')
                    ->label(__('messages.Name'))
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->priceable instanceof ProductVariant) {
                            return $record->priceable->display_name;
                        }
                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('messages.currency'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('messages.amount') !== 'messages.amount' ? __('messages.amount') : 'Amount')
                    ->money(fn (Price $record) => $record->currency?->code ?? 'EUR')
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('messages.Type'))
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('messages.enabled'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('admin.prices.valid_from'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('admin.prices.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('admin.prices.no_expiry')),
                TextColumn::make('created_at')
                    ->label(__('admin.prices.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('messages.enabled')),
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label(__('messages.Type'))
                    ->options(fn () => Price::query()->whereNotNull('type')->distinct()->pluck('type', 'type')->toArray()),
                \Filament\Tables\Filters\SelectFilter::make('currency_id')
                    ->label(__('messages.currency'))
                    ->relationship('currency', 'code'),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\PriceResource\RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'edit'   => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
