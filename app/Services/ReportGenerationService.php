<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Report;
use App\Models\Scopes\UserOwnedScope;
use App\Models\User;
use App\Models\UserProductInteraction;
use App\Models\WishlistItem;
use App\Services\Pricing\PriceCalculator;
use App\Support\Logging\StructuredLogger;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * ReportGenerationService
 *
 * Service class containing ReportGenerationService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class ReportGenerationService
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
        private readonly StructuredLogger $logger,
    ) {}

    /**
     * Handle generateSalesReport functionality with proper error handling.
     */
    public function generateSalesReport(array $filters = []): array
    {
        $timeout = now()->addMinutes(5);
        // 5 minute timeout for sales report generation
        $query = AnalyticsEvent::where('event_type', 'purchase')->with(['user', 'trackable'])->whereNotNull('value');
        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        $salesData = [];
        $totalRevenue = 0;
        $processedCount = 0;
        $query->cursor()->takeUntilTimeout($timeout)->each(function ($event) use (&$salesData, &$totalRevenue, &$processedCount) {
            $processedCount++;
            $value = (float) ($event->value ?? 0);
            $totalRevenue += $value;
            $date = $event->created_at->format('Y-m-d');
            if (! isset($salesData[$date])) {
                $salesData[$date] = ['date' => $date, 'revenue' => 0, 'transactions' => 0, 'users' => collect()];
            }
            $salesData[$date]['revenue'] += $value;
            $salesData[$date]['transactions']++;
            $salesData[$date]['users']->push($event->user_id);
        });
        // Calculate unique users per day
        foreach ($salesData as &$day) {
            $day['unique_users'] = $day['users']->unique()->count();
            unset($day['users']);
        }
        $calculator = app(PriceCalculator::class);
        $totals = $calculator->breakdown($totalRevenue);
        $summary = $totals->toSummary();

        foreach ($salesData as &$day) {
            $day['formatted_revenue'] = app_money_format($day['revenue'], $summary['currency']);
        }
        unset($day);

        Log::info('Sales report generated', ['processed_events' => $processedCount, 'total_revenue' => $totalRevenue, 'days_covered' => count($salesData), 'timeout_reached' => now()->greaterThan($timeout)]);

        return ['summary' => $summary + ['total_revenue' => $summary['total'], 'total_transactions' => $processedCount, 'days_covered' => count($salesData), 'processed_events' => $processedCount], 'daily_data' => array_values($salesData)];
    }

    /**
     * Handle generateProductAnalyticsReport functionality with proper error handling.
     */
    public function generateProductAnalyticsReport(array $filters = []): array
    {
        $operation = $this->logger->operation('report_product_analytics', [
            'filters' => $filters,
        ]);

        try {
            $timeout = now()->addMinutes(10);
            // 10 minute timeout for product analytics
            $query = Product::with(['categories', 'brand', 'media'])->where('is_visible', true);
            // Apply filters
            if (isset($filters['category_id'])) {
                $query->whereHas('categories', function ($q) use ($filters) {
                    $q->where('id', $filters['category_id']);
                });
            }
            if (isset($filters['brand_id'])) {
                $query->where('brand_id', $filters['brand_id']);
            }
            if (isset($filters['price_min'])) {
                $query->where('price', '>=', $filters['price_min']);
            }
            if (isset($filters['price_max'])) {
                $query->where('price', '<=', $filters['price_max']);
            }

            $productSnapshots = [];
            $productIds = [];

            $query->cursor()->takeUntilTimeout($timeout)->each(function ($product) use (&$productSnapshots, &$productIds): void {
                $productIds[] = $product->id;

                // Capture the static attributes we want to return before enriching the
                // payload with behavioural metrics.
                $productSnapshots[$product->id] = [
                    'id'             => $product->id,
                    'name'           => $product->name,
                    'sku'            => $product->sku,
                    'price'          => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'brand'          => $product->brand?->name,
                    'categories'     => $product->categories->pluck('name')->toArray(),
                    'has_images'     => $product->media->isNotEmpty(),
                    'is_featured'    => $product->is_featured,
                    'created_at'     => optional($product->created_at)->format('Y-m-d H:i:s'),
                    'analytics'      => [
                        'view_tracking'     => ['total_views' => 0, 'unique_viewers' => 0],
                        'engagement'        => ['cart_additions' => 0, 'purchases' => 0, 'orders' => 0],
                        'conversion_rates'  => ['cart_to_view_rate' => 0.0, 'purchase_to_view_rate' => 0.0],
                        'wishlist'          => ['wishlist_additions' => 0],
                        'variant_analytics' => ['total_variants' => 0, 'top_variants' => []],
                    ],
                ];
            });

            $processedCount = count($productIds);

            if ($productIds !== []) {
                $interactionMetrics = UserProductInteraction::query()
                    ->selectRaw('product_id, event, SUM(count) as total_count, COUNT(*) as interactions, COUNT(DISTINCT user_id) as unique_users')
                    ->whereIn('product_id', $productIds)
                    ->groupBy('product_id', 'event')
                    ->get()
                    ->groupBy('product_id');

                $orderMetrics = OrderItem::query()
                    ->withoutGlobalScopes([UserOwnedScope::class])
                    ->selectRaw('product_id, SUM(quantity) as purchased_quantity, COUNT(*) as line_items, COUNT(DISTINCT order_id) as orders')
                    ->whereIn('product_id', $productIds)
                    ->groupBy('product_id')
                    ->get()
                    ->keyBy('product_id');

                $wishlistMetrics = WishlistItem::query()
                    ->withoutGlobalScopes([UserOwnedScope::class])
                    ->selectRaw('product_id, COUNT(*) as wishlist_additions')
                    ->whereIn('product_id', $productIds)
                    ->groupBy('product_id')
                    ->pluck('wishlist_additions', 'product_id');

                $variantMetrics = ProductVariant::query()
                    ->select(['id', 'product_id', 'name', 'sku', 'views_count', 'sold_quantity', 'conversion_rate'])
                    ->whereIn('product_id', $productIds)
                    ->get()
                    ->groupBy('product_id');

                $totalViews = 0;
                $totalCartAdditions = 0;
                $totalPurchases = 0;
                $totalWishlistAdditions = 0;
                $cartRateSum = 0.0;
                $purchaseRateSum = 0.0;
                $cartRateProducts = 0;
                $purchaseRateProducts = 0;

                foreach ($productIds as $productId) {
                    $analytics = $productSnapshots[$productId]['analytics'];
                    $interactionGroup = $interactionMetrics->get($productId, collect());

                    $views = (int) ($interactionGroup->firstWhere('event', 'view')['total_count'] ?? 0);
                    $uniqueViewers = (int) ($interactionGroup->firstWhere('event', 'view')['unique_users'] ?? 0);
                    $cartAdditions = (int) ($interactionGroup->firstWhere('event', 'add_to_cart')['total_count'] ?? 0);
                    $wishlistAdditions = (int) ($wishlistMetrics[$productId] ?? 0);

                    $orderStat = $orderMetrics->get($productId);
                    $purchasedQuantity = (int) ($orderStat->purchased_quantity ?? 0);
                    $ordersCount = (int) ($orderStat->orders ?? 0);

                    $analytics['view_tracking'] = [
                        'total_views'    => $views,
                        'unique_viewers' => $uniqueViewers,
                    ];

                    $analytics['engagement'] = [
                        'cart_additions' => $cartAdditions,
                        'purchases'      => $purchasedQuantity,
                        'orders'         => $ordersCount,
                    ];

                    $purchaseRate = 0.0;
                    $cartRate = 0.0;
                    if ($views > 0) {
                        $cartRate = ($cartAdditions / $views) * 100;
                        $purchaseRate = ($purchasedQuantity / $views) * 100;
                        $cartRateSum += $cartRate;
                        $purchaseRateSum += $purchaseRate;
                        $cartRateProducts++;
                        $purchaseRateProducts++;
                    }

                    $analytics['conversion_rates'] = [
                        'cart_to_view_rate'     => round($cartRate, 2),
                        'purchase_to_view_rate' => round($purchaseRate, 2),
                    ];

                    $analytics['wishlist'] = [
                        'wishlist_additions' => $wishlistAdditions,
                    ];

                    $variants = $variantMetrics->get($productId, collect());
                    $analytics['variant_analytics'] = [
                        'total_variants' => $variants->count(),
                        'top_variants'   => $variants
                            ->sortByDesc(fn (ProductVariant $variant): int => (int) ($variant->sold_quantity ?? 0))
                            ->take(3)
                            ->map(static function (ProductVariant $variant): array {
                                return [
                                    'id'              => $variant->id,
                                    'name'            => $variant->name,
                                    'sku'             => $variant->sku,
                                    'views_count'     => (int) ($variant->views_count ?? 0),
                                    'sold_quantity'   => (int) ($variant->sold_quantity ?? 0),
                                    'conversion_rate' => round((float) ($variant->conversion_rate ?? 0.0), 2),
                                ];
                            })
                            ->values()
                            ->all(),
                    ];

                    $productSnapshots[$productId]['analytics'] = $analytics;

                    $totalViews += $views;
                    $totalCartAdditions += $cartAdditions;
                    $totalPurchases += $purchasedQuantity;
                    $totalWishlistAdditions += $wishlistAdditions;
                }

                $summary = [
                    'total_products'     => $processedCount,
                    'processed_products' => $processedCount,
                    'totals'             => [
                        'views'              => $totalViews,
                        'cart_additions'     => $totalCartAdditions,
                        'purchases'          => $totalPurchases,
                        'wishlist_additions' => $totalWishlistAdditions,
                    ],
                    'average_conversion_rates' => [
                        'cart_to_view'     => $cartRateProducts > 0 ? round($cartRateSum / $cartRateProducts, 2) : 0.0,
                        'purchase_to_view' => $purchaseRateProducts > 0 ? round($purchaseRateSum / $purchaseRateProducts, 2) : 0.0,
                    ],
                ];
            } else {
                $summary = [
                    'total_products'           => 0,
                    'processed_products'       => 0,
                    'totals'                   => ['views' => 0, 'cart_additions' => 0, 'purchases' => 0, 'wishlist_additions' => 0],
                    'average_conversion_rates' => ['cart_to_view' => 0.0, 'purchase_to_view' => 0.0],
                ];
            }

            $result = ['summary' => $summary, 'products' => array_values($productSnapshots)];

            $operation->finish([
                'processed_products' => $processedCount,
                'timeout_reached'    => now()->greaterThan($timeout),
            ]);

            return $result;
        } catch (Throwable $throwable) {
            $operation->fail($throwable);

            throw $throwable;
        }
    }

    /**
     * Handle generateUserActivityReport functionality with proper error handling.
     */
    public function generateUserActivityReport(array $filters = []): array
    {
        $operation = $this->logger->operation('report_user_activity', [
            'filters' => $filters,
        ]);

        try {
            $timeout = now()->addMinutes(8);
            // 8 minute timeout for user activity report
            $query = AnalyticsEvent::with(['user'])->whereNotNull('user_id');
            // Apply filters
            if (isset($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }
            if (isset($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
            if (isset($filters['event_type'])) {
                $query->where('event_type', $filters['event_type']);
            }
            $userActivity = [];
            $processedCount = 0;
            $query->cursor()->takeUntilTimeout($timeout)->each(function ($event) use (&$userActivity, &$processedCount) {
                $processedCount++;
                $userId = $event->user_id;
                if (! isset($userActivity[$userId])) {
                    $userActivity[$userId] = ['user_id' => $userId, 'user_name' => $event->user?->name ?? 'Unknown', 'user_email' => $event->user?->email ?? 'Unknown', 'events' => [], 'total_events' => 0, 'last_activity' => null];
                }
                $userActivity[$userId]['events'][] = ['type' => $event->event_type, 'url' => $event->url, 'created_at' => $event->created_at->format('Y-m-d H:i:s')];
                $userActivity[$userId]['total_events']++;
                if (! $userActivity[$userId]['last_activity'] || $event->created_at->greaterThan($userActivity[$userId]['last_activity'])) {
                    $userActivity[$userId]['last_activity'] = $event->created_at->format('Y-m-d H:i:s');
                }
            });
            $result = ['summary' => ['total_events' => $processedCount, 'unique_users' => count($userActivity), 'processed_events' => $processedCount], 'user_activity' => array_values($userActivity)];

            $operation->finish([
                'processed_events' => $processedCount,
                'unique_users'     => count($userActivity),
                'timeout_reached'  => now()->greaterThan($timeout),
            ]);

            return $result;
        } catch (Throwable $throwable) {
            $operation->fail($throwable);

            throw $throwable;
        }
    }

    /**
     * Handle generateSystemReport functionality with proper error handling.
     */
    public function generateSystemReport(): array
    {
        $operation = $this->logger->operation('report_system');

        try {
            $timeout = now()->addMinutes(15);
            // 15 minute timeout for comprehensive system report
            $report = ['generated_at' => now()->toISOString(), 'timeout' => $timeout->toISOString(), 'sections' => []];
            // Generate each section with individual timeouts
            $sections = ['users' => fn () => $this->generateUserStats(), 'products' => fn () => $this->generateProductStats(), 'analytics' => fn () => $this->generateAnalyticsStats()];
            foreach ($sections as $sectionName => $sectionGenerator) {
                if (now()->greaterThan($timeout)) {
                    $this->logger->log('warning', 'system_report_timeout', [
                        'completed_sections' => array_keys($report['sections']),
                        'remaining_sections' => array_keys($sections),
                    ]);
                    break;
                }
                try {
                    $report['sections'][$sectionName] = $sectionGenerator();
                } catch (Exception $e) {
                    $this->logger->log('error', 'system_report_section_failed', [
                        'section' => $sectionName,
                        'error'   => $e->getMessage(),
                    ]);
                    $report['sections'][$sectionName] = ['error' => $e->getMessage()];
                }
            }

            $operation->finish([
                'sections_generated' => array_keys($report['sections']),
                'timeout_reached'    => now()->greaterThan($timeout),
            ]);

            return $report;
        } catch (Throwable $throwable) {
            $operation->fail($throwable);

            throw $throwable;
        }
    }

    /**
     * Handle generateUserStats functionality with proper error handling.
     */
    private function generateUserStats(): array
    {
        $timeout = now()->addSeconds(30);

        return User::cursor()->takeUntilTimeout($timeout)->countBy(function ($user) {
            return $user->created_at->format('Y-m');
        })->toArray();
    }

    /**
     * Handle generateProductStats functionality with proper error handling.
     */
    private function generateProductStats(): array
    {
        $timeout = now()->addSeconds(30);
        $stats = ['total' => 0, 'visible' => 0, 'featured' => 0, 'with_stock' => 0];
        Product::cursor()->takeUntilTimeout($timeout)->each(function ($product) use (&$stats) {
            $stats['total']++;
            if ($product->is_visible) {
                $stats['visible']++;
            }
            if ($product->is_featured) {
                $stats['featured']++;
            }
            if ($product->stock_quantity > 0) {
                $stats['with_stock']++;
            }
        });

        return $stats;
    }

    /**
     * Handle generateAnalyticsStats functionality with proper error handling.
     */
    private function generateAnalyticsStats(): array
    {
        $timeout = now()->addSeconds(30);

        return AnalyticsEvent::cursor()->takeUntilTimeout($timeout)->countBy('event_type')->toArray();
    }
}
