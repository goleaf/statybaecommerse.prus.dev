<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantAnalyticsResource\Pages;
use App\Models\VariantAnalytics;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class VariantAnalyticsResource extends Resource
{
    protected static ?string $model = VariantAnalytics::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('variant_id')
                    ->relationship('variant', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\DatePicker::make('date')
                    ->required(),

                Forms\Components\TextInput::make('views')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Forms\Components\TextInput::make('clicks')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Forms\Components\TextInput::make('add_to_cart')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Forms\Components\TextInput::make('purchases')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Forms\Components\TextInput::make('revenue')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.0001),

                Forms\Components\TextInput::make('conversion_rate')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.0001)
                    ->suffix('%'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Variant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('clicks')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('add_to_cart')
                    ->label('Add to Cart')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('purchases')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('revenue')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('conversion_rate')
                    ->label('Conversion Rate')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : '0%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Date From'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Date Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn ($query, $date) => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn ($query, $date) => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
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
            'index' => Pages\ListVariantAnalytics::route('/'),
            'create' => Pages\CreateVariantAnalytics::route('/create'),
            'edit' => Pages\EditVariantAnalytics::route('/{record}/edit'),
        ];
    }
}
