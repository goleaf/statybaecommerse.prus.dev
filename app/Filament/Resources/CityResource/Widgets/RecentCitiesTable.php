<?php

declare(strict_types=1);

namespace App\Filament\Resources\CityResource\Widgets;

use App\Models\City;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class RecentCitiesTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Cities';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                City::query()
                    ->with(['country', 'region', 'zone'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('cities.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('cities.code'))
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('cities.country'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('population')
                    ->label(__('cities.population'))
                    ->numeric()
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state) : '-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_capital')
                    ->label(__('cities.is_capital'))
                    ->boolean()
                    ->trueIcon('heroicon-o-crown')
                    ->falseIcon('heroicon-o-building-office')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('cities.is_enabled'))
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('cities.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('cities.view'))
                    ->url(fn (City $record): string => route('filament.admin.resources.cities.view', $record))
                    ->icon('heroicon-o-eye'),
                Tables\Actions\Action::make('edit')
                    ->label(__('cities.edit'))
                    ->url(fn (City $record): string => route('filament.admin.resources.cities.edit', $record))
                    ->icon('heroicon-o-pencil'),
            ])
            ->paginated(false);
    }
}
