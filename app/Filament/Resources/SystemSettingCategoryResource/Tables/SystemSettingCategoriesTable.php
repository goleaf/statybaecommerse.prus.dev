<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Tables;

use App\Models\SystemSettingCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SystemSettingCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('-')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Parent')
                    ->options(static fn (): array => SystemSettingCategory::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                Action::make('duplicate')
                    ->action(static function (SystemSettingCategory $record): void {
                        $copy = $record->replicate(['slug']);
                        $copy->name = $record->name . ' (Copy)';
                        $copy->slug = Str::slug($record->slug . '-copy');
                        $copy->save();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('activate')
                    ->action(static fn (Collection $records): int => $records
                        ->each
                        ->update(['is_active' => true])
                        ->count()),
                BulkAction::make('deactivate')
                    ->action(static fn (Collection $records): int => $records
                        ->each
                        ->update(['is_active' => false])
                        ->count()),
                DeleteBulkAction::make(),
            ]);
    }
}

