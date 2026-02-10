<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Tables;

use App\Enums\OrganizationType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OrganizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('messages.description'))
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(static function ($state): string {
                        if ($state instanceof OrganizationType) {
                            return $state->label();
                        }

                        return OrganizationType::tryFrom((string) $state)?->label() ?? (string) $state;
                    }),
                IconColumn::make('is_active')
                    ->label(__('messages.active'))
                    ->boolean(),
                TextColumn::make('users_count')
                    ->label(__('messages.users'))
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('projects_count')
                    ->label(__('messages.projects'))
                    ->counts('projects')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('messages.active')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
