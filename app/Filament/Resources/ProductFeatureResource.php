<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductFeatureResource\Pages;
use App\Models\ProductFeature;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup as TablesBulkActionGroup;
use Filament\Tables\Actions\DeleteAction as TablesDeleteAction;
use Filament\Tables\Actions\DeleteBulkAction as TablesDeleteBulkAction;
use Filament\Tables\Actions\EditAction as TablesEditAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class ProductFeatureResource extends Resource
{
    /**
     * @var array<string, string>
     */
    private const FEATURE_TYPE_OPTIONS = [
        'specification' => 'Specification',
        'benefit' => 'Benefit',
        'feature' => 'Feature',
        'technical' => 'Technical',
        'performance' => 'Performance',
    ];

    protected static ?string $model = ProductFeature::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Products;

    protected static ?int $navigationSort = 17;

    public static function form(Schema $form): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('product_id')
                ->label('Product')
                ->relationship('product', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('feature_type')
                ->label('Feature Type')
                ->options(self::getFeatureTypeOptions())
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('feature_key')
                ->label('Feature Key')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('feature_value')
                ->label('Feature Value')
                ->required()
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('weight')
                ->label('Weight')
                ->numeric()
                ->step(0.0001)
                ->default(0)
                ->minValue(0),
            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->inline(false)
                ->helperText('Inactive features will be hidden from customer-facing experiences.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('feature_type')
                    ->label('Feature Type')
                    ->enum(self::getFeatureTypeOptions())
                    ->colors([
                        'primary' => 'specification',
                        'success' => 'benefit',
                        'warning' => 'feature',
                        'info'    => 'technical',
                        'danger'  => 'performance',
                    ]),
                Tables\Columns\TextColumn::make('feature_key')
                    ->label('Feature Key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('feature_value')
                    ->label('Feature Value')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('weight')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('feature_type')
                    ->options(self::FEATURE_TYPE_OPTIONS),
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->actions([
                TablesEditAction::make(),
                TablesDeleteAction::make(),
            ])
            ->bulkActions([
                TablesBulkActionGroup::make([
                    TablesDeleteBulkAction::make(),
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
            'index'  => Pages\ListProductFeatures::route('/'),
            'create' => Pages\CreateProductFeature::route('/create'),
            'edit'   => Pages\EditProductFeature::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getFeatureTypeOptions(): array
    {
        return [
            'specification' => 'Specification',
            'benefit'       => 'Benefit',
            'feature'       => 'Feature',
            'technical'     => 'Technical',
            'performance'   => 'Performance',
        ];
    }
}