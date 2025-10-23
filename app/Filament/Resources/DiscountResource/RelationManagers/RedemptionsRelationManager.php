<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\RelationManagers;

use App\Models\DiscountRedemption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class RedemptionsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'redemptions';

    protected static ?string $title = 'Discount Redemptions';

    protected static ?string $modelLabel = 'Redemption';

    protected static ?string $pluralModelLabel = 'Redemptions';

    public function form(Schema $schema): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('code_id')
                    ->label('Discount Code')
                    ->relationship('code', 'code')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('order_id')
                    ->label('Order')
                    ->relationship('order', 'number')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount_saved')
                    ->label('Amount Saved')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('currency_code')
                    ->label('Currency')
                    ->maxLength(3)
                    ->default('EUR')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'redeemed' => 'Redeemed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                        'expired' => 'Expired',
                    ])
                    ->default('pending'),
                Forms\Components\DateTimePicker::make('redeemed_at')
                    ->label('Redeemed At')
                    ->default(now())
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code.code')
            ->columns([
                Tables\Columns\TextColumn::make('code.code')
                    ->label('Code')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.number')
                    ->label('Order')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('amount_saved')
                    ->label('Amount Saved')
                    ->sortable()
                    ->formatStateUsing(fn ($state, DiscountRedemption $record): string => $state === null
                        ? '-' : number_format((float) $state, 2).' '.($record->currency_code ?? 'EUR')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'redeemed',
                        'secondary' => 'refunded',
                        'danger' => 'cancelled',
                        'gray' => 'expired',
                    ]),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'   => 'Pending',
                        'redeemed'  => 'Redeemed',
                        'expired'   => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('currency_code')
                    ->label('Currency')
                    ->options([
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                        'GBP' => 'GBP',
                    ]),
                Filter::make('has_order')
                    ->label('Has Order')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('order_id')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
}
