<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PriceResource extends Resource
{
    protected static ?string $model = Price::class;

    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SchemaSection::make(__('admin.prices.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('admin.prices.product'))
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('amount')
                                    ->label(__('admin.prices.amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->required(),
                            ]),
                    ]),
                SchemaSection::make(__('admin.prices.audit_section'))
                    ->schema([
                        Textarea::make('audit_reason')
                            ->label(__('admin.prices.audit_reason'))
                            ->helperText(__('admin.prices.audit_reason_help'))
                            ->visible(fn (?Price $record): bool => (bool) ($record?->exists))
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('admin.prices.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('admin.prices.amount'))
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('admin.prices.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'edit' => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
