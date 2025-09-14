<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * RecentCampaignsWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class RecentCampaignsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Campaigns';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Campaign::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('campaigns.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('campaigns.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'info',
                        'sms' => 'warning',
                        'push' => 'success',
                        'banner' => 'primary',
                        'popup' => 'secondary',
                        'social' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('campaigns.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'scheduled' => 'warning',
                        'paused' => 'secondary',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('campaigns.fields.start_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_views')
                    ->label(__('campaigns.fields.total_views'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_clicks')
                    ->label(__('campaigns.fields.total_clicks'))
                    ->numeric()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Campaign $record): string => route('filament.admin.resources.campaigns.view', $record))
                    ->icon('heroicon-m-eye')
                    ->openUrlInNewTab(),
            ]);
    }
}
