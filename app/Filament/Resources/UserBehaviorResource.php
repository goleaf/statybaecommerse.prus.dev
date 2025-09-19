<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserBehaviorResource\Pages;
use App\Models\UserBehavior;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class UserBehaviorResource extends Resource
{
    protected static ?string $model = UserBehavior::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('session_id')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

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
                    ->maxLength(500),

                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->maxLength(45),

                Forms\Components\KeyValue::make('metadata')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Created At')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('session_id')
                    ->label('Session')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('behavior_type')
                    ->colors([
                        'primary' => 'view',
                        'success' => 'click',
                        'warning' => 'add_to_cart',
                        'danger' => 'remove_from_cart',
                        'info' => 'purchase',
                        'secondary' => 'search',
                    ]),

                Tables\Columns\TextColumn::make('referrer')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('behavior_type')
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
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUserBehaviors::route('/'),
            'create' => Pages\CreateUserBehavior::route('/create'),
            'edit' => Pages\EditUserBehavior::route('/{record}/edit'),
        ];
    }
}
