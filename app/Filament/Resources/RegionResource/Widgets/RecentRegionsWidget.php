<?php

declare (strict_types=1);
namespace App\Filament\Resources\RegionResource\Widgets;

use App\Models\Region;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
/**
 * RecentRegionsWidget
 * 
 * Filament v4 resource for RecentRegionsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @property int|string|array $columnSpan
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class RecentRegionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Regions';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(Region::query()->latest()->limit(5))->columns([Tables\Columns\TextColumn::make('name')->label(__('regions.name'))->searchable()->sortable(), Tables\Columns\TextColumn::make('code')->label(__('regions.code'))->searchable(), Tables\Columns\TextColumn::make('country.name')->label(__('regions.country'))->sortable(), Tables\Columns\IconColumn::make('is_enabled')->label(__('regions.is_enabled'))->boolean(), Tables\Columns\TextColumn::make('created_at')->label(__('regions.created_at'))->dateTime()->sortable()])->actions([Tables\Actions\ViewAction::make(), Tables\Actions\EditAction::make()]);
    }
}