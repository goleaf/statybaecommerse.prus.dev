<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\RelationManagers;

use App\Forms\Components\Flatpickr;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Support\Filament\Components\Flatpickr;

final class RedemptionsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'redemptions';

    public function form(Form $form): Form|array
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('Customer'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('order_id')
                    ->label(__('Order'))
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount_saved')
                    ->label(__('Amount Saved'))
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                Flatpickr::makeDateTime('redeemed_at')
                    ->label(__('Redeemed At'))
                    ->default(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table|array
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Customer'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('Guest')),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label(__('Order'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('N/A')),
                Tables\Columns\TextColumn::make('amount_saved')
                    ->label(__('Amount Saved'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->label(__('Redeemed At'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->defaultSort('created_at', 'desc');
    }
}
