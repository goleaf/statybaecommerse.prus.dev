<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class CountriesWithVatWidget extends BaseWidget
{
    protected static ?string $heading = 'Countries Requiring VAT';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->query(
                Country::query()
                    ->where('requires_vat', true)
                    ->orderByDesc('vat_rate')
            )
            ->columns([
                Tables\Columns\TextColumn::make('translated_name')
                    ->label(__('countries.fields.translated_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cca2')
                    ->label(__('countries.fields.cca2'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('vat_rate')
                    ->label(__('countries.fields.vat_rate'))
                    ->formatStateUsing(static fn (?string $state): string => $state !== null
                        ? number_format((float) $state, 2) . '%'
                        : 'N/A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label(__('countries.fields.currency_code'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_eu_member')
                    ->label(__('countries.fields.is_eu_member'))
                    ->boolean(),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading(__('countries.fields.requires_vat'))
            ->emptyStateDescription(__('attributes.unknown'));
    }
}