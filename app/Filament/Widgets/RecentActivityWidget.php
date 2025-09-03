<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Activitylog\Models\Activity;

final class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected static ?string $heading = 'Recent Activity';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()
                    ->with(['causer', 'subject'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'product' => 'success',
                        'order' => 'warning',
                        'user' => 'info',
                        'category' => 'primary',
                        'brand' => 'secondary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label(__('Subject'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label(__('User'))
                    ->default(__('System'))
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Time'))
                    ->since()
                    ->tooltip(fn ($state) => $state->format('Y-m-d H:i:s')),
            ])
            ->actions([
                Action::make('view_details')
                    ->label(__('Details'))
                    ->icon('heroicon-m-eye')
                    ->modalContent(function (Activity $record) {
                        return view('filament.activity-log.view-modal', [
                            'activity' => $record,
                            'properties' => $record->properties->toArray(),
                        ]);
                    })
                    ->modalWidth('2xl'),
            ])
            ->poll('30s')
            ->paginated(false);
    }
    
    public static function getHeading(): string
    {
        return __('Recent Activity');
    }
}
