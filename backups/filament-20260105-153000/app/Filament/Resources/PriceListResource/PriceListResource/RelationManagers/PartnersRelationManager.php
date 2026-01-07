<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Actions\AttachAction;
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
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('price_lists.partner'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('discount_codes.code'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tier.name')
                    ->label(__('discount_conditions.partner_tier'))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('contact_email')
                    ->label(__('customers.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('contact_phone')
                    ->label(__('customers.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
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
            ->filters([
                Tables\Filters\SelectFilter::make('tier')
                    ->label(__('discount_conditions.partner_tier'))
                    ->relationship('tier', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('price_lists.is_active'))
                    ->boolean()
                    ->trueLabel(__('price_lists.active_only'))
                    ->falseLabel(__('price_lists.inactive_only'))
                    ->native(false),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                AttachAction::make()->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
