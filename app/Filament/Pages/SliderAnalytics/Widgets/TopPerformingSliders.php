<?php declare(strict_types=1);

namespace App\Filament\Pages\SliderAnalytics\Widgets;

use App\Models\Slider;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class TopPerformingSliders extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top Performing Sliders';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        $startDate = $this->pageFilters['startDate'] ?? now()->subDays(30);
        $endDate = $this->pageFilters['endDate'] ?? now();
        $sliderId = $this->pageFilters['sliderId'] ?? null;
        $status = $this->pageFilters['status'] ?? 'all';

        $query = Slider::query()
            ->when($startDate, fn(Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->when($sliderId, fn(Builder $query) => $query->where('id', $sliderId))
            ->when($status !== 'all', fn(Builder $query) => $query->where('is_active', $status === 'active'));

        return $table
            ->query($query)
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->getStateUsing(function (Slider $record): ?string {
                        $media = $record->getFirstMedia('slider_images');
                        return $media ? $media->getUrl() : null;
                    })
                    ->defaultImageUrl('/images/placeholder-slider.png')
                    ->size(60)
                    ->circular(),
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->weight('bold'),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('features')
                    ->label('Features')
                    ->getStateUsing(function (Slider $record): string {
                        $features = [];

                        if ($record->hasMedia('slider_images')) {
                            $features[] = 'Image';
                        }

                        if ($record->hasMedia('slider_backgrounds')) {
                            $features[] = 'Background';
                        }

                        if (!empty($record->button_text) && !empty($record->button_url)) {
                            $features[] = 'Button';
                        }

                        if (!empty($record->background_color) || !empty($record->text_color)) {
                            $features[] = 'Colors';
                        }

                        return implode(', ', $features) ?: 'Basic';
                    })
                    ->badge()
                    ->color('warning'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->paginated(false);
    }
}
