<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use App\Support\Filament\Components\Flatpickr;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

final class RedemptionsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'redemptions';

    public function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form
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

    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
