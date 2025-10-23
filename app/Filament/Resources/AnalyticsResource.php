<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AnalyticsResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class AnalyticsResource extends Resource
{
    use HasNav;

    protected static ?string $model = Order::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    /**
     * Preserve the typed navigation group union to keep enum-backed grouping working across PHP upgrades.
     */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Analytics;

    public static function getNavigationLabel(): string
    {
        // Surface the dashboard-specific label to match the refreshed navigation copy.
        return __('analytics.analytics_dashboard');
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

    public static function form(Form $form): Form
    {
        // Keep the dashboard read-only by returning the base form configuration untouched.
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            // Preload frequently accessed relationships so table metrics do not suffer from N+1 queries.
            ->modifyQueryUsing(
                static fn (Builder $query): Builder => $query->with([
                    'user:id,name,email',
                    'items:id,order_id',
                    'channel:id,name',
                ])
            )
            ->columns([
                TextColumn::make('order_date')->label('order_date')->date()->toggleable(),
                TextColumn::make('user.name')->label('user.name')->toggleable(),
                TextColumn::make('items_count')->label('items_count')->getStateUsing(fn (Order $record): int => method_exists($record, 'items') ? (int) $record->items()->count() : 0)->toggleable(),
                TextColumn::make('total')->label('total')->money('EUR')->toggleable(),
                TextColumn::make('status')->label('status')->badge()->toggleable(),
                TextColumn::make('created_at')->label('created_at')->dateTime()->toggleable(),
                TextColumn::make('updated_at')->label('updated_at')->dateTime()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('analytics.status'))
                    ->options([
                        'pending'    => __('analytics.pending'),
                        'processing' => __('analytics.processing'),
                        'completed'  => __('analytics.completed'),
                        'cancelled'  => __('analytics.cancelled'),
                        'shipped'    => __('analytics.shipped'),
                        'delivered'  => __('analytics.delivered'),
                        'refunded'   => __('analytics.refunded'),
                    ])
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label(__('analytics.customer'))
                    ->searchable(),
                SelectFilter::make('channel_id')
                    ->relationship('channel', 'name')
                    ->label(__('analytics.channel'))
                    ->searchable(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('analytics.from_date'))
                            ->placeholder(__('analytics.from_date')),
                        DatePicker::make('created_until')
                            ->label(__('analytics.until_date'))
                            ->placeholder(__('analytics.until_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
                Filter::make('high_value')
                    ->query(fn (Builder $query): Builder => $query->where('total', '>=', 500)),
                Filter::make('this_month')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
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
