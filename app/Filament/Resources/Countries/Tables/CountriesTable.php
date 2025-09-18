<?php

declare(strict_types=1);

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup as TableBulkActionGroup;
use Filament\Notifications\Notification;

/**
 * CountriesTable
 * 
 * Filament table configuration for Country management with comprehensive columns, filters, and actions.
 */
final class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Flag and Basic Info
                ImageColumn::make('flag')
                    ->label(__('countries.flag'))
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl('/images/default-flag.png'),
                
                TextColumn::make('name')
                    ->label(__('countries.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('name_official')
                    ->label(__('countries.name_official'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Country Codes
                TextColumn::make('cca2')
                    ->label(__('countries.cca2'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('cca3')
                    ->label(__('countries.cca3'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                
                // Geographic Info
                TextColumn::make('region')
                    ->label(__('countries.region'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('subregion')
                    ->label(__('countries.subregion'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Currency Info
                TextColumn::make('currency_code')
                    ->label(__('countries.currency_code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('vat_rate')
                    ->label(__('countries.vat_rate'))
                    ->numeric()
                    ->sortable()
                    ->suffix('%')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Status Columns
                IconColumn::make('is_active')
                    ->label(__('countries.is_active'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_enabled')
                    ->label(__('countries.is_enabled'))
                    ->boolean()
                    ->sortable(),
                
                IconColumn::make('is_eu_member')
                    ->label(__('countries.is_eu_member'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('requires_vat')
                    ->label(__('countries.requires_vat'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Contact Info
                TextColumn::make('phone_calling_code')
                    ->label(__('countries.phone_calling_code'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('timezone')
                    ->label(__('countries.timezone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Coordinates
                TextColumn::make('latitude')
                    ->label(__('countries.latitude'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('longitude')
                    ->label(__('countries.longitude'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Sort Order
                TextColumn::make('sort_order')
                    ->label(__('countries.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                // Timestamps
                TextColumn::make('created_at')
                    ->label(__('countries.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('countries.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                
                TernaryFilter::make('is_active')
                    ->label(__('countries.is_active'))
                    ->boolean(),
                
                TernaryFilter::make('is_enabled')
                    ->label(__('countries.is_enabled'))
                    ->boolean(),
                
                TernaryFilter::make('is_eu_member')
                    ->label(__('countries.is_eu_member'))
                    ->boolean(),
                
                TernaryFilter::make('requires_vat')
                    ->label(__('countries.requires_vat'))
                    ->boolean(),
                
                SelectFilter::make('region')
                    ->label(__('countries.region'))
                    ->options(function () {
                        return \App\Models\Country::distinct()
                            ->pluck('region', 'region')
                            ->filter()
                            ->sort()
                            ->toArray();
                    }),
                
                SelectFilter::make('currency_code')
                    ->label(__('countries.currency_code'))
                    ->options(function () {
                        return \App\Models\Country::distinct()
                            ->pluck('currency_code', 'currency_code')
                            ->filter()
                            ->sort()
                            ->toArray();
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                TableBulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('countries.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('countries.activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    
                    BulkAction::make('deactivate')
                        ->label(__('countries.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('countries.deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                    
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
