<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivityWidget extends BaseWidget
{
    protected string $view = 'filament.widgets.recent-activity-widget';

    protected static ?string $heading = 'Recent Activity';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        // Get recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'type' => 'order',
                    'description' => 'New order #' . $order->id . ' from ' . ($order->user->name ?? 'Guest'),
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            });

        // Get recent users
        $recentUsers = User::latest()
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user',
                    'description' => 'New user registered: ' . $user->name,
                    'amount' => null,
                    'status' => 'active',
                    'created_at' => $user->created_at,
                ];
            });

        // Get recent reviews
        $recentReviews = Review::with('product', 'user')
            ->where('is_approved', true)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'type' => 'review',
                    'description' => 'New review for ' . $review->product->name . ' by ' . $review->user->name,
                    'amount' => $review->rating,
                    'status' => 'approved',
                    'created_at' => $review->created_at,
                ];
            });

        // Get recent campaigns
        $recentCampaigns = Campaign::latest()
            ->limit(5)
            ->get()
            ->map(function ($campaign) {
                return [
                    'type' => 'campaign',
                    'description' => 'New campaign: ' . $campaign->name,
                    'amount' => null,
                    'status' => $campaign->status,
                    'created_at' => $campaign->created_at,
                ];
            });

        // Combine all activities
        $allActivities = $recentOrders
            ->concat($recentUsers)
            ->concat($recentReviews)
            ->concat($recentCampaigns)
            ->sortByDesc('created_at')
            ->take(20);

        // Create a fake query builder for the table
        return collect($allActivities)->toQuery();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('type')
                ->label(__('translations.type'))
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'order' => 'success',
                    'user' => 'info',
                    'review' => 'warning',
                    'campaign' => 'primary',
                    default => 'gray',
                })
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'order' => __('translations.order'),
                    'user' => __('translations.user'),
                    'review' => __('translations.review'),
                    'campaign' => __('translations.campaign'),
                    default => $state,
                }),
            TextColumn::make('description')
                ->label(__('translations.description'))
                ->searchable()
                ->sortable(),
            TextColumn::make('amount')
                ->label(__('translations.amount'))
                ->formatStateUsing(function ($state, $record) {
                    if ($record['type'] === 'order') {
                        return \Illuminate\Support\Number::currency($state, 'EUR');
                    } elseif ($record['type'] === 'review') {
                        return $state . '/5 ⭐';
                    }
                    return '-';
                })
                ->alignEnd(),
            BadgeColumn::make('status')
                ->label(__('translations.status'))
                ->color(fn(string $state): string => match ($state) {
                    'completed', 'delivered', 'approved', 'active' => 'success',
                    'pending', 'processing' => 'warning',
                    'cancelled', 'rejected' => 'danger',
                    default => 'gray',
                }),
            TextColumn::make('created_at')
                ->label(__('translations.created_at'))
                ->dateTime()
                ->sortable()
                ->since(),
        ];
    }

    public function getTableRecordsPerPage(): int
    {
        return 10;
    }

    protected function getTablePollingInterval(): ?string
    {
        return '30s';
    }
}
