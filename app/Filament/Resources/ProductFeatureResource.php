<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\ProductFeatureResource\Pages;
use App\Models\ProductFeature;
use Filament\Schemas\Schema;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use BackedEnum;
use App\Enums\NavigationGroup;
final class ProductFeatureResource extends Resource
{
    protected static ?string $model = ProductFeature::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';
    // protected static $navigationGroup = NavigationGroup::Products;
    protected static ?int $navigationSort = 17;
    public static function form(Schema $schema): Schema
    {
        return $schema
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
                    ->searchable(),
                Forms\Components\TextInput::make('feature_key')
                    ->label('Feature Key')
                    ->maxLength(255),
                Forms\Components\Textarea::make('feature_value')
                    ->label('Feature Value')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->step(0.0001)
                    ->default(0)
                    ->minValue(0),
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
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
                Tables\Columns\TextColumn::make('feature_value')
                    ->limit(50)
                Tables\Columns\TextColumn::make('weight')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                Tables\Columns\TextColumn::make('updated_at')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('feature_type')
                Tables\Filters\SelectFilter::make('product_id')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('weight', 'desc');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListProductFeatures::route('/'),
            'create' => Pages\CreateProductFeature::route('/create'),
            'edit' => Pages\EditProductFeature::route('/{record}/edit'),
}
