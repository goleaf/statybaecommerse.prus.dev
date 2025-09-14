<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriberResource\Widgets;

use App\Models\Subscriber;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class RecentSubscribersWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Subscribers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscriber::query()
                    ->recent(7)
                    ->latest('subscribed_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->getStateUsing(fn (Subscriber $record): string => $record->full_name)
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->colors([
                        'primary' => 'website',
                        'secondary' => 'admin',
                        'success' => 'import',
                        'warning' => 'api',
                        'info' => 'social',
                    ]),

                Tables\Columns\TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->getStateUsing(fn (Subscriber $record): bool => $record->is_active)
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Subscriber $record): string => route('filament.admin.resources.subscribers.edit', $record)),
            ])
            ->poll('30s');
    }
}
