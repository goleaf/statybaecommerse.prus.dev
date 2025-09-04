<?php declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ImportPricesChunk implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<int,array<string,mixed>> */
    private array $rows;

    /**
     * @param array<int,array<string,mixed>> $rows
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function handle(): void
    {
        foreach ($this->rows as $row) {
            $productSlug = (string) ($row['product_slug'] ?? '');
            $currencyCode = strtoupper((string) ($row['currency_code'] ?? ''));
            $amount = isset($row['amount']) ? (float) $row['amount'] : null;
            $compare = isset($row['compare_amount']) ? (float) $row['compare_amount'] : null;
            if ($productSlug === '' || $currencyCode === '' || $amount === null) {
                continue;
            }

            $productId = DB::table('products')->where('slug', $productSlug)->value('id');
            $currencyId = DB::table('currencies')->where('code', $currencyCode)->value('id');
            if (!$productId || !$currencyId) {
                continue;
            }

            $data = [
                'priceable_type' => \App\Models\Product::class,
                'priceable_id' => (int) $productId,
                'currency_id' => (int) $currencyId,
                'amount' => round($amount, 2),
            ];
            if ($compare !== null) {
                $data['compare_amount'] = round($compare, 2);
            }

            try {
                DB::table('prices')->upsert(
                    [$data],
                    ['priceable_type', 'priceable_id', 'currency_id'],
                    ['amount', 'compare_amount']
                );
            } catch (\Throwable $e) {
                // continue
            }
        }
    }
}
