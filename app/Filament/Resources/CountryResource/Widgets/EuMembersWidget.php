<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class EuMembersWidget extends BaseWidget
{
    protected static ?string $heading = 'EU Member Countries';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->query(
                Country::query()
                    ->where('is_eu_member', true)
                    ->orderBy('translated_name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('translated_name')
                    ->label(__('countries.fields.translated_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cca2')
                    ->label(__('countries.fields.cca2'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('region')
                    ->label(__('countries.fields.region'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label(__('countries.fields.currency_code'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('vat_rate')
                    ->label(__('countries.fields.vat_rate'))
                    ->formatStateUsing(static fn (?string $state): string => $state !== null
                        ? number_format((float) $state, 2) . '%'
                        : 'N/A'),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading(__('countries.fields.is_eu_member'))
            ->emptyStateDescription(__('attributes.unknown'));
    }
}
