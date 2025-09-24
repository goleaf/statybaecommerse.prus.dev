<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews';

    protected static ?string $modelLabel = 'Review';

    protected static ?string $pluralModelLabel = 'Reviews';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('reviews.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('rating')
                    ->label(__('reviews.fields.rating'))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('title')
                    ->label(__('reviews.fields.title'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('comment')
                    ->label(__('reviews.fields.comment'))
                    ->limit(100)
                    ->html(),
                IconColumn::make('is_approved')
                    ->label(__('reviews.fields.is_approved'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('reviews.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
