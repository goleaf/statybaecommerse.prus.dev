<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Filament\Resources\PriceResource;
use App\Models\Price;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.prices.plural_model_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Price $record): string => PriceResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('priceable.name')
                    ->label(__('messages.product'))
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('messages.amount'))
                    ->money(fn (Price $record) => $record->currency?->code ?? 'EUR')
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('messages.Type'))
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn (Price $record): string => PriceResource::getUrl('edit', ['record' => $record])),
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
