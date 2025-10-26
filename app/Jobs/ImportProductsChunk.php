<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Throwable;
use function collect;

/**
 * ImportProductsChunk
 *
 * Queue job for ImportProductsChunk background processing with proper error handling, retry logic, and progress tracking.
 */
class ImportProductsChunk implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of job attempts before failing.
     */
    public int $tries = 5;

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(private readonly array $rows)
    {
    }

    /**
     * Define retry backoff windows (in seconds).
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 90, 180, 300, 600];
    }

    /**
     * Pre-cached map of brand names to ids for the current chunk.
     *
     * @var array<string, int>
     */
    private array $brandIdsByName = [];

    /**
     * Handle the job, event, or request processing.
     */
    public function handle(): void
    {
        // Use LazyCollection with timeout to prevent long-running import operations
        $timeout = now()->addMinutes(10);

        // Warm the brand lookup so repeated rows do not hammer the database unnecessarily
        $this->brandIdsByName = $this->primeBrandLookup();

        LazyCollection::make($this->rows)
            ->takeUntilTimeout($timeout)
            ->each(function (array $row): void {
                try {
                    $this->processRow($row);
                } catch (Throwable $exception) {
                    Log::error('Product import row failed.', [
                        'slug' => $row['slug'] ?? null,
                        'name' => $row['name'] ?? null,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });
    }

    /**
     * @return array<string, int>
     */
    private function primeBrandLookup(): array
    {
        // Collect the unique brand names once so we can fetch identifiers in bulk
        $brandNames = collect($this->rows)
            ->pluck('brand')
            ->map(fn ($value): ?string => $this->normalizeBrandName($value))
            ->filter()
            ->unique()
            ->all();

        if ($brandNames === []) {
            return [];
        }

        return Brand::query()
            ->whereIn('name', $brandNames)
            ->pluck('id', 'name')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function processRow(array $row): void
    {
        // Normalise the core product attributes before attempting to persist them
        $name = $this->normalizeName($row['name'] ?? null);
        $slug = $this->resolveSlug($row['slug'] ?? null, $name);

        if ($slug === '' || $name === '') {
            Log::warning('Product import skipped because slug or name was missing.', [
                'slug' => $slug,
                'name' => $name,
            ]);

            return;
        }

        $brandId = $this->resolveBrandId($row['brand'] ?? null);
        $publishedAt = $this->parsePublishedAt($row['published_at'] ?? null);

        $payload = [
            'slug' => $slug,
            'name' => $name,
        ];

        if ($brandId !== null) {
            $payload['brand_id'] = $brandId;
        }

        if ($publishedAt !== null) {
            $payload['published_at'] = $publishedAt;
        }

        if (array_key_exists('is_visible', $row)) {
            $payload['is_visible'] = (bool) $row['is_visible'];
        }

        // Persist the upsert so imports stay idempotent across multiple retries
        Product::query()->updateOrCreate(
            ['slug' => $slug],
            $payload
        );
    }

    private function normalizeName(mixed $name): string
    {
        // Keep formatting predictable by trimming extra whitespace before any slug logic runs
        return trim((string) ($name ?? ''));
    }

    private function resolveSlug(mixed $slug, string $name): string
    {
        $candidate = trim((string) ($slug ?? ''));

        if ($candidate === '' && $name !== '') {
            return Str::slug($name);
        }

        return $candidate === '' ? '' : Str::slug($candidate);
    }

    private function resolveBrandId(mixed $brand): ?int
    {
        $name = $this->normalizeBrandName($brand);

        return $name !== null ? ($this->brandIdsByName[$name] ?? null) : null;
    }

    private function normalizeBrandName(mixed $brand): ?string
    {
        if ($brand === null) {
            return null;
        }

        $name = trim((string) $brand);

        return $name === '' ? null : $name;
    }

    private function parsePublishedAt(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            // Carbon handles string parsing and normalises to the application timezone
            return Carbon::parse((string) $value);
        } catch (Throwable $exception) {
            Log::warning('Failed to parse published_at during product import.', [
                'value' => $value,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
