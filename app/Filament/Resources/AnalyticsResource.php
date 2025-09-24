<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsResource\Pages;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class AnalyticsResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static UnitEnum|string|null $navigationGroup = 'Analytics';

    public static function getNavigationLabel(): string
    {
        return __('analytics.analytics');
    }

    public static function getModelLabel(): string
    {
        return __('analytics.analytics');
    }

    public static function getPluralModelLabel(): string
    {
        return __('analytics.analytics');
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = (int) Order::query()->where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->with(['user']))
            ->columns([
                TextColumn::make('order_date')->label('order_date')->date()->toggleable(),
                TextColumn::make('user.name')->label('user.name')->toggleable(),
                TextColumn::make('items_count')->label('items_count')->getStateUsing(fn(Order $record): int => method_exists($record, 'items') ? (int) $record->items()->count() : 0)->toggleable(),
                TextColumn::make('total')->label('total')->money('EUR')->toggleable(),
                TextColumn::make('status')->label('status')->badge()->toggleable(),
                TextColumn::make('created_at')->label('created_at')->dateTime()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'pending',
                        'completed' => 'completed',
                        'cancelled' => 'cancelled',
                    ]),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from'),
                        \Filament\Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn(Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn(Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
                Filter::make('high_value')
                    ->query(fn(Builder $query): Builder => $query->where('total', '>=', 500)),
                Filter::make('this_month')
                    ->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\AnalyticsDashboard::route('/'),
        ];
    }
}
