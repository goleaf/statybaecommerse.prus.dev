<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Translations\LegalTranslation;
use App\Models\Translations\ProductTranslation;
use App\Support\Html\HtmlSanitizer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class SanitizeHtmlContent extends Command
{
    protected $signature = 'maintenance:sanitize-html {--chunk=100 : Number of records to process per chunk} {--dry-run : Only report changes without persisting them}';

    protected $description = 'Re-sanitize persisted rich text content to enforce the HTML allow-list.';

    public function handle(HtmlSanitizer $sanitizer): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $totalChanged = 0;

        $totalChanged += $this->sanitizeQuery(
            label: 'products.description',
            query: Product::query()->select(['id', 'description']),
            fields: ['description'],
            sanitizer: $sanitizer,
            chunkSize: $chunkSize,
            dryRun: $dryRun,
        );

        $totalChanged += $this->sanitizeQuery(
            label: 'product_translations.description',
            query: ProductTranslation::query()->select(['id', 'description']),
            fields: ['description'],
            sanitizer: $sanitizer,
            chunkSize: $chunkSize,
            dryRun: $dryRun,
        );

        $totalChanged += $this->sanitizeQuery(
            label: 'legal_translations.content',
            query: LegalTranslation::query()->select(['id', 'content']),
            fields: ['content'],
            sanitizer: $sanitizer,
            chunkSize: $chunkSize,
            dryRun: $dryRun,
        );

        $summary = $dryRun
            ? sprintf('Identified %d record(s) with content changes.', $totalChanged)
            : sprintf('Sanitized %d record(s).', $totalChanged);

        $this->info($summary);

        return self::SUCCESS;
    }

    /**
     * @param array<int, string> $fields
     */
    private function sanitizeQuery(string $label, Builder $query, array $fields, HtmlSanitizer $sanitizer, int $chunkSize, bool $dryRun): int
    {
        $this->line(sprintf('Processing %s…', $label));

        $changed = 0;

        $query->where(function (Builder $builder) use ($fields): void {
            foreach ($fields as $field) {
                $builder->orWhereNotNull($field);
            }
        })->orderBy('id')->chunkById($chunkSize, function ($records) use ($fields, $sanitizer, $dryRun, &$changed): void {
            foreach ($records as $record) {
                if (! $record instanceof Model) {
                    continue;
                }

                $updates = [];

                foreach ($fields as $field) {
                    $original = $record->getAttribute($field);
                    if ($original === null) {
                        continue;
                    }

                    $sanitized = $sanitizer->sanitize((string) $original);
                    if ($sanitized !== $original) {
                        $updates[$field] = $sanitized;
                    }
                }

                if ($updates === []) {
                    continue;
                }

                $changed++;

                if ($dryRun) {
                    continue;
                }

                $record->fill($updates);
                $record->saveQuietly();
            }
        });

        $this->line(sprintf('Finished %s (%d updated).', $label, $changed));

        return $changed;
    }
}
