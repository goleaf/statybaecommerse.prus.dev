<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\RelationManagers;

use App\Models\VariantInventory;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VariantInventoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'variantInventories';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.inventory_management.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.sku')
                    ->label(__('messages.sku'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock')
                    ->label(__('messages.quantity'))
                    ->sortable(),
                TextColumn::make('reserved')
                    ->label(__('admin.inventory.reserved_quantity'))
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}
