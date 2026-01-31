<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Tables;

use App\Models\Slider;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SlidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('translations.slide_image'))
                    ->collection('slider_images'),
                TextColumn::make('title')
                    ->label(__('messages.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->sortable(),
                TextColumn::make('priority')
                    ->label(__('translations.priority'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high'   => 'warning',
                        'normal' => 'info',
                        'low'    => 'gray',
                        default  => 'gray',
                    })
                    ->sortable(),
                ColorColumn::make('background_color')
                    ->label(__('translations.background_color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('text_color')
                    ->label(__('translations.text_color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('translations.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('messages.featured'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('translations.is_active')),
                TernaryFilter::make('is_featured')
                    ->label(__('messages.featured')),
            ])
            ->actions([
                Action::make('toggleSlider')
                    ->label(fn (Slider $record): string => $record->is_active ? __('translations.deactivate') : __('translations.activate'))
                    ->color(fn (Slider $record): string => $record->is_active ? 'danger' : 'success')
                    ->action(function (Slider $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active
                                ? __('translations.slider_activated')
                                : __('translations.slider_deactivated')
                            )
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
                ReplicateAction::make()
                    ->beforeReplicaSaved(function (Slider $replica): void {
                        $replica->title = $replica->title . ' (Copy)';
                        $replica->sort_order = Slider::max('sort_order') + 1;
                    })
                    ->successNotificationTitle(__('translations.slider_duplicated')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
