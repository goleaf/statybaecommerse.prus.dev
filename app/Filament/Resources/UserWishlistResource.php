<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\UserWishlistResource\Pages;
use App\Models\UserWishlist;
use Filament\Schemas\Schema;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use BackedEnum;
use App\Enums\NavigationGroup;
final class UserWishlistResource extends Resource
{
    protected static ?string $model = UserWishlist::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';
    // protected static $navigationGroup = NavigationGroup::System;
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
    {record}/edit'),
}
