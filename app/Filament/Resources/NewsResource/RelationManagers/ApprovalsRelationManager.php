<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

final class ApprovalsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'approvals';

    protected static ?string $recordTitleAttribute = 'decision';

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->columns([
                TextColumn::make('decided_at')
                    ->label(__('news.approvals.decided_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('news.approvals.reviewer'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('decision')
                    ->label(__('news.approvals.decision'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('news.approvals.decisions.' . $state))
                    ->colors([
                        'success' => fn (string $state): bool => $state === 'approved',
                        'warning' => fn (string $state): bool => $state === 'returned',
                    ]),
                TextColumn::make('notes')
                    ->label(__('news.approvals.notes'))
                    ->limit(60)
                    ->tooltip(fn (?string $state): ?string => $state),
            ])
            ->defaultSort('decided_at', 'desc')
            ->paginated(false)
            ->headerActions([])
            ->actions([])
            ->emptyStateHeading(__('news.approvals.empty_heading'))
            ->emptyStateDescription(__('news.approvals.empty_description'));
    }
}