<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\DiscountRedemptionResource;
use App\Models\DiscountRedemption;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DiscountRedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'discountRedemptions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('discount_id')
                    ->relationship('discount', 'name')
                    ->required(),
                Select::make('order_id')
                    ->relationship('order', 'number')
                    ->required(),
                TextInput::make('amount_saved')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                DateTimePicker::make('redeemed_at')
                    ->default(now()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->recordTitleAttribute('amount_saved')
            ->columns([
                TextColumn::make('discount.name')
                    ->label(__('admin.labels.discount'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order.number')
                    ->label(__('admin.labels.order'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount_saved')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('redeemed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('create')
                    ->icon('heroicon-m-plus')
                    ->url(fn (): string => DiscountRedemptionResource::getUrl('create', [
                        'user_id'  => $this->getOwnerRecord()->getKey(),
                        'redirect' => request()->fullUrl(),
                    ])),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (DiscountRedemption $record): string => DiscountRedemptionResource::getUrl('view', [
                        'record'   => $record,
                        'redirect' => request()->fullUrl(),
                    ])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (DiscountRedemption $record): string => DiscountRedemptionResource::getUrl('edit', [
                        'record'   => $record,
                        'redirect' => request()->fullUrl(),
                    ])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

