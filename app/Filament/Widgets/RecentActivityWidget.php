<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Order;
use App\Models\Product;
use App\Models\Slider;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Configure the widget table to meet the Filament v4 return type contract.
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(__('ui.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Order'          => 'success',
                        'Product'        => 'primary',
                        'User'           => 'info',
                        'Campaign'       => 'danger',
                        'News'           => 'secondary',
                        'Slider'         => 'gray',
                        'System Setting' => 'slate',
                        default          => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('ui.description'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('ui.subject'))
                    ->searchable()
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 100 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('ui.user'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active', 'completed', 'published', 'approved' => 'success',
                        'pending', 'draft', 'processing' => 'warning',
                        'inactive', 'cancelled', 'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('ui.system'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('ui.time'))
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }

    public function getTableQuery(): Builder
    {
        // Get recent orders - SQLite compatible
        $recentOrders = Order::selectRaw("
            'Order' as type,
            'Order #' || id as title,
            'Total: €' || printf('%.2f', total) || ' - Status: ' || status as description,
            status,
            created_at,
            updated_at
        ")->where('created_at', '>=', Carbon::now()->subDays(7));

        // Get recent products - SQLite compatible
        $recentProducts = Product::selectRaw("
            'Product' as type,
            name as title,
            'SKU: ' || COALESCE(sku, 'N/A') || ' - Price: €' || printf('%.2f', price) as description,
            CASE 
                WHEN is_visible = 1 THEN 'active'
                ELSE 'inactive'
            END as status,
            created_at,
            updated_at
        ")->where('created_at', '>=', Carbon::now()->subDays(7));

        // Get recent users - SQLite compatible
        $recentUsers = User::selectRaw("
            'User' as type,
            first_name || ' ' || last_name as title,
            'Email: ' || email as description,
            CASE 
                WHEN is_active = 1 THEN 'active'
                ELSE 'inactive'
            END as status,
            created_at,
            updated_at
        ")->where('created_at', '>=', Carbon::now()->subDays(7));

        // Get recent campaigns - SQLite compatible
        $recentCampaigns = Campaign::selectRaw("
            'Campaign' as type,
            name as title,
            'Status: ' || status || ' - Budget: €' || printf('%.2f', COALESCE(budget_limit, 0)) as description,
            status,
            created_at,
            updated_at
        ")->where('created_at', '>=', Carbon::now()->subDays(7));

        // Get recent sliders - SQLite compatible
        $recentSliders = Slider::selectRaw("
            'Slider' as type,
            title,
            'Order: ' || sort_order || ' - ' || CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END as description,
            CASE 
                WHEN is_active = 1 THEN 'active'
                ELSE 'inactive'
            END as status,
            created_at,
            updated_at
        ")->where('created_at', '>=', Carbon::now()->subDays(7));

        // Get recent system settings - SQLite compatible
        $recentSettings = SystemSetting::selectRaw("
            'System Setting' as type,
            'Setting: ' || `key` as title,
            'Value: ' || COALESCE(substr(value, 1, 50), 'NULL') || ' - Type: ' || type as description,
            CASE 
                WHEN is_public = 1 THEN 'active'
                ELSE 'inactive'
            END as status,
            created_at,
            updated_at
        ")->where('created_at', '>=', Carbon::now()->subDays(7));

        // Union all queries
        return $recentOrders
            ->union($recentProducts)
            ->union($recentUsers)
            ->union($recentCampaigns)
            ->union($recentSliders)
            ->union($recentSettings)
            ->orderBy('created_at', 'desc');
    }

    public function getHeading(): string
    {
        return static::$heading ?? 'Recent Activity Dashboard';
    }

    public function getColumnSpan(): int|string|array
    {
        return $this->columnSpan;
    }
}
