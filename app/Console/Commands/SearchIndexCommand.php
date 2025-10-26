<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class SearchIndexCommand extends Command
{
    protected $signature = 'search:index {--fresh : Flush existing indexes before rebuilding} {--only= : Comma separated list of targets (product,category,brand)}';

    protected $description = 'Build search indexes for the configured Scout driver.';

    public function handle(): int
    {
        if (! $this->shouldUseScout()) {
            $this->components->warn('Scout search driver is disabled. Using database search, so no indexes were built.');

            return self::SUCCESS;
        }

        $models = $this->resolveModels((string) $this->option('only'));

        if ($models === []) {
            $this->components->warn('No matching searchable models were resolved for indexing.');

            return self::SUCCESS;
        }

        $fresh = (bool) $this->option('fresh');

        foreach ($models as $label => $model) {
            $this->components->info(sprintf('Indexing %s records…', Str::plural($label)));

            if ($fresh) {
                $model::removeAllFromSearch();
            }

            $model::makeAllSearchable();
        }

        $this->components->info('Search indexing completed.');

        return self::SUCCESS;
    }

    private function shouldUseScout(): bool
    {
        return config('search.driver') === 'scout' && config('search.scout.enabled');
    }

    /**
     * @return array<string, class-string>
     */
    private function resolveModels(string $only): array
    {
        $map = [
            'product'  => Product::class,
            'category' => Category::class,
            'brand'    => Brand::class,
        ];

        if ($only === '') {
            return $map;
        }

        return collect(explode(',', $only))
            ->map(fn (string $value): string => strtolower(trim($value)))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->filter(fn (string $value): bool => array_key_exists($value, $map))
            ->mapWithKeys(fn (string $value): array => [$value => $map[$value]])
            ->all();
    }
}
