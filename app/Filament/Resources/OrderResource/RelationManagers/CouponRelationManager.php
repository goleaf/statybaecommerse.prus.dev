<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CouponRelationManager extends RelationManager
{
    protected static string $relationship = 'coupon';

    protected static ?string $recordTitleAttribute = 'code';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.coupons');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('messages.code'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('messages.value'))
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}
