<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use Filament\Actions\EditAction;
use Filament\Forms;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;

final class OrdersRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'shipped'    => 'Shipped',
                        'delivered'  => 'Delivered',
                        'cancelled'  => 'Cancelled',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(1000),
            ]);
    }

    public function table(Table $table): Table|array
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                TextColumn::make('number')
                    ->label(__('orders.fields.number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
                ActiveScope::class,
                StatusScope::class,
            ]));
    }
}
