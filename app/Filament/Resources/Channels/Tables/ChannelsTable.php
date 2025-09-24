<?php

namespace App\Filament\Resources\Channels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ChannelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.channels.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('admin.channels.code'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('type')
                    ->label(__('admin.channels.type'))
                    ->colors([
                        'success' => 'web',
                        'info' => 'mobile',
                        'warning' => 'api',
                        'danger' => 'pos',
                    ]),
                TextColumn::make('url')
                    ->label(__('admin.channels.url'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                IconColumn::make('is_enabled')
                    ->label(__('admin.channels.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('admin.channels.is_default'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('admin.channels.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.channels.type'))
                    ->options([
                        'web' => __('admin.channels.types.web'),
                        'mobile' => __('admin.channels.types.mobile'),
                        'api' => __('admin.channels.types.api'),
                        'pos' => __('admin.channels.types.pos'),
                    ]),
                TernaryFilter::make('is_enabled')
                    ->label(__('admin.channels.is_enabled')),
                TernaryFilter::make('is_default')
                    ->label(__('admin.channels.is_default')),
                TernaryFilter::make('is_active')
                    ->label(__('admin.channels.is_active')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
