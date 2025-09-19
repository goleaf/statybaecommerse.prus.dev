<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\RelationManagers;

use App\Models\DiscountRedemption;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class RedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemptions';

    protected static ?string $title = 'Discount Redemptions';

    protected static ?string $modelLabel = 'Redemption';

    protected static ?string $pluralModelLabel = 'Redemptions';

    public function form(Form $schema): Form
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('order_id')
                    ->label('Order ID')
                    ->numeric()
                    ->helperText('Associated order ID'),
                Forms\Components\TextInput::make('discount_amount')
                    ->label('Discount Amount')
                    ->numeric()
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('original_amount')
                    ->label('Original Amount')
                    ->numeric()
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('final_amount')
                    ->label('Final Amount')
                    ->numeric()
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('discount_code_id')
                    ->label('Discount Code ID')
                    ->numeric()
                    ->helperText('Specific code used (if any)'),
                Forms\Components\DateTimePicker::make('redeemed_at')
                    ->label('Redeemed At')
                    ->default(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('original_amount')
                    ->label('Original Amount')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Discount Amount')
                    ->money('EUR')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('final_amount')
                    ->label('Final Amount')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_code.code')
                    ->label('Code Used')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('recent')
                    ->query(fn(Builder $query): Builder => $query->where('redeemed_at', '>=', now()->subDays(7)))
                    ->label('Last 7 Days'),
                Tables\Filters\Filter::make('this_month')
                    ->query(fn(Builder $query): Builder => $query->whereMonth('redeemed_at', now()->month))
                    ->label('This Month'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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


