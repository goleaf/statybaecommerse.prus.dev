<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\VariantAnalyticsResource\Pages;
use App\Models\VariantAnalytics;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;
use BackedEnum;
final class VariantAnalyticsResource extends Resource
{
    protected static ?string $model = VariantAnalytics::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Analytics;
    protected static ?int $navigationSort = 2;
    public static function form(Schema $schema): Schema
    {
        return $schema
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
                Forms\Components\TextInput::make('add_to_cart')
                Forms\Components\TextInput::make('purchases')
                Forms\Components\TextInput::make('revenue')
                    ->step(0.0001),
                Forms\Components\TextInput::make('conversion_rate')
                    ->maxValue(100)
                    ->step(0.0001)
                    ->suffix('%'),
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Variant')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                Tables\Columns\TextColumn::make('views')
                Tables\Columns\TextColumn::make('clicks')
                Tables\Columns\TextColumn::make('add_to_cart')
                    ->label('Add to Cart')
                Tables\Columns\TextColumn::make('purchases')
                Tables\Columns\TextColumn::make('revenue')
                    ->money('EUR')
                Tables\Columns\TextColumn::make('conversion_rate')
                    ->label('Conversion Rate')
                    ->formatStateUsing(fn($state) => $state ? $state . '%' : '0%')
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('variant_id')
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
                                fn($query, $date) => $query->whereDate('date', '>=', $date),
                            )
                                $data['date_until'],
                                fn($query, $date) => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('date', 'desc');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListVariantAnalytics::route('/'),
            'create' => Pages\CreateVariantAnalytics::route('/create'),
            'edit' => Pages\EditVariantAnalytics::route('/{record}/edit'),
}
