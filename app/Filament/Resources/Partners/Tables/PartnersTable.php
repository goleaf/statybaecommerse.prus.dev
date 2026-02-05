<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\Tables;

use App\Models\Partner;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
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
                TextColumn::make('partnerTier.name')
                    ->label(__('messages.partner_tiers'))
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
