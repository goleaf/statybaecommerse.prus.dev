# Inline Charts in Filament Tables

The admin now embeds compact sparklines directly inside table rows using [`lara-zeus/inline-chart`](https://packagist.org/packages/lara-zeus/inline-chart). The package renders Chart.js data inside Filament table columns without sacrificing performance.

## Where charts appear

| Resource | Column | Widget | Description |
| --- | --- | --- | --- |
| Products → List | **Sales (30d)** | `ProductSales30DaysChart` | Shows the last 30 days of order item quantities for each product. |
| Customers → List | **LTV (12m)** | `CustomerLtv12MonthsChart` | Shows the customer’s monthly order totals for the last 12 months. |

All inline chart columns are toggleable so users can hide them on narrow screens.

## Data series & caching

| Helper | Method | Data source | Cache key | TTL |
| --- | --- | --- | --- | --- |
| `App\Support\Stats\Inline\ProductSeries` | `last30Days(int $productId)` | `order_items` grouped by `created_at` date | `inline:product:{id}:sales:30d` | 5 minutes |
| `App\Support\Stats\Inline\CustomerSeries` | `ordersLast12m(int $customerId)` | `orders` totals grouped by month (auto-detects `customer_id` vs `user_id`) | `inline:customer:{id}:ltv:12m` | 10 minutes |

The helpers guarantee a fixed-length dataset. When no activity exists, they return zero-filled arrays to avoid blank charts.

## Adding another inline chart

1. Create a widget under `app/Filament/Widgets/InlineCharts/` that extends `InlineChartWidget` and implements `getType()` and `getData()`.
2. Build the series in a support helper (see `app/Support/Stats/Inline`) that returns `labels` and `values` arrays and caches the result for 1–5 minutes.
3. Register the widget inside a table column with:
   ```php
   InlineChart::make('column_key')
       ->label('Column Title')
       ->chart(YourWidget::class)
       ->maxWidth(300)
       ->maxHeight(60)
       ->description('What the chart shows')
       ->toggleable();
   ```
4. If the column depends on aggregated data, ensure the underlying query eager-loads related data or caches the aggregation.
5. Add a regression test asserting that `getData()` returns a dataset and that the Filament list page renders the chart `wire:key` markup.

## Styling & behaviour

* Widgets default to compact heights (≈60px) with muted colours that match Filament’s design tokens.
* Polling is disabled by default. If you need live updates, enable Filament’s polling on the widget class with sensible intervals (≥30 s).
* Columns expose tooltips via the package’s built-in tooltip handler.

For more context on the admin UI, refer to `docs/CachePolicy.md` and `docs/filament-navigation-structure.md`.
