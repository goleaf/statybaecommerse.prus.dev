<?php

declare(strict_types=1);

namespace App\Filament\Resources\RegionResource\Widgets;

use App\Models\Region;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final /**
 * RecentRegionsWidget
 * 
 * Filament resource for admin panel management.
 */
class RecentRegionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Regions';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Region::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('regions.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('regions.code'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('regions.country'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('regions.is_enabled'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('regions.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
}
