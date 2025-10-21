<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Translations\LegalTranslation;
use App\Models\Translations\ProductTranslation;
use App\Support\Html\HtmlSanitizer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class SanitizeHtmlContentCommand extends Command
{
    protected $signature = 'maintenance:sanitize-html
                            {--chunk=100 : How many records to process per chunk}
                            {--dry-run : Inspect changes without persisting them}';

    protected $description = 'Normalize rich text fields through the HTML sanitizer.';

    public function handle(HtmlSanitizer $sanitizer): int
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        if ($chunkSize <= 0) {
            $this->components->error('Chunk size must be a positive integer.');

            return self::FAILURE;
        }

        $this->components->info('Sanitizing product descriptions and legal translations...');

        $counters = [
            'products' => 0,
            'product_translations' => 0,
            'legal_translations' => 0,
        ];

        Product::withoutGlobalScopes()
            ->select(['id', 'description', 'short_description'])
            ->chunkById($chunkSize, function (Collection $products) use (&$counters, $sanitizer, $dryRun): void {
                foreach ($products as $product) {
                    $updates = [];

                    foreach (['description', 'short_description'] as $field) {
                        $current = $product->{$field};
                        if (! is_string($current) || trim($current) === '') {
                            continue;
                        }

                        $clean = $sanitizer->sanitize($current);
                        if ($clean !== $current) {
                            $updates[$field] = $clean;
                        }
                    }

                    if ($updates === []) {
                        continue;
                    }

                    $counters['products']++;

                    if (! $dryRun) {
                        $product->fill($updates)->saveQuietly();
                    }
                }
            });

        ProductTranslation::query()
            ->select(['id', 'description', 'short_description', 'summary'])
            ->chunkById($chunkSize, function (Collection $translations) use (&$counters, $sanitizer, $dryRun): void {
                foreach ($translations as $translation) {
                    $updates = [];

                    foreach (['description', 'short_description', 'summary'] as $field) {
                        $current = $translation->{$field};
                        if (! is_string($current) || trim($current) === '') {
                            continue;
                        }

                        $clean = $sanitizer->sanitize($current);
                        if ($clean !== $current) {
                            $updates[$field] = $clean;
                        }
                    }

                    if ($updates === []) {
                        continue;
                    }

                    $counters['product_translations']++;

                    if (! $dryRun) {
                        $translation->fill($updates)->saveQuietly();
                    }
                }
            });

        LegalTranslation::query()
            ->select(['id', 'content'])
            ->chunkById($chunkSize, function (Collection $translations) use (&$counters, $sanitizer, $dryRun): void {
                foreach ($translations as $translation) {
                    $current = $translation->content;
                    if (! is_string($current) || trim($current) === '') {
                        continue;
                    }

                    $clean = $sanitizer->sanitize($current);
                    if ($clean === $current) {
                        continue;
                    }

                    $counters['legal_translations']++;

                    if (! $dryRun) {
                        $translation->forceFill(['content' => $clean])->saveQuietly();
                    }
                }
            });

        $this->components->info('HTML sanitization run finished.');

        foreach ($counters as $label => $count) {
            $this->components->line(Str::headline($label).": {$count} updated");
        }

        if ($dryRun) {
            $this->components->warn('Dry run enabled — no changes were persisted.');
        }

        return self::SUCCESS;
    }
}
