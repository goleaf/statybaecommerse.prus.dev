<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductFeatureResource\Pages;
use App\Models\ProductFeature;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class ProductFeatureResource extends Resource
{
    protected static ?string $model = ProductFeature::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 17;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('feature_type')
                    ->options([
                        'specification' => 'Specification',
                        'benefit' => 'Benefit',
                        'feature' => 'Feature',
                        'technical' => 'Technical',
                        'performance' => 'Performance',
                    ])
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('feature_key')
                    ->label('Feature Key')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('feature_value')
                    ->label('Feature Value')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->step(0.0001)
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('feature_type')
                    ->colors([
                        'primary' => 'specification',
                        'success' => 'benefit',
                        'warning' => 'feature',
                        'info' => 'technical',
                        'danger' => 'performance',
                    ]),

                Tables\Columns\TextColumn::make('feature_key')
                    ->label('Feature Key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('feature_value')
                    ->label('Feature Value')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('feature_type')
                    ->options([
                        'specification' => 'Specification',
                        'benefit' => 'Benefit',
                        'feature' => 'Feature',
                        'technical' => 'Technical',
                        'performance' => 'Performance',
                    ]),

                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('weight', 'desc');
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
            'index' => Pages\ListProductFeatures::route('/'),
            'create' => Pages\CreateProductFeature::route('/create'),
            'edit' => Pages\EditProductFeature::route('/{record}/edit'),
        ];
    }
}
