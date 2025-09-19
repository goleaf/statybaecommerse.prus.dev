<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Slider;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class RecentSlidersWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected int|string|array $columnSpanFull = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Slider::query()
                    ->with('translations')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                ImageColumn::make('image')
                    ->circular()
                    ->size(40),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('button_text')
                    ->label(__('translations.button_text'))
                    ->limit(20),
                TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('translations.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                // EditAction::make()
                //     ->url(function(Slider $record): string {
                //         try {
                //             return route('filament.admin.resources.sliders.edit', $record);
                //         } catch (\Exception $e) {
                //             return '#';
                //         }
                //     }),
                // DeleteAction::make(),
            ])
            ->paginated(false);
    }
}
