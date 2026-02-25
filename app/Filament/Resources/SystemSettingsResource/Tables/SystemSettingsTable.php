<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingsResource\Tables;

use App\Filament\Resources\SystemSettingsResource;
use App\Models\SystemSetting;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SystemSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (SystemSetting $record): string => SystemSettingsResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('key')
                    ->label(__('messages.key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('messages.value'))
                    ->limit(50)
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : $state),
                TextColumn::make('category.name')
                    ->label(__('messages.category'))
                    ->sortable(),
                TextColumn::make('group')
                    ->label(__('messages.group'))
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label(__('messages.is_public'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_encrypted')
                    ->label(__('messages.is_encrypted'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label(__('messages.is_required'))
                    ->boolean()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('messages.is_active'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('messages.type'))
                    ->options([
                        'string'  => 'String',
                        'integer' => 'Integer',
                        'boolean' => 'Boolean',
                        'json'    => 'JSON',
                    ]),
                SelectFilter::make('category_id')
                    ->label(__('messages.category'))
                    ->relationship('category', 'name'),
                SelectFilter::make('group')
                    ->label(__('messages.group')),
                SelectFilter::make('is_public')
                    ->label(__('messages.is_public'))
                    ->options([
                        '1' => __('messages.yes'),
                        '0' => __('messages.no'),
                    ]),
                SelectFilter::make('is_encrypted')
                    ->label(__('messages.is_encrypted'))
                    ->options([
                        '1' => __('messages.yes'),
                        '0' => __('messages.no'),
                    ]),
                SelectFilter::make('is_required')
                    ->label(__('messages.is_required'))
                    ->options([
                        '1' => __('messages.yes'),
                        '0' => __('messages.no'),
                    ]),
                SelectFilter::make('is_active')
                    ->label(__('messages.is_active'))
                    ->options([
                        '1' => __('messages.active'),
                        '0' => __('messages.inactive'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('reset_to_default')
                    ->label(__('messages.reset_to_default'))
                    ->icon(Heroicon::ArrowPath)
                    ->requiresConfirmation()
                    ->action(fn (SystemSetting $record) => $record->update(['value' => $record->default_value])),
                Action::make('duplicate')
                    ->label(__('messages.duplicate'))
                    ->icon(Heroicon::DocumentDuplicate)
                    ->action(function (SystemSetting $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->key = $record->key . '_copy';
                        $newRecord->name = $record->name . ' (Copy)';
                        $newRecord->save();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_settings')
                        ->label(__('messages.export'))
                        ->icon(Heroicon::ArrowDownTray)
                        ->action(fn (Collection $records) => /* export logic */ null),
                    BulkAction::make('clear_cache')
                        ->label(__('messages.clear_cache'))
                        ->icon(Heroicon::Trash)
                        ->action(fn (Collection $records) => $records->each->clearInstanceCache()),
                ]),
            ]);
    }
}
