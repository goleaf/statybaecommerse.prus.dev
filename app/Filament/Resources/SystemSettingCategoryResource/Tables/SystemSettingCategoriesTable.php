<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Tables;

use App\Filament\Resources\SystemSettingCategoryResource;
use App\Models\SystemSettingCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SystemSettingCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (SystemSettingCategory $record): string => SystemSettingCategoryResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('messages.parent'))
                    ->sortable(),
                TextColumn::make('icon')
                    ->label(__('messages.icon'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('messages.color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label(__('messages.is_active'))
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label(__('messages.parent'))
                    ->relationship('parent', 'name'),
                SelectFilter::make('is_active')
                    ->label(__('messages.is_active'))
                    ->options([
                        '1' => __('messages.active'),
                        '0' => __('messages.inactive'),
                    ]),
            ])
            ->actions([
                Action::make('duplicate')
                    ->label(__('messages.duplicate'))
                    ->icon(Heroicon::DocumentDuplicate)
                    ->action(function (SystemSettingCategory $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->name = $record->name . ' (Copy)';
                        $newRecord->slug = $record->slug . '-copy';
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('messages.activate'))
                        ->icon(Heroicon::CheckCircle)
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label(__('messages.deactivate'))
                        ->icon(Heroicon::XCircle)
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
