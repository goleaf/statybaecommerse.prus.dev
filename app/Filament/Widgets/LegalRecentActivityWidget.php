<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Legal;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LegalRecentActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Legal Documents';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Legal::query()
                    ->with(['translations'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('admin.legal.key'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.legal.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'privacy_policy' => 'danger',
                        'terms_of_use' => 'warning',
                        'refund_policy' => 'info',
                        'shipping_policy' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Legal::getTypes()[$state] ?? $state),

                Tables\Columns\TextColumn::make('translations.title')
                    ->label(__('admin.legal.title'))
                    ->getStateUsing(function (Legal $record): string {
                        $translation = $record->translations()
                            ->where('locale', app()->getLocale())
                            ->first();
                        
                        return $translation?->title ?? $record->key;
                    })
                    ->searchable()
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.legal.is_enabled'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('admin.legal.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'disabled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.legal.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('admin.legal.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Legal $record): string => route('legal.show', $record->key))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
