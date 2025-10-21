<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class PartnersRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'partners';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('price_lists.relation_managers.partners.title');
    }

    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('price_lists.partner'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('price_lists.code'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contact_email')
                    ->label(__('price_lists.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label(__('price_lists.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label(__('price_lists.commission_rate'))
                    ->formatStateUsing(fn (?string $state): string => $state !== null ? number_format((float) $state, 2) . '%' : '-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('price_lists.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('price_lists.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make()->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}