<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\UserWishlistResource\Pages;
use App\Models\UserWishlist;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;
use BackedEnum;
final class UserWishlistResource extends Resource
{
    protected static ?string $model = UserWishlist::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Users;
    protected static ?int $navigationSort = 4;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_public')
                    ->label('Public Wishlist')
                    ->default(false),
                Forms\Components\Toggle::make('is_default')
                    ->label('Default Wishlist')
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                Tables\Columns\TextColumn::make('updated_at')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public Only'),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Only'),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('created_at', 'desc');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListUserWishlists::route('/'),
            'create' => Pages\CreateUserWishlist::route('/create'),
            'edit' => Pages\EditUserWishlist::route('/{record}/edit'),
}
