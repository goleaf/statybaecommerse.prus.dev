<?php

declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Models\Slider;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class SliderComparisonTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->subDays(30);
        $endDate = $this->pageFilters['endDate'] ?? now();
        $sliderId = $this->pageFilters['sliderId'] ?? null;
        $status = $this->pageFilters['status'] ?? 'all';

        $query = Slider::query()
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($sliderId, fn (Builder $query) => $query->where('id', $sliderId))
            ->when($status !== 'all', fn (Builder $query) => $query->where('is_active', $status === 'active'));

        return $table
            ->query($query)
            ->columns([
                ImageColumn::make('image')
                    ->label('Preview')
                    ->getStateUsing(function (Slider $record): ?string {
                        $media = $record->getFirstMedia('slider_images');

                        return $media ? $media->getUrl() : null;
                    })
                    ->defaultImageUrl('/images/placeholder-slider.png')
                    ->size(80)
                    ->square(),
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),
                TextColumn::make('performance_score')
                    ->label('Performance Score')
                    ->getStateUsing(function (Slider $record): int {
                        $score = 0;

                        // Base score for being active
                        if ($record->is_active) {
                            $score += 20;
                        }

                        // Media score
                        if ($record->hasMedia('slider_images')) {
                            $score += 15;
                        }
                        if ($record->hasMedia('slider_backgrounds')) {
                            $score += 10;
                        }

                        // Content score
                        if (! empty($record->description)) {
                            $score += 10;
                        }
                        if (! empty($record->button_text) && ! empty($record->button_url)) {
                            $score += 15;
                        }

                        // Design score
                        if (! empty($record->background_color) || ! empty($record->text_color)) {
                            $score += 10;
                        }

                        // Recency score
                        $daysSinceCreated = $record->created_at->diffInDays(now());
                        if ($daysSinceCreated <= 7) {
                            $score += 10;
                        } elseif ($daysSinceCreated <= 30) {
                            $score += 5;
                        }

                        // Settings score
                        if (! empty($record->settings)) {
                            $score += 5;
                        }

                        return min(100, $score);
                    })
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    }),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('features_count')
                    ->label('Features')
                    ->getStateUsing(function (Slider $record): int {
                        $count = 0;
                        if ($record->hasMedia('slider_images')) {
                            $count++;
                        }
                        if ($record->hasMedia('slider_backgrounds')) {
                            $count++;
                        }
                        if (! empty($record->button_text) && ! empty($record->button_url)) {
                            $count++;
                        }
                        if (! empty($record->background_color) || ! empty($record->text_color)) {
                            $count++;
                        }
                        if (! empty($record->description)) {
                            $count++;
                        }
                        if (! empty($record->settings)) {
                            $count++;
                        }

                        return $count;
                    })
                    ->badge()
                    ->color('info'),
                TextColumn::make('engagement_rate')
                    ->label('Engagement')
                    ->getStateUsing(function (Slider $record): string {
                        // Simulate engagement rate based on features
                        $baseRate = $record->is_active ? 75 : 25;
                        $featureBonus = 0;

                        if ($record->hasMedia('slider_images')) {
                            $featureBonus += 10;
                        }
                        if (! empty($record->button_text) && ! empty($record->button_url)) {
                            $featureBonus += 15;
                        }
                        if (! empty($record->description)) {
                            $featureBonus += 5;
                        }

                        $rate = min(100, $baseRate + $featureBonus);

                        return $rate.'%';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        (int) str_replace('%', '', $state) >= 80 => 'success',
                        (int) str_replace('%', '', $state) >= 60 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Slider $record): string => route('filament.admin.pages.slider-management'))
                    ->openUrlInNewTab(),
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->url(fn (Slider $record): string => route('filament.admin.resources.sliders.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('performance_score', 'desc')
            ->paginated([10, 25, 50]);
    }
}
