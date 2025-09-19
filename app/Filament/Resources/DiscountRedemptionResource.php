<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountRedemptionResource\Pages;
use App\Models\DiscountRedemption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class DiscountRedemptionResource extends Resource
{
    protected static ?string $model = DiscountRedemption::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('discount_id')
                    ->relationship('discount', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('code_id')
                    ->relationship('discountCode', 'code')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'id')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('amount_saved')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01),

                Forms\Components\Select::make('currency_code')
                    ->options([
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                        'GBP' => 'GBP',
                    ])
                    ->required()
                    ->default('EUR'),

                Forms\Components\DateTimePicker::make('redeemed_at')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'redeemed' => 'Redeemed',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('pending'),

                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->maxLength(45),

                Forms\Components\TextInput::make('user_agent')
                    ->label('User Agent')
                    ->maxLength(500),

                Forms\Components\Select::make('created_by')
                    ->relationship('creator', 'name')
                    ->searchable(),

                Forms\Components\Select::make('updated_by')
                    ->relationship('updater', 'name')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('discount.name')
                    ->label('Discount')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('discountCode.code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_saved')
                    ->label('Amount Saved')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency_code')
                    ->label('Currency')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'redeemed',
                        'danger' => 'expired',
                        'secondary' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'redeemed' => 'Redeemed',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('discount_id')
                    ->relationship('discount', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('redeemed_at')
                    ->form([
                        Forms\Components\DatePicker::make('redeemed_from')
                            ->label('Redeemed From'),
                        Forms\Components\DatePicker::make('redeemed_until')
                            ->label('Redeemed Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['redeemed_from'],
                                fn ($query, $date) => $query->whereDate('redeemed_at', '>=', $date),
                            )
                            ->when(
                                $data['redeemed_until'],
                                fn ($query, $date) => $query->whereDate('redeemed_at', '<=', $date),
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
            ->defaultSort('redeemed_at', 'desc');
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
            'index' => Pages\ListDiscountRedemptions::route('/'),
            'create' => Pages\CreateDiscountRedemption::route('/create'),
            'edit' => Pages\EditDiscountRedemption::route('/{record}/edit'),
        ];
    }
}
