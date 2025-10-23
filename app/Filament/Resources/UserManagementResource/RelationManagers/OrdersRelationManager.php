<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\RelationManagers;


use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class OrdersRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    public function form(Schema $schema): Schema   
    {
        return $schema
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

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
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
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
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