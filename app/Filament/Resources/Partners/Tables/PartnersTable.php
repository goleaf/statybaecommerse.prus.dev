<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\Tables;

use App\Models\Partner;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label(__('messages.logo'))
                    ->collection('logo')
                    ->conversion('logo-sm')
                    ->defaultImageUrl(static fn (Partner $record): ?string => $record->getLogoUrl('sm') ?? $record->getLogoUrl())
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('messages.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_email')
                    ->label(__('messages.email'))
                    ->searchable(),
                TextColumn::make('contact_phone')
                    ->label(__('messages.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('partnerTier.name')
                    ->label(__('messages.partner_tiers'))
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label(__('messages.users'))
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label(__('messages.orders'))
                    ->counts('orders')
                    ->sortable(),
                TextColumn::make('discount_rate')
                    ->label(__('messages.discount_rate'))
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('commission_rate')
                    ->label(__('messages.commission_rate'))
                    ->numeric(2)
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('messages.enabled'))
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_enabled')
                    ->label(__('messages.enabled')),
                SelectFilter::make('tier_id')
                    ->relationship('partnerTier', 'name')
                    ->label(__('messages.partner_tiers')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
