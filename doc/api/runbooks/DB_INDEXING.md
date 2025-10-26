# Database Indexing Runbook

This runbook describes how to audit index coverage with the `db:audit-indexes` artisan command, how to capture baseline query plans before deploying new indexes, and how to verify improvements afterwards. Follow these steps whenever you introduce schema changes that add or modify database indexes.

## Prerequisites

- Application dependencies are installed and the `.env` file points to the database you want to audit.
- Your database user has permission to run `EXPLAIN` and `ANALYZE` queries.
- You have a MySQL-compatible client available (e.g. `mysql`, TablePlus, DataGrip).

## 1. Run the index audit

```bash
php artisan db:audit-indexes
```

The command now performs two layers of analysis:

1. **Duplicate detection** – highlights overlapping indexes so you can drop redundant definitions.
2. **Commerce composite suggestions** – recommends battle-tested index combinations for `orders`, `order_items`, and `products` to keep analytics dashboards and storefront filters fast.

Run it in the environment that matches the schema you are about to optimize (local feature branch, staging, etc.). Save the output in your ticket or PR so you can compare it after applying migrations.

Example (with deliberate duplicates and missing composites):

```text
Duplicate indexes detected:
- duplicate_index_examples on [slug] (unique: no) via [duplicate_index_examples_slug_idx, duplicate_index_examples_slug_idx_duplicate]
- duplicate_index_examples on [category_id, slug] (unique: no) via [duplicate_index_examples_category_slug_idx, duplicate_index_examples_category_slug_idx_duplicate]
Suggested composite indexes for commerce hotspots:
- orders: add [status, created_at] (recommended name: index_orders_status_created_at) → Speeds up order analytics by filtering status windows in dashboards.
- products: add [is_visible, price] (recommended name: products_visibility_price_idx) → Keeps price range filters and merchandising widgets responsive.
```

### Filtering the audit

You can narrow the audit to a specific connection:

```bash
# Audit a non-default connection
db_connection=analytics php artisan db:audit-indexes
```

> **Tip:** Use `--json` to capture a machine-readable payload containing both duplicates and recommendations when you want to post-process the findings in CI.

> **Tip:** Run the audit both before and after migrations so you can confirm that warnings about missing indexes are resolved.

## 2. Capture baseline EXPLAIN plans

Before applying schema changes, record the execution plans for your high-impact queries. Focus on the analytics widgets in the admin panel and the storefront filters because they issue the largest table scans.

### Analytics widgets (`ComprehensiveStatsWidget`)

The widget aggregates order revenue and counts recent records. Run each query with `EXPLAIN ANALYZE` so you capture the planner's cost and actual timing.

```sql
EXPLAIN ANALYZE
SELECT DATE(created_at) AS date, SUM(total) AS revenue
FROM orders
WHERE status = 'completed'
  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY date
ORDER BY date;
```

Save the output to `storage/logs/query-plans/baseline/comprehensive-stats-<timestamp>.txt` so you can diff it later.

### Storefront filters

For each storefront filter (category, price range, availability, etc.), record the plan that powers the API or Livewire endpoint. Example:

```sql
EXPLAIN ANALYZE
SELECT id, name, price
FROM products
WHERE is_visible = 1
  AND price BETWEEN 10 AND 40
  AND category_id IN (SELECT id FROM categories WHERE slug IN ('skincare', 'makeup'))
ORDER BY popularity DESC
LIMIT 24;
```

If queries are generated dynamically, capture them from your query log (`DB::listen` or Telescope) and re-run them with `EXPLAIN ANALYZE`.

## 3. Apply migrations that add/adjust indexes

Run your migration locally or in staging. Example:

```bash
php artisan migrate
```

### Current coverage snapshot (February 2025)

The analytics stack now relies on standalone `created_at` indexes for the busiest tables:

- `orders_created_at_index`
- `products_created_at_index`
- `users_created_at_index`

Use these indexes when filtering dashboards and widgets by date ranges. Prefer range predicates (`BETWEEN`, `>=`, `<=`) or the dedicated Eloquent scopes so the optimizer can select the correct index.

Key consumers already wired for these indexes include:

- `App\Support\Stats\OrderMetrics` via the reusable `Order::createdBetween()` scope to drive the sales and revenue aggregates.
- `App\Services\Dashboard\DashboardMetricsRepository` for "orders today" and the revenue rollups, again through the created-at scope.
- `App\Support\Stats\Series\CustomerSeries` which backs the Filament customer sparkline widget and now leans on the scoped date window.

> **Note:** When building new analytics features, reach for the `Order` date scopes instead of raw `whereBetween` clauses so you automatically benefit from the curated coverage.

If the migration only affects analytics tables, re-run the targeted audit to confirm that missing index warnings disappear.

## 4. Verify improvements

Repeat the steps above:

1. Re-run `php artisan db:audit-indexes` and ensure the command no longer flags the tables you optimized.
2. Re-run the `EXPLAIN ANALYZE` statements and compare the new output against the baseline. Look for:
   - Reduced `rows` estimates for the scanned tables.
   - Lower execution time in the final line (`actual time=` in MySQL 8+).
   - `key`/`idx` columns now populated with the name of the index you added.

Store the "after" outputs under `storage/logs/query-plans/after/` for traceability.

## 5. Interpreting EXPLAIN output

Key fields to review when deciding whether an index is used effectively:

- **type** – `ref`, `range`, or `index_merge` indicates the optimizer is using an index. `ALL` means a full table scan.
- **key / key_len** – Shows the exact index chosen and how much of it is used. If `NULL`, the optimizer skipped indexes.
- **rows** – Estimated rows examined; should drop significantly when an index is effective.
- **filtered** – Percentage of rows that passed the predicate; low values may suggest you need a more selective index.

## 6. Example plans (before vs. after)

The following snippets illustrate the difference that the new composite indexes introduce for the analytics widget queries. The "before" plan was captured prior to adding an index on `(status, created_at)` for the `orders` table. The "after" plan uses the optimized schema.

```text
-- Before migration
id | select_type | table  | type | possible_keys | key  | key_len | rows  | Extra
1  | SIMPLE      | orders | ALL  | NULL          | NULL | NULL    | 84215 | Using temporary; Using filesort

-- After migration (index_orders_status_created_at)
id | select_type | table  | type  | possible_keys                          | key                                | key_len | rows | Extra
1  | SIMPLE      | orders | range | index_orders_status_created_at         | index_orders_status_created_at     | 152     | 872  | Using where; Using index
```

Notice how the `key` column changes from `NULL` to the specific index name, and the `rows` estimate drops from ~84k to <1k.

Another example from the storefront filter query after adding `products_visibility_price_idx (is_visible, price)`:

```text
-- Before migration
id | select_type | table    | type | possible_keys | key  | key_len | rows  | Extra
1  | SIMPLE      | products | ALL  | NULL          | NULL | NULL    | 42137 | Using where

-- After migration (products_visibility_price_idx)
id | select_type | table    | type  | possible_keys             | key                          | key_len | rows | Extra
1  | SIMPLE      | products | range | products_visibility_price_idx | products_visibility_price_idx | 5       | 312  | Using index condition
```

When reviewing plans, confirm that:

- `type` improves from `ALL` to `range`/`ref`/`index`.
- `key`/`idx` matches the index you expect to use.
- `Extra` no longer mentions `Using filesort` for grouped queries unless sorting is unavoidable.

## 7. Document findings

Update your ticket or PR description with:

- The audit output before and after migrations.
- Snippets of the `EXPLAIN ANALYZE` plans demonstrating index usage.
- Any follow-up actions (e.g., additional composite indexes needed for other filters).

Having these artifacts ensures the team can trace the performance improvements back to the schema changes and reuse the process for future optimizations.
