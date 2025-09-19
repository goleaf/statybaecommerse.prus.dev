<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\PartnerTierResource\Pages;
use App\Models\PartnerTier;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;
use BackedEnum;
final class PartnerTierResource extends Resource
{
    protected static ?string $model = PartnerTier::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Users;
    protected static ?int $navigationSort = 2;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->maxLength(255)
                            ->unique(PartnerTier::class, 'code', ignoreRecord: true),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Financial Settings')
                        Forms\Components\TextInput::make('discount_rate')
                            ->label('Discount Rate (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.0001),
                        Forms\Components\TextInput::make('commission_rate')
                            ->label('Commission Rate (%)')
                        Forms\Components\TextInput::make('minimum_order_value')
                            ->label('Minimum Order Value (€)')
                            ->step(0.01),
                Forms\Components\Section::make('Benefits')
                        Forms\Components\Repeater::make('benefits')
                            ->schema([
                                Forms\Components\TextInput::make('benefit')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['benefit'] ?? null),
                    ]),
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                Tables\Columns\TextColumn::make('discount_rate')
                    ->label('Discount Rate')
                    ->formatStateUsing(fn($state) => $state ? $state . '%' : '-')
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission Rate')
                Tables\Columns\TextColumn::make('minimum_order_value')
                    ->label('Min Order Value')
                    ->money('EUR')
                Tables\Columns\TextColumn::make('partners_count')
                    ->label(NavigationGroup::Users)
                    ->counts('partners')
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Enabled Only'),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('name');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListPartnerTiers::route('/'),
            'create' => Pages\CreatePartnerTier::route('/create'),
            'edit' => Pages\EditPartnerTier::route('/{record}/edit'),
}
