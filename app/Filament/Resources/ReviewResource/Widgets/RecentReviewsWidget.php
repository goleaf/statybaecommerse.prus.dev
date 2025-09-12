<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Widgets;

use App\Models\Review;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class RecentReviewsWidget extends BaseWidget
{
    protected static ?string $heading = 'admin.reviews.widgets.recent_reviews';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Review::query()
                    ->with(['user', 'product'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.reviews.fields.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('admin.reviews.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('rating')
                    ->label(__('admin.reviews.fields.rating'))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state.' ⭐'),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.reviews.fields.title'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label(__('admin.reviews.fields.is_approved'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('admin.reviews.fields.is_featured'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.reviews.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Review $record): string => route('filament.admin.resources.reviews.view', $record)),
            ])
            ->paginated(false);
    }
}
