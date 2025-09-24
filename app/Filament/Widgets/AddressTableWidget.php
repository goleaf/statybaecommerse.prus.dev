<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Address;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * AddressTableWidget
 *
 * Widget displaying recent addresses in a table format
 */
final class AddressTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Addresses';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    /**
     * Get table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Address::query()
                    ->with(['user', 'country'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('translations.user'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('display_name')
                    ->label(__('translations.full_name'))
                    ->searchable(['first_name', 'last_name', 'company_name']),
                TextColumn::make('type')
                    ->label(__('translations.type'))
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        \App\Enums\AddressType::SHIPPING => 'primary',
                        \App\Enums\AddressType::BILLING => 'success',
                        \App\Enums\AddressType::HOME => 'warning',
                        \App\Enums\AddressType::WORK => 'info',
                        \App\Enums\AddressType::OTHER => 'secondary',
                        default => 'gray',
                    }),
                TextColumn::make('city')
                    ->label(__('translations.city'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('translations.country'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label(__('translations.is_active'))
                    ->formatStateUsing(fn ($state) => $state ? __('translations.yes') : __('translations.no'))
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
