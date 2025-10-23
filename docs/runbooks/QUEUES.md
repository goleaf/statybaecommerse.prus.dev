# Queue Policy & Operations

## Purpose
Long-running work must never block HTTP requests or Artisan commands. Instead, dispatch tasks to the queue so user interactions finish quickly while heavy processing happens in the background.

Recent changes route two expensive flows to queued jobs:

- **Stock exports** now dispatch `App\\Jobs\\GenerateStockExport` from the stock controller. The job writes CSV files to the `public/exports` disk path with a 3-attempt retry and exponential backoff (60/120/300 seconds).
- **Report generation** commands now push `App\\Jobs\\GenerateReportsJob` onto the `reports` queue. The job fans out the individual report files and persists them to the chosen storage directory with identical retry/backoff semantics.

## Driver policy
- **Local development:** the sample `.env.terminal-optimized` ships with `QUEUE_CONNECTION=sync` to simplify quick iteration. This keeps tests and manual QA deterministic while still allowing job classes to be executed immediately.
- **Production & staging:** fall back to Laravel's default `database` queue driver (`config/queue.php`) so work is durable and recoverable across workers.

To simulate production locally, switch to the database driver and run migrations for the `jobs` table:

```bash
php artisan queue:table
php artisan migrate
QUEUE_CONNECTION=database php artisan queue:work --queue=reports,exports,default
```

## Operational checklist
1. Confirm a queue worker is active for the `reports` and `exports` queues (in addition to `default`).
2. Monitor the `jobs` and `failed_jobs` tables or your preferred queue dashboard for stuck tasks.
3. Failed jobs will automatically retry up to three times with the defined backoff. Inspect logs (`storage/logs/laravel.log`) for additional context before retrying manually with `php artisan queue:retry <id>`.
4. When introducing new heavy flows, prefer creating dedicated queues and document them here so operations can size workers appropriately.

## Retry policy

Each queued job now exposes an explicit retry limit so worker behaviour is deterministic regardless of the `--tries` flag. Backoff windows are expressed in seconds.

| Job | Queue | Max retries | Backoff |
| --- | --- | --- | --- |
| `App\\Jobs\\CheckLowStockJob` | `default` | 2 | 60, 300 |
| `App\\Jobs\\ClearApplicationCacheJob` | `default` | 1 | – |
| `App\\Jobs\\GenerateMediaVariantsJob` | `default` | 3 | 30, 120, 300 |
| `App\\Jobs\\GenerateReportsJob` | `reports` | 3 | 60, 120, 300 |
| `App\\Jobs\\GenerateStockExport` | `exports` | 3 | 60, 120, 300 |
| `App\\Jobs\\ImportInventoryChunk` | `default` | 5 | 30, 90, 180, 300, 600 |
| `App\\Jobs\\ImportPricesChunk` | `default` | 5 | 30, 90, 180, 300, 600 |
| `App\\Jobs\\ImportProductsChunk` | `default` | 5 | 30, 90, 180, 300, 600 |
| `App\\Jobs\\ProcessExportJob` | `default` | 3 | 60, 120, 300 |
| `App\\Jobs\\RebuildSearchIndexJob` | `default` | 1 | – |
| `App\\Jobs\\RunMinimalSeedJob` | `default` | 1 | – |
| `App\\Jobs\\SendContactMessageJob` | `default` | 3 | 60, 120, 240 |
| `App\\Jobs\\SendNotificationJob` | `default` | 3 | 60, 180, 360 |

## Dead-letter queue & spike alerts

- Jobs that exhaust their retries are copied to the `dead_letter_jobs` table (UUID, payload, and exception metadata) for investigation. Call `$deadLetter->requeue()` from a console REPL to push the job back onto its original queue once the root cause is fixed.
- Queue failures are bucketed in five-minute windows by default. When the number of terminal failures within a window exceeds `QUEUE_FAILURE_SPIKE_THRESHOLD` (default 5) a system notification is broadcast to admins describing the spike and pointing at the most recent dead-letter entry.
- Tune behaviour via the new configuration keys in `.env`:
  ```ini
  QUEUE_DEAD_LETTER_ENABLED=true
  QUEUE_DEAD_LETTER_QUEUE=dead-letter
  QUEUE_FAILURE_ALERTS=true
  QUEUE_FAILURE_SPIKE_THRESHOLD=5
  QUEUE_FAILURE_SPIKE_WINDOW=300
  ```
  Set `QUEUE_FAILURE_ALERTS=false` in non-production environments if you do not want notifications during local development.

## Troubleshooting
- If local testing relies on the `sync` driver, queued jobs run inline. Switch to `database` (or another async driver) when you need to validate background behaviour, and remember to start a worker before running the scenario.
- Use `Queue::fake()` in automated tests to assert job dispatch without requiring a live worker.

Keeping this policy ensures the application remains responsive while maintaining clear operational expectations between development and production environments.
