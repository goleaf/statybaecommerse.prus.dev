<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\UserBehaviorResource\Pages;
use App\Models\UserBehavior;
use Filament\Schemas\Schema;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use BackedEnum;
use App\Enums\NavigationGroup;
final class UserBehaviorResource extends Resource
{
    protected static ?string $model = UserBehavior::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    // protected static $navigationGroup = NavigationGroup::System;
    protected static ?int $navigationSort = 1;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('session_id')
                    ->maxLength(255),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                Forms\Components\Select::make('behavior_type')
                    ->options([
                        'view' => 'View',
                        'click' => 'Click',
                        'add_to_cart' => 'Add to Cart',
                        'remove_from_cart' => 'Remove from Cart',
                        'purchase' => 'Purchase',
                        'search' => 'Search',
                        'filter' => 'Filter',
                        'sort' => 'Sort',
                        'wishlist' => 'Wishlist',
                        'share' => 'Share',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('referrer')
                    ->maxLength(500),
                Forms\Components\TextInput::make('user_agent')
                    ->label('User Agent')
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->maxLength(45),
                Forms\Components\KeyValue::make('metadata')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Created At')
            ]);
    }
    public static function table(Table $table): Table
    {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
            'index' => Pages\ListUserBehaviors::route('/'),
            'create' => Pages\CreateUserBehavior::route('/create'),
            'edit' => Pages\EditUserBehavior::route('/{record}/edit'),
}
