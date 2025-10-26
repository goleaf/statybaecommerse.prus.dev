# Filament Advanced Widgets Integration

This document records the integration of [eightynine/filament-advanced-widgets](https://github.com/eightynine/filament-advanced-widgets) across the admin panel. It lists the widgets that were added, how they are wired into Filament panels/resources, and the extension points you can use when evolving the analytics layer.

## Global dashboard widgets

| Widget | Class | Purpose | Filters |
| --- | --- | --- | --- |
| Store KPIs | `App\Filament\Widgets\GeneralStatsOverview` | Consolidated revenue, order, conversion, and customer metrics (with sparkline + delta badges). | `today`, `week`, `month`, `quarter`, `year`, `ytd`, `last_30_days` |
| Sales by period | `App\Filament\Widgets\SalesByMonthChart` | Trendline of revenue with automatic YoY badge. | `month`, `quarter`, `year`, `ytd` |

Both widgets share `App\Filament\Support\InteractsWithDateFilter` to normalise filter inputs into Carbon date ranges. Metrics come from `App\Support\Stats\OrderMetrics` and `App\Support\Stats\TrafficMetrics`, which add light caching (60s) to avoid duplicate queries.

The admin panel registers these widgets explicitly inside `App\Filament\AdminPanelProvider` via `->widgets([...])` so dashboard composition stays deterministic.

## Resource widgets

| Resource | Header widgets | Footer widgets |
| --- | --- | --- |
| Orders | `OrderResourceStats` | `OrderRevenueTrend` |
| Products | `ProductStatsWidget` | `ProductChartWidget` |
| Customers | `CustomerResourceStats` | `CustomerGrowthChart` |
| News | `NewsResourceStats` | `NewsPerformanceChart` |

All resource widgets live in `app/Filament/Resources/<Resource>Resource/Widgets/` and are registered through the corresponding `List<Record>` page (`getHeaderWidgets()` / `getFooterWidgets()`). Each widget offers meaningful filters and leverages the shared date filter trait where timelines are involved.

### Filters & behaviour highlights

- **Order widgets** reuse `OrderMetrics` to expose period-aware stats, refund rates, and order/revenue dual-axis charts.
- **Product widgets** now extend the advanced widget base classes, add catalogue filters, and surface “new products” for the selected period.
- **Customer widgets** guard against missing `orders.customer_id` columns and fall back gracefully if order engagement cannot be derived.
- **News widgets** operate on `News::withoutGlobalScopes()` so editorial states (draft/review/published) are counted accurately.

## Metric helpers

Two dedicated helpers orchestrate the heavy lifting:

- `App\Support\Stats\OrderMetrics`
  - Aggregates revenue, orders, conversion rate, refund rate, top sellers, and trends (per-day or per-month via `flowframe/laravel-trend`).
  - Provides cached series for sparkline/line charts and period comparisons for badge deltas.
- `App\Support\Stats\TrafficMetrics`
  - Tracks sessions, conversions, new/active users, and newsletter signups.
  - Supplies a reusable `sessionsTrend()` helper for future widgets.

Both helpers accept Carbon instances, normalise ranges, and surface percentage deltas to drive widget badges.

## Extending the widgets

1. **Add a new metric**: expose it from `OrderMetrics` or `TrafficMetrics`, then consume it inside the relevant widget’s `getStats()` / `getData()` method. Keep cache keys unique by appending the filter + date window.
2. **Introduce another filter**: register the filter in the widget’s `getFilters()` and ensure the trait’s `getDateRange()` recognises the slug (extend the `match` expression as needed).
3. **Scope by tenant / role**: wrap widget registration in visibility callbacks or narrow metric queries with the authenticated user/tenant context.
4. **Chart options**: advanced chart widgets accept standard Chart.js configuration arrays under the `options` key—see `OrderRevenueTrend` for a dual-axis example.

## Testing

Smoke tests live in `tests/Feature/Filament/Widgets/` and assert that the global widgets can be instantiated, gather seeded data, and return correctly shaped structures.

## Gotchas & tips

- Clear cache (`Cache::clear()`) during tests or artisan tasks when seeding new datasets to avoid stale KPIs.
- When working with `News` stats, always operate on `withoutGlobalScopes()`; the default scopes hide drafts/review items.
- Prefer `Number::format()` / `Number::currency()` for consistent localisation in widget output.
- Keep filters lightweight—widgets default to manual polling (no auto refresh) to protect dashboard performance.
