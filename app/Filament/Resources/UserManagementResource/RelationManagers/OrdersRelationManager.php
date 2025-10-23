<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\StatusScope;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class OrdersRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Orders';

    public function form(Schema $schema): Schema
    {
        return $form->schema([
            TextInput::make('number')
                ->label(__('orders.fields.number'))
                ->disabled()
                ->dehydrated(false),
            Select::make('status')
                ->label(__('orders.fields.status'))
                ->options(OrderStatus::getOptions())
                ->required(),
            TextInput::make('total')
                ->label(__('orders.fields.total'))
                ->numeric()
                ->prefix(fn (?\App\Models\Order $record): string => $record?->currency ?? 'EUR')
                ->disabled()
                ->dehydrated(false),
            Textarea::make('notes')
                ->label(__('orders.fields.notes'))
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
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
                    ->icon(fn (string $state): string => OrderStatus::from($state)->getIcon())
                    ->color(fn (string $state): string => OrderStatus::from($state)->getColor())
                    ->formatStateUsing(fn (string $state): string => OrderStatus::from($state)->getLabel()),
                TextColumn::make('total')
                    ->label(__('orders.fields.total'))
                    ->money(fn (\App\Models\Order $record): string => $record->currency ?? 'EUR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('orders.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('orders.fields.status'))
                    ->options(OrderStatus::getOptions()),
                TrashedFilter::make(),
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
