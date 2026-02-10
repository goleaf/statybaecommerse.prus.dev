<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderShipping;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'shipping';

    protected static ?string $recordTitleAttribute = 'tracking_number';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.enum_values.types.shipping_status');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('method')
                    ->label(__('messages.shipping_method'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('tracking_number')
                    ->label(__('messages.tracking_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}
