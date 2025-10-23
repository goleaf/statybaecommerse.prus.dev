<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ApprovalsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'approvals';

    protected static ?string $recordTitleAttribute = 'decision';

    public function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                TextColumn::make('decided_at')
                    ->label(__('posts.approvals.decided_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('posts.approvals.reviewer'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('decision')
                    ->label(__('posts.approvals.decision'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('posts.approvals.decisions.' . $state))
                    ->colors([
                        'success' => fn (string $state): bool => $state === 'approved',
                        'warning' => fn (string $state): bool => $state === 'returned',
                    ]),
                TextColumn::make('notes')
                    ->label(__('posts.approvals.notes'))
                    ->limit(60)
                    ->tooltip(fn (?string $state): ?string => $state),
            ])
            ->defaultSort('decided_at', 'desc')
            ->paginated(false)
            ->headerActions([])
            ->actions([])
            ->emptyStateHeading(__('posts.approvals.empty_heading'))
            ->emptyStateDescription(__('posts.approvals.empty_description'));
    }
}
