<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class CountryDetailsWidget extends BaseWidget
{
    protected static ?string $heading = 'Country Details';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->query(
                Country::query()
                    ->latest('updated_at')
                    ->limit(10)
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
                Tables\Columns\TextColumn::make('subregion')
                    ->label(__('countries.fields.subregion'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency_code')
                    ->label(__('countries.fields.currency_code'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_calling_code')
                    ->label(__('countries.fields.phone_calling_code')),
                Tables\Columns\IconColumn::make('requires_vat')
                    ->label(__('countries.fields.requires_vat'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('countries.fields.is_active'))
                    ->boolean(),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('countries.models.countries'))
            ->emptyStateDescription(__('attributes.unknown'));
    }
}
