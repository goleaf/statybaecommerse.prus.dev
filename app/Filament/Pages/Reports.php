<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DatabaseDateService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
final class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = \App\Enums\NavigationGroup::Reports;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'reports-dashboard';

    public ?string $dateRange = 'last_30_days';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?string $reportType = 'sales';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.reports');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.reports');
    }

    public function mount(): void
    {
        $this->setDateRange();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('reportType')
                            ->label(__('admin.fields.report_type'))
                            ->options([
                                'sales' => __('admin.reports.sales_report'),
                                'products' => __('admin.reports.product_performance'),
                                'customers' => __('admin.reports.customer_analysis'),
                                'inventory' => __('admin.reports.inventory_report'),
                            ])
                            ->default('sales')
                            ->live(),
                        Forms\Components\Select::make('dateRange')
                            ->label(__('admin.fields.date_range'))
                            ->options([
                                'today' => __('admin.date_ranges.today'),
                                'yesterday' => __('admin.date_ranges.yesterday'),
                                'last_7_days' => __('admin.date_ranges.last_7_days'),
                                'last_30_days' => __('admin.date_ranges.last_30_days'),
                                'last_90_days' => __('admin.date_ranges.last_90_days'),
                                'this_year' => __('admin.date_ranges.this_year'),
                                'custom' => __('admin.date_ranges.custom'),
                            ])
                            ->default('last_30_days')
                            ->live()
                            ->afterStateUpdated(fn() => $this->setDateRange()),
                        Forms\Components\DatePicker::make('startDate')
                            ->label(__('admin.fields.start_date'))
                            ->visible(fn(Forms\Get $get): bool => $get('dateRange') === 'custom'),
                        Forms\Components\DatePicker::make('endDate')
                            ->label(__('admin.fields.end_date'))
                            ->visible(fn(Forms\Get $get): bool => $get('dateRange') === 'custom'),
                    ]),
            ]);
    }

    protected function setDateRange(): void
    {
        match ($this->dateRange) {
            'today' => [
                $this->startDate = now()->startOfDay()->format('Y-m-d'),
                $this->endDate = now()->endOfDay()->format('Y-m-d'),
            ],
            'yesterday' => [
                $this->startDate = now()->subDay()->startOfDay()->format('Y-m-d'),
                $this->endDate = now()->subDay()->endOfDay()->format('Y-m-d'),
            ],
            'last_7_days' => [
                $this->startDate = now()->subDays(7)->format('Y-m-d'),
                $this->endDate = now()->format('Y-m-d'),
            ],
            'last_30_days' => [
                $this->startDate = now()->subDays(30)->format('Y-m-d'),
                $this->endDate = now()->format('Y-m-d'),
            ],
            'last_90_days' => [
                $this->startDate = now()->subDays(90)->format('Y-m-d'),
                $this->endDate = now()->format('Y-m-d'),
            ],
            'this_year' => [
                $this->startDate = now()->startOfYear()->format('Y-m-d'),
                $this->endDate = now()->format('Y-m-d'),
            ],
            default => null,
        };
    }

    public function getSalesData(): array
    {
        $query = Order::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ]);

        $totalRevenue = $query->clone()->paid()->sum('total');
        $totalOrders = $query->clone()->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $dailySales = $query
            ->clone()
            ->select(
                DB::raw(DatabaseDateService::dateExpression('created_at') . ' as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->paid()
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'dailySales' => $dailySales,
        ];
    }

    public function getProductData(): array
    {
        $topProducts = Product::whereHas('orderItems.order', function ($orderQuery) {
            $orderQuery->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])->paid();
        })
            ->withCount(['orderItems' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereBetween('created_at', [
                        Carbon::parse($this->startDate)->startOfDay(),
                        Carbon::parse($this->endDate)->endOfDay(),
                    ])->paid();
                });
            }])
            ->withSum(['orderItems' => function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereBetween('created_at', [
                        Carbon::parse($this->startDate)->startOfDay(),
                        Carbon::parse($this->endDate)->endOfDay(),
                    ])->paid();
                });
            }], 'total')
            ->orderByDesc('order_items_count')
            ->limit(10)
            ->get();

        $lowStockProducts = Product::where('stock_quantity', '<=', 10)
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        return [
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
        ];
    }

    public function getCustomerData(): array
    {
        $newCustomers = User::where('is_admin', false)
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])
            ->count();

        $topCustomers = User::where('is_admin', false)
            ->withSum(['orders' => function ($query) {
                $query->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay(),
                ])->paid();
            }], 'total')
            ->withCount(['orders' => function ($query) {
                $query->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay(),
                ])->paid();
            }])
            ->having('orders_sum_total', '>', 0)
            ->orderByDesc('orders_sum_total')
            ->limit(10)
            ->get();

        return [
            'newCustomers' => $newCustomers,
            'topCustomers' => $topCustomers,
        ];
    }

    public function getInventoryData(): array
    {
        $totalProducts = Product::count();
        $outOfStock = Product::where('stock_quantity', '<=', 0)->count();
        $lowStock = Product::whereBetween('stock_quantity', [1, 10])->count();
        $inStock = Product::where('stock_quantity', '>', 10)->count();

        return [
            'totalProducts' => $totalProducts,
            'outOfStock' => $outOfStock,
            'lowStock' => $lowStock,
            'inStock' => $inStock,
        ];
    }

    protected function getViewData(): array
    {
        return match ($this->reportType) {
            'sales' => $this->getSalesData(),
            'products' => $this->getProductData(),
            'customers' => $this->getCustomerData(),
            'inventory' => $this->getInventoryData(),
            default => [],
        };
    }
}
