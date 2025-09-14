<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class ImportProductsChunk implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<int,array<string,mixed>> */
    private array $rows;

    /**
     * @param  array<int,array<string,mixed>>  $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function handle(): void
    {
        // Use LazyCollection with timeout to prevent long-running import operations
        $timeout = now()->addMinutes(10); // 10 minute timeout for import chunks
        
        LazyCollection::make($this->rows)
            ->takeUntilTimeout($timeout)
            ->each(function ($row) {
                $slug = (string) ($row['slug'] ?? '');
                $name = (string) ($row['name'] ?? '');
                if ($slug === '' && $name !== '') {
                    $slug = Str::slug($name);
                }
                if ($slug === '' || $name === '') {
                    return;  // require both
                }

                $brandName = (string) ($row['brand'] ?? '');
                $brandId = null;
                if ($brandName !== '') {
                    $brandId = DB::table('sh_brands')->where('name', $brandName)->value('id');
                }

                $publishedAt = null;
                if (! empty($row['published_at'])) {
                    try {
                        $publishedAt = now()->parse((string) $row['published_at']);
                    } catch (\Throwable $e) {
                        $publishedAt = null;
                    }
                }

                $data = [
                    'slug' => $slug,
                    'name' => $name,
                ];
                if ($brandId) {
                    $data['brand_id'] = (int) $brandId;
                }
                if ($publishedAt) {
                    $data['published_at'] = $publishedAt;
                }
                if (isset($row['is_visible'])) {
                    $data['is_visible'] = (bool) $row['is_visible'];
                }

                try {
                    DB::table('sh_products')->upsert(
                        [$data],
                        ['slug'],
                        array_values(array_diff(array_keys($data), ['slug']))
                    );
                } catch (\Throwable $e) {
                    // best-effort; continue
                }
            });
    }
}
