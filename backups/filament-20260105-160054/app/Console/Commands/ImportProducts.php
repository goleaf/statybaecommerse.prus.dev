<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

/**
 * Console command that imports products from a predefined Excel file into the database.
 *
 * The implementation mirrors the stakeholder-supplied drop-in while adding docblocks, comments,
 * strict typing, and PSR-12 formatting in line with the repository guardrails.
 */
class ImportProducts extends Command
{
    /**
     * The Artisan signature does not take any parameters, as requested.
     */
    protected $signature = 'products:import';

    /**
     * A short description shown in `php artisan list`.
     */
    protected $description = 'Import products from /public/products/statyba_produktai.xlsx (sheet "Bendras prekių sąrašas").';

    /**
     * Main table name where products are stored.
     */
    protected string $productsTable = 'products';

    /**
     * Optional related table names detected at runtime for relationships.
     */
    protected ?string $categoriesTable = null;

    protected ?string $brandsTable = null;

    protected ?string $manufacturersTable = null;

    protected ?string $pivotCategoryProduct = null;

    /**
     * Optional tables used to materialise variants and attribute options.
     */
    protected ?string $productVariantsTable = null;

    protected ?string $attributesTable = null;

    protected ?string $attributeValuesTable = null;

    protected ?string $productAttributesTable = null;

    protected ?string $productVariantAttributesTable = null;

    /**
     * Local caches so repeated lookups do not hammer the database.
     *
     * @var array<string, string>
     */
    protected array $categoryCache = [];

    /**
     * Cache attribute identifiers keyed by their slug for fast reuse.
     *
     * @var array<string, string>
     */
    protected array $attributeCache = [];

    /**
     * Cache attribute value identifiers keyed by attribute slug + value slug.
     *
     * @var array<string, string>
     */
    protected array $attributeValueCache = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Allow large Excel sheets without exhausting limits.
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '0');

        // File location and contextual metadata.
        $file = public_path('products/statyba_produktai.xlsx');
        $sheetName = 'Bendras prekių sąrašas';
        $delimiter = '/';
        $vat = 21.0; // The stakeholder explicitly requested a fixed VAT without params.

        // Abort early when file is missing to avoid silent failures.
        if (! is_file($file)) {
            $this->error("Excel file not found: {$file}");

            return self::FAILURE;
        }

        try {
            // Dynamically detect table names or produce actionable feedback.
            $this->detectTablesOrExplain();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Reading [{$sheetName}] from: {$file}");

        try {
            // Load the spreadsheet using PhpSpreadsheet to stay consistent with the drop-in.
            $spreadsheet = IOFactory::load($file);
        } catch (Throwable $e) {
            $this->error('Failed to read Excel: ' . $e->getMessage());

            return self::FAILURE;
        }

        // Prefer the named sheet but fall back to the first sheet when missing.
        $sheet = $spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        if ($rows === []) {
            $this->warn('No rows found in worksheet.');

            return self::SUCCESS;
        }

        /** @var array<int, mixed> $rows */

        // Identify the header row using Lithuanian column names.
        $headerRowIndex = $this->findHeaderRowIndex($rows);
        if ($headerRowIndex === null) {
            $this->error('Could not find header row. Expecting Lithuanian headers like "Pavadinimas" or "Nuotrauka".');

            return self::FAILURE;
        }

        $headerRow = $rows[$headerRowIndex] ?? null;
        if (! is_array($headerRow)) {
            $this->error('Header row is not a valid array structure.');

            return self::FAILURE;
        }

        // Normalise header values into canonical internal keys.
        $headers = $this->normalizeHeaders($headerRow);
        $headerMap = $this->getHeaderMap();
        $keyedHeaders = $this->mapHeadersToInternalKeys($headers, $headerMap);
        $this->line('Detected columns: ' . implode(', ', array_values($headers)));

        // Calculate how many rows remain after the header row.
        $startRow = $headerRowIndex + 1;
        $totalRows = count($rows) - $startRow + 1;
        if ($totalRows <= 0) {
            $this->warn('No data rows found after header.');

            return self::SUCCESS;
        }

        // Provide CLI feedback on long-running imports.
        $progress = $this->output->createProgressBar($totalRows);
        $progress->start();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $variantCreated = 0;
        $variantUpdated = 0;
        $variantSkipped = 0;

        // Iterate each data row, skipping empty ones and logging errors per row.
        $rowCount = count($rows);
        for ($i = $startRow; $i < $rowCount; $i++) {
            $row = $rows[$i] ?? null;
            if (! is_array($row)) {
                $skipped++;
                $progress->advance();

                continue;
            }

            if ($this->rowIsEmpty($row)) {
                $skipped++;
                $progress->advance();

                continue;
            }

            $item = $this->rowToAssoc($row, $keyedHeaders);
            $name = $this->normalizeStringValue($item['name'] ?? null);
            if ($name === '') {
                $skipped++;
                $progress->advance();

                continue;
            }

            try {
                $result = $this->upsertProductFromItem($item, $delimiter, $vat);
                $status = $result['status'] ?? 'skipped';
                $variantStatus = $result['variant_status'] ?? null;

                if ($status === 'created') {
                    $created++;
                } elseif ($status === 'updated') {
                    $updated++;
                } else {
                    $skipped++;
                }

                if ($variantStatus === 'created') {
                    $variantCreated++;
                } elseif ($variantStatus === 'updated') {
                    $variantUpdated++;
                } elseif ($variantStatus === 'skipped' || $variantStatus === null) {
                    $variantSkipped++;
                }
            } catch (Throwable $e) {
                $errors++;
                Log::error('[products:import] row ' . $i . ' error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->warn("Row {$i} failed: " . $e->getMessage());
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);
        $this->table(
            ['Created', 'Updated', 'Skipped', 'Errors', 'Variants +', 'Variants ~', 'Variants ·'],
            [[
                (string) $created,
                (string) $updated,
                (string) $skipped,
                (string) $errors,
                (string) $variantCreated,
                (string) $variantUpdated,
                (string) $variantSkipped,
            ]]
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Detects the relevant table names, throwing when core tables are missing.
     */
    protected function detectTablesOrExplain(): void
    {
        if (! Schema::hasTable($this->productsTable)) {
            throw new RuntimeException("Products table '{$this->productsTable}' does not exist. Adjust the command if your table name differs.");
        }

        // Prefer common naming conventions but allow fallbacks.
        if (Schema::hasTable('categories')) {
            $this->categoriesTable = 'categories';
        } elseif (Schema::hasTable('product_categories')) {
            $this->categoriesTable = 'product_categories';
        }

        if (Schema::hasTable('category_product')) {
            $this->pivotCategoryProduct = 'category_product';
        } elseif (Schema::hasTable('category_products')) {
            $this->pivotCategoryProduct = 'category_products';
        }

        if (Schema::hasTable('brands')) {
            $this->brandsTable = 'brands';
        }

        if (Schema::hasTable('manufacturers')) {
            $this->manufacturersTable = 'manufacturers';
        }

        if (Schema::hasTable('product_variants')) {
            $this->productVariantsTable = 'product_variants';
        }

        if (Schema::hasTable('attributes')) {
            $this->attributesTable = 'attributes';
        }

        if (Schema::hasTable('attribute_values')) {
            $this->attributeValuesTable = 'attribute_values';
        }

        if (Schema::hasTable('product_attributes')) {
            $this->productAttributesTable = 'product_attributes';
        }

        if (Schema::hasTable('product_variant_attributes')) {
            $this->productVariantAttributesTable = 'product_variant_attributes';
        }
    }

    /**
     * Attempt to detect the header row by scanning for well-known Lithuanian keywords.
     *
     * @param array<int, mixed> $rows
     */
    protected function findHeaderRowIndex(array $rows): ?int
    {
        foreach ($rows as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }

            $values = array_filter(
                array_map(
                    static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value,
                    array_values($row)
                ),
                static fn (mixed $value): bool => $value !== null && $value !== ''
            );

            if ($values === []) {
                continue;
            }

            $glued = implode('|', array_map(
                static fn (mixed $value): string => is_scalar($value) ? mb_strtolower((string) $value, 'UTF-8') : '',
                $values
            ));

            if (str_contains($glued, 'pavadinimas') || str_contains($glued, 'nuotrauka')) {
                return (int) $idx;
            }
        }

        return null;
    }

    /**
     * Lowercase and trim headers so they can be matched against our mapping.
     *
     * @param  array<int|string, mixed> $headerRow
     * @return array<string, string>
     */
    protected function normalizeHeaders(array $headerRow): array
    {
        $headers = [];
        foreach ($headerRow as $col => $value) {
            $normalised = $this->normalizeStringValue($value);
            if ($normalised === '') {
                continue;
            }

            $headers[(string) $col] = mb_strtolower($normalised, 'UTF-8');
        }

        return $headers;
    }

    /**
     * Prepares the header mapping table used to link sheet columns with internal keys.
     *
     * @return array<string, string>
     */
    protected function getHeaderMap(): array
    {
        $base = [
            'nuotrauka'               => 'image',
            'pavadinimas'             => 'name',
            'aprašymas'               => 'description',
            'kiekis pakuotėje'        => 'pack_qty',
            'matmenys'                => 'dimensions',
            'spalva'                  => 'color',
            'dydis'                   => 'size',
            'svoris'                  => 'weight',
            'medžiaga'                => 'material',
            'pakuotės ilgis'          => 'pkg_length',
            'pakuotės plotis'         => 'pkg_width',
            'pauotės aukštis'         => 'pkg_height',
            'produkto nuoroda'        => 'product_url',
            'kaina be pvm'            => 'price_ex_vat',
            'gamintojas'              => 'manufacturer',
            'kategorija / sub'        => 'category_path',
            'kategorija 2'            => 'category2',
            'kategorija 3'            => 'category3',
            'kategorija 4'            => 'category4',
            'kategorija 5'            => 'category5',
            'katalogai atsisiuntimui' => 'catalog_urls',
            'ean'                     => 'ean',
            'barcode'                 => 'ean',
            'sku'                     => 'sku',
        ];

        // Create ASCII variants to improve matching resilience.
        $map = $base;
        foreach ($base as $key => $value) {
            $ascii = Str::ascii($key);
            if (! array_key_exists($ascii, $map)) {
                $map[$ascii] = $value;
            }
        }

        return $map;
    }

    /**
     * Translate spreadsheet headers into our internal keys, allowing for diacritics.
     *
     * @param  array<string, string> $headers
     * @param  array<string, string> $headerMap
     * @return array<string, string>
     */
    protected function mapHeadersToInternalKeys(array $headers, array $headerMap): array
    {
        $keyed = [];
        foreach ($headers as $col => $header) {
            $normalized = $this->normalizeLithuanianKey($header);
            $internal = $headerMap[$header] ?? $headerMap[$normalized] ?? $normalized;
            $keyed[$col] = $internal;
        }

        return $keyed;
    }

    /**
     * Normalise a header key by lowercasing, stripping diacritics, and collapsing spaces.
     */
    protected function normalizeLithuanianKey(string $key): string
    {
        $ascii = Str::ascii(mb_strtolower($key, 'UTF-8'));

        return preg_replace('/\s+/', ' ', trim($ascii)) ?? '';
    }

    /**
     * Determine whether the provided row is completely empty.
     *
     * @param array<int|string, mixed> $row
     */
    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->nullableStringValue($value) !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert a spreadsheet row into a key/value array using the resolved headers.
     *
     * @param  array<int|string, mixed> $row
     * @param  array<string, string>    $keyedHeaders
     * @return array<string, mixed>
     */
    protected function rowToAssoc(array $row, array $keyedHeaders): array
    {
        $assoc = [];
        foreach ($keyedHeaders as $col => $key) {
            $assoc[$key] = array_key_exists($col, $row) ? $this->castCell($row[$col]) : null;
        }

        return $assoc;
    }

    /**
     * Apply lightweight casting to spreadsheet cell values.
     */
    protected function castCell(mixed $value): mixed
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        if (is_int($value) || is_float($value)) {
            return $value + 0;
        }

        if (is_bool($value)) {
            return $value;
        }

        return $value ?? null;
    }

    /**
     * Collect extended item attributes (colour, size, etc.) into a consistent structure.
     *
     * @param  array<string, mixed> $item
     * @return array<string, mixed>
     */
    protected function collectItemAttributes(array $item): array
    {
        $rawSku = $this->nullableStringValue($item['sku'] ?? null);

        $attributes = [
            'ean'          => $this->nullableStringValue($item['ean'] ?? null),
            'source_sku'   => $rawSku,
            'sku'          => $rawSku,
            'dimensions'   => $this->nullableStringValue($item['dimensions'] ?? null),
            'color'        => $this->nullableStringValue($item['color'] ?? null),
            'size'         => $this->nullableStringValue($item['size'] ?? null),
            'weight'       => $this->nullableStringValue($item['weight'] ?? null),
            'material'     => $this->nullableStringValue($item['material'] ?? null),
            'pack_qty'     => $this->nullableStringValue($item['pack_qty'] ?? null),
            'pkg_length'   => $this->nullableStringValue($item['pkg_length'] ?? null),
            'pkg_width'    => $this->nullableStringValue($item['pkg_width'] ?? null),
            'pkg_height'   => $this->nullableStringValue($item['pkg_height'] ?? null),
            'product_url'  => $this->nullableStringValue($item['product_url'] ?? null),
            'catalog_urls' => $this->nullableStringValue($item['catalog_urls'] ?? null),
        ];

        $knownKeys = [
            'image',
            'name',
            'description',
            'price_ex_vat',
            'manufacturer',
            'category_path',
            'category2',
            'category3',
            'category4',
            'category5',
            'kaina be pvm',
        ];

        foreach ($item as $key => $value) {
            if (in_array($key, $knownKeys, true)) {
                continue;
            }

            if (! array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->nullableStringValue($value);
            }
        }

        return $attributes;
    }

    /**
     * Insert or update a product row derived from the spreadsheet entry and hydrate variants.
     *
     * @param  array<string, mixed>                                                                 $item
     * @return array{status:string, product_id:?string, variant_status:?string, variant_id:?string}
     */
    protected function upsertProductFromItem(array $item, string $delimiter, float $vat): array
    {
        $productsTable = $this->productsTable;
        /** @var array<int, string> $columns */
        $columns = Schema::getColumnListing($productsTable);
        $now = Carbon::now();

        // Normalise common fields up front so downstream helpers work with clean data.
        $name = $this->normalizeStringValue($item['name'] ?? null);
        $desc = $this->normalizeStringValue($item['description'] ?? null);
        $manufacturerName = $this->nullableStringValue($item['manufacturer'] ?? null);

        // Build or locate category entries before the product so pivoting succeeds.
        $categoryPath = $this->extractCategoryPath($item, $delimiter);
        $categoryId = $categoryPath !== null ? $this->ensureCategoryPath($categoryPath, $delimiter) : null;

        $brandId = null;
        if ($manufacturerName !== null) {
            $brandId = $this->ensureBrandOrManufacturer($manufacturerName);
        }

        // Gather extended attributes (colour, size, custom columns) into a single bag.
        $attributes = $this->collectItemAttributes($item);

        $priceEx = $this->toFloat($item['price_ex_vat'] ?? null);

        $pkName = $this->primaryKeyOf($productsTable) ?? 'id';
        $providedSku = $this->normalizeSku($attributes['sku'] ?? null);
        $existing = null;

        if ($providedSku !== null && in_array('sku', $columns, true)) {
            $existing = DB::table($productsTable)->where('sku', $providedSku)->first();
        }

        $existingId = null;
        if ($existing !== null) {
            $existingId = $existing->{$pkName} ?? null;
        }

        // Generate a slug that honours database limits while staying deterministic.
        $slug = null;
        if (in_array('slug', $columns, true)) {
            if ($existing !== null && isset($existing->slug) && $existing->slug !== null) {
                $slug = (string) $existing->slug;
            } else {
                $slugBase = $this->buildProductSlugBase($name, $manufacturerName);
                $slug = $this->generateUniqueSlug($productsTable, $slugBase, $existingId !== null ? (string) $existingId : null);
            }
        }

        // Resolve a SKU that satisfies unique constraints regardless of sheet quality.
        $sku = null;
        if (in_array('sku', $columns, true)) {
            $sku = $this->resolveSkuForTable(
                $productsTable,
                $providedSku,
                $name,
                $attributes,
                $existingId !== null ? (string) $existingId : null
            );

            if ($sku !== null) {
                $attributes['sku'] = $sku;
            }
        }

        // Assemble the payload, guarding each assignment with schema checks.
        $payload = [];
        $this->putIfColumnExists($payload, $columns, 'name', $name);
        $this->putIfColumnExists($payload, $columns, 'slug', $slug);
        $this->putIfColumnExists($payload, $columns, 'description', $desc);
        $this->putIfColumnExists($payload, $columns, 'price', $priceEx);
        $this->putIfColumnExists($payload, $columns, 'price_ex_vat', $priceEx);

        if (in_array('price_incl_vat', $columns, true)) {
            $this->putIfColumnExists(
                $payload,
                $columns,
                'price_incl_vat',
                $priceEx !== null ? round($priceEx * (1 + $vat / 100), 2) : null
            );
        }

        $this->putIfColumnExists($payload, $columns, 'weight', $this->toFloat($item['weight'] ?? null));
        $this->putIfColumnExists($payload, $columns, 'dimensions', $attributes['dimensions'] ?? null);
        $this->putIfColumnExists($payload, $columns, 'image_url', $item['image'] ?? null);
        $this->putIfColumnExists($payload, $columns, 'external_url', $item['product_url'] ?? null);

        if ($categoryId !== null && in_array('category_id', $columns, true)) {
            $payload['category_id'] = $categoryId;
        }

        if ($brandId !== null) {
            if ($this->brandsTable !== null && in_array('brand_id', $columns, true)) {
                $payload['brand_id'] = $brandId;
            } elseif ($this->manufacturersTable !== null && in_array('manufacturer_id', $columns, true)) {
                $payload['manufacturer_id'] = $brandId;
            } elseif (in_array('manufacturer', $columns, true) && $manufacturerName !== null) {
                $payload['manufacturer'] = $manufacturerName;
            }
        } elseif (in_array('manufacturer', $columns, true) && $manufacturerName !== null) {
            $payload['manufacturer'] = $manufacturerName;
        }

        if (in_array('attributes', $columns, true)) {
            $payload['attributes'] = json_encode(
                array_filter($attributes, static fn (mixed $value): bool => $value !== null && $value !== ''),
                JSON_UNESCAPED_UNICODE
            );
        }

        if ($sku !== null) {
            $payload['sku'] = $sku;
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = $now;
        }

        $uniqueField = null;
        if (in_array('sku', $columns, true) && $sku !== null) {
            $uniqueField = 'sku';
        } elseif (in_array('slug', $columns, true) && $slug !== null) {
            $uniqueField = 'slug';
        } elseif (in_array('name', $columns, true) && $name !== '') {
            $uniqueField = 'name';
        } else {
            throw new RuntimeException("Could not determine unique field for upsert in table '{$productsTable}'.");
        }

        $uniqueValue = $payload[$uniqueField] ?? null;
        if (! is_string($uniqueValue) || $uniqueValue === '') {
            throw new RuntimeException("Resolved unique field '{$uniqueField}' is empty for '{$productsTable}'.");
        }

        $existing = DB::table($productsTable)->where($uniqueField, $uniqueValue)->first();
        if ($existing !== null) {
            $existingId = $existing->{$pkName} ?? null;
        }

        if ($existing !== null) {
            DB::table($productsTable)->where($pkName, $existing->{$pkName})->update($payload);
            $id = $existing->{$pkName} ?? null;
            $action = 'updated';
        } else {
            if (in_array('created_at', $columns, true)) {
                $payload['created_at'] = $now;
            }

            $inserted = DB::table($productsTable)->insertGetId($payload, $pkName);
            if (! $inserted) {
                DB::table($productsTable)->insert($payload);
                $row = DB::table($productsTable)->where($uniqueField, $uniqueValue)->first();
                $inserted = $row->{$pkName} ?? null;
            }

            $id = $inserted;
            $action = 'created';
        }

        if ($id !== null && $categoryId !== null && $this->pivotCategoryProduct !== null && (is_string($id) || is_int($id))) {
            $this->ensurePivot((string) $id, $categoryId);
        }

        $variantOutcome = null;
        if ($id !== null && (is_string($id) || is_int($id))) {
            $variantOutcome = $this->syncVariantData(
                (string) $id,
                $name,
                $attributes,
                $item,
                $priceEx,
                $vat,
                $sku
            );
        }

        return [
            'status'         => $action,
            'product_id'     => is_string($id) || is_int($id) ? (string) $id : null,
            'variant_status' => $variantOutcome['status'] ?? null,
            'variant_id'     => $variantOutcome['variant_id'] ?? null,
        ];
    }

    /**
     * Resolve the category path, building it from multiple columns when needed.
     *
     * @param array<string, mixed> $item
     */
    protected function extractCategoryPath(array $item, string $delimiter): ?string
    {
        $path = $this->nullableStringValue($item['category_path'] ?? null);
        if ($path === null && (! empty($item['category2']) || ! empty($item['category3']) || ! empty($item['category4']) || ! empty($item['category5']))) {
            $parts = array_filter([
                $this->nullableStringValue($item['category2'] ?? null),
                $this->nullableStringValue($item['category3'] ?? null),
                $this->nullableStringValue($item['category4'] ?? null),
                $this->nullableStringValue($item['category5'] ?? null),
            ]);
            $path = implode(" {$delimiter} ", array_map(static fn (?string $segment): string => $segment ?? '', $parts));
        }

        return $path !== null && $path !== '' ? $path : null;
    }

    /**
     * Ensure the entire category path exists, returning the deepest category identifier.
     */
    protected function ensureCategoryPath(string $path, string $delimiter = '/'): ?string
    {
        if ($this->categoriesTable === null || $path === '') {
            return null;
        }

        if ($delimiter === '') {
            $delimiter = '/';
        }

        $segments = array_values(array_filter(array_map(
            static fn (string $segment): string => trim($segment),
            explode($delimiter, $path)
        )));

        if ($segments === []) {
            return null;
        }

        $table = $this->categoriesTable;
        $pk = $this->primaryKeyOf($table) ?? 'id';
        $parentId = null;
        $traversed = [];

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            $traversed[] = mb_strtolower($segment, 'UTF-8');
            $cacheKey = implode('>', $traversed);

            if (isset($this->categoryCache[$cacheKey])) {
                $parentId = $this->categoryCache[$cacheKey];

                continue;
            }

            // Attempt to reuse an existing slug or matching name under the same parent.
            $slugCandidate = Str::slug($segment);
            $existing = null;

            if ($slugCandidate !== '') {
                $existing = DB::table($table)->where('slug', $slugCandidate)->first();
            }

            if ($existing === null) {
                $query = DB::table($table)
                    ->when(
                        $parentId,
                        static fn (Builder $builder, string $id): Builder => $builder->where('parent_id', $id),
                        static fn (Builder $builder): Builder => $builder->whereNull('parent_id')
                    );

                if (Schema::hasColumn($table, 'name')) {
                    $query->where('name', $segment);
                }

                $existing = $query->first();
            }

            if ($existing !== null) {
                $resolvedId = $existing->{$pk} ?? null;
                if ($resolvedId !== null) {
                    $parentId = (string) $resolvedId;
                    $this->categoryCache[$cacheKey] = (string) $parentId;

                    continue;
                }
            }

            $slug = $this->generateUniqueSlug($table, $segment);

            $insert = ['slug' => $slug];
            if (Schema::hasColumn($table, 'name')) {
                $insert['name'] = $segment;
            }
            if (Schema::hasColumn($table, 'parent_id')) {
                $insert['parent_id'] = $parentId;
            }
            if (Schema::hasColumn($table, 'created_at')) {
                $insert['created_at'] = Carbon::now();
            }
            if (Schema::hasColumn($table, 'updated_at')) {
                $insert['updated_at'] = Carbon::now();
            }

            $newId = DB::table($table)->insertGetId($insert, $pk);
            if (! $newId) {
                DB::table($table)->insert($insert);
                $row = DB::table($table)->where('slug', $slug)->first();
                $newId = $row->{$pk} ?? null;
            }

            if ($newId === null) {
                return null;
            }

            $parentId = (string) $newId;
            $this->categoryCache[$cacheKey] = $parentId;
        }

        return $parentId;
    }

    /**
     * Ensure brand or manufacturer records exist, returning their identifier.
     */
    protected function ensureBrandOrManufacturer(string $name): ?string
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $slug = Str::slug($name);
        $table = $this->brandsTable ?? $this->manufacturersTable;
        if ($table === null) {
            return null;
        }

        $pk = $this->primaryKeyOf($table) ?? 'id';
        $existing = DB::table($table)
            ->where(function (Builder $builder) use ($table, $slug, $name): void {
                $hasSlug = Schema::hasColumn($table, 'slug');
                $hasName = Schema::hasColumn($table, 'name');

                if ($hasSlug) {
                    $builder->where('slug', $slug);
                }

                if ($hasName) {
                    if ($hasSlug) {
                        $builder->orWhere('name', $name);
                    } else {
                        $builder->where('name', $name);
                    }
                }
            })
            ->first();

        if ($existing !== null) {
            $identifier = $existing->{$pk} ?? null;

            if (is_string($identifier) || is_int($identifier)) {
                return (string) $identifier;
            }

            return null;
        }

        $insert = [];
        if (Schema::hasColumn($table, 'name')) {
            $insert['name'] = $name;
        }

        if (Schema::hasColumn($table, 'slug')) {
            $insert['slug'] = $slug;
        }

        if (Schema::hasColumn($table, 'created_at')) {
            $insert['created_at'] = Carbon::now();
        }

        if (Schema::hasColumn($table, 'updated_at')) {
            $insert['updated_at'] = Carbon::now();
        }

        $newId = DB::table($table)->insertGetId($insert, $pk);
        if (! $newId) {
            DB::table($table)->insert($insert);
            $row = DB::table($table)
                ->where(function (Builder $builder) use ($table, $slug, $name): void {
                    $hasSlug = Schema::hasColumn($table, 'slug');
                    $hasName = Schema::hasColumn($table, 'name');

                    if ($hasSlug) {
                        $builder->where('slug', $slug);
                    }

                    if ($hasName) {
                        if ($hasSlug) {
                            $builder->orWhere('name', $name);
                        } else {
                            $builder->where('name', $name);
                        }
                    }
                })
                ->first();
            $newId = $row->{$pk} ?? null;
        }

        if ($newId === null) {
            return null;
        }

        if (is_string($newId) || is_int($newId)) {
            return (string) $newId;
        }

        return null;
    }

    /**
     * Create or update product variant records for the imported row.
     *
     * @param  array<string, mixed>                     $attributes
     * @param  array<string, mixed>                     $item
     * @return array{status:string, variant_id:?string}
     */
    protected function syncVariantData(
        string $productId,
        string $productName,
        array $attributes,
        array $item,
        ?float $priceEx,
        float $vat,
        ?string $productSku
    ): array {
        if ($this->productVariantsTable === null) {
            return ['status' => 'skipped', 'variant_id' => null];
        }

        $table = $this->productVariantsTable;
        /** @var array<int, string> $columns */
        $columns = Schema::getColumnListing($table);

        if (! in_array('product_id', $columns, true) || ! in_array('sku', $columns, true)) {
            return ['status' => 'skipped', 'variant_id' => null];
        }

        $variantOptions = $this->extractVariantOptions($attributes, $item);
        $variantName = $this->buildVariantName($productName, $variantOptions);

        $providedVariantSku = $this->normalizeSku($item['variant_sku'] ?? null);
        $variantSkuSeed = $this->resolveVariantSkuSeed($productSku, $variantOptions, $productName, $attributes, $providedVariantSku);

        $pk = $this->primaryKeyOf($table) ?? 'id';
        $existing = DB::table($table)->where('sku', $variantSkuSeed)->first();
        if ($existing === null && in_array('variant_combination_hash', $columns, true)) {
            $hashSeed = $this->buildVariantCombinationHash($productId, $variantOptions, $variantSkuSeed);
            $existing = DB::table($table)
                ->where('product_id', $productId)
                ->where('variant_combination_hash', $hashSeed)
                ->first();
        }

        $ignoreId = null;
        if ($existing !== null) {
            $existingId = $existing->{$pk} ?? null;
            if ($existingId !== null) {
                $ignoreId = (string) $existingId;
            }

            if (isset($existing->sku)) {
                $variantSkuSeed = (string) $existing->sku;
            }
        }

        $variantSku = $this->ensureUniqueColumnValue($table, 'sku', $variantSkuSeed, $ignoreId, 64);
        $variantHash = $this->buildVariantCombinationHash($productId, $variantOptions, $variantSku);

        $variantPayload = [];
        $this->putIfColumnExists($variantPayload, $columns, 'product_id', $productId);
        $this->putIfColumnExists($variantPayload, $columns, 'name', $variantName);
        $this->putIfColumnExists($variantPayload, $columns, 'sku', $variantSku);
        $this->putIfColumnExists($variantPayload, $columns, 'barcode', $attributes['ean'] ?? null);
        $this->putIfColumnExists($variantPayload, $columns, 'price', $priceEx ?? 0.0);
        $this->putIfColumnExists($variantPayload, $columns, 'cost_price', $priceEx);
        $this->putIfColumnExists($variantPayload, $columns, 'weight', $this->toFloat($attributes['weight'] ?? null));

        if (in_array('price_incl_vat', $columns, true)) {
            $this->putIfColumnExists(
                $variantPayload,
                $columns,
                'price_incl_vat',
                $priceEx !== null ? round($priceEx * (1 + $vat / 100), 2) : null
            );
        }

        if (in_array('attributes', $columns, true)) {
            $variantPayload['attributes'] = json_encode($variantOptions, JSON_UNESCAPED_UNICODE);
        }

        if (in_array('variant_attribute_matrix', $columns, true)) {
            $variantPayload['variant_attribute_matrix'] = json_encode(['options' => $variantOptions], JSON_UNESCAPED_UNICODE);
        }

        if (in_array('variant_combination_hash', $columns, true)) {
            $variantPayload['variant_combination_hash'] = $variantHash;
        }

        if (in_array('is_enabled', $columns, true)) {
            $variantPayload['is_enabled'] = true;
        }

        if (in_array('track_inventory', $columns, true)) {
            $variantPayload['track_inventory'] = false;
        }

        if (in_array('is_default', $columns, true)) {
            $isFirstVariant = DB::table($table)->where('product_id', $productId)->count() === 0;
            $variantPayload['is_default'] = $existing !== null ? (bool) ($existing->is_default ?? false) : $isFirstVariant;
        }

        if (in_array('updated_at', $columns, true)) {
            $variantPayload['updated_at'] = Carbon::now();
        }

        $variantId = null;
        $status = 'skipped';

        if ($existing !== null) {
            DB::table($table)->where($pk, $existing->{$pk})->update($variantPayload);
            $variantId = $existing->{$pk} ?? null;
            $status = 'updated';
        } else {
            if (in_array('created_at', $columns, true)) {
                $variantPayload['created_at'] = Carbon::now();
            }

            $inserted = DB::table($table)->insertGetId($variantPayload, $pk);
            if (! $inserted) {
                DB::table($table)->insert($variantPayload);
                $row = DB::table($table)->where('sku', $variantSku)->first();
                $inserted = $row->{$pk} ?? null;
            }

            $variantId = $inserted;
            $status = 'created';
        }

        if ($variantId !== null) {
            $variantId = (string) $variantId;
            $this->syncAttributeAssignments($productId, $variantId, $variantOptions);
        }

        return [
            'status'     => $status,
            'variant_id' => $variantId,
        ];
    }

    /**
     * Extract meaningful variant options from product attributes and raw columns.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $item
     * @return array<string, string>
     */
    protected function extractVariantOptions(array $attributes, array $item): array
    {
        $reserved = ['ean', 'sku', 'source_sku', 'product_url', 'catalog_urls', 'manufacturer', 'category_path', 'category2', 'category3', 'category4', 'category5', 'variant_sku'];
        $preferred = ['color', 'size', 'dimensions', 'weight', 'material', 'pack_qty', 'pkg_length', 'pkg_width', 'pkg_height'];

        $options = [];
        foreach ($preferred as $key) {
            $value = $this->nullableStringValue($attributes[$key] ?? null);
            if ($value !== null) {
                $options[$key] = $value;
            }
        }

        foreach ($attributes as $key => $value) {
            if (isset($options[$key])) {
                continue;
            }

            if (in_array($key, $reserved, true)) {
                continue;
            }

            $normalized = $this->nullableStringValue($value);
            if ($normalized !== null) {
                $options[$key] = $normalized;
            }
        }

        return $options;
    }

    /**
     * Build a human friendly variant name by appending formatted option labels.
     *
     * @param array<string, string> $variantOptions
     */
    protected function buildVariantName(string $productName, array $variantOptions): string
    {
        if ($variantOptions === []) {
            return $productName;
        }

        $parts = [];
        foreach ($variantOptions as $key => $value) {
            $parts[] = $this->humanizeVariantKey($key) . ': ' . $value;
        }

        return trim($productName . ' - ' . implode(', ', $parts));
    }

    /**
     * Generate a deterministic variant SKU seed taking the product SKU and options into account.
     *
     * @param array<string, mixed>  $attributes
     * @param array<string, string> $variantOptions
     */
    protected function resolveVariantSkuSeed(
        ?string $productSku,
        array $variantOptions,
        string $productName,
        array $attributes,
        ?string $providedVariantSku
    ): string {
        $candidate = $providedVariantSku;
        if ($candidate === null) {
            $candidate = $this->normalizeSku($attributes['source_sku'] ?? null);
        }

        if ($candidate === null && $productSku !== null) {
            $candidate = $productSku;
        }

        if ($candidate === null) {
            $candidate = $this->generateSkuSeed($productName, $variantOptions) ?? strtoupper(substr(hash('sha1', $productName), 0, 16));
        }

        if ($variantOptions !== []) {
            $suffix = strtoupper(substr(hash('crc32b', json_encode($variantOptions, JSON_UNESCAPED_UNICODE)), 0, 6));
            $candidate = rtrim(substr($candidate, 0, max(1, 48 - 7)), '-') . '-' . $suffix;
        }

        return substr($candidate, 0, 64);
    }

    /**
     * Create a stable hash representing the variant option combination.
     *
     * @param array<string, string> $variantOptions
     */
    protected function buildVariantCombinationHash(string $productId, array $variantOptions, string $sku): string
    {
        return hash('sha1', json_encode([
            'product_id' => $productId,
            'sku'        => $sku,
            'options'    => $variantOptions,
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * Synchronise attribute definitions and pivot relationships for the variant.
     *
     * @param array<string, string> $variantOptions
     */
    protected function syncAttributeAssignments(string $productId, string $variantId, array $variantOptions): void
    {
        if ($variantOptions === []) {
            return;
        }

        foreach ($variantOptions as $key => $value) {
            $label = $this->humanizeVariantKey($key);
            $attributeSlug = Str::slug($label);
            if ($attributeSlug === '') {
                $attributeSlug = Str::slug('option-' . substr(hash('sha1', $key), 0, 8));
            }

            $attributeId = $this->ensureAttributeExists($attributeSlug, $label);
            if ($attributeId === null) {
                continue;
            }

            $valueId = $this->ensureAttributeValueExists($attributeSlug, $attributeId, $value);
            if ($valueId === null) {
                continue;
            }

            $this->syncProductAttributePivot($productId, $attributeId, $valueId);
            $this->syncVariantAttributePivot($variantId, $attributeId, $valueId);
        }
    }

    /**
     * Ensure the attribute definition exists and return its identifier.
     */
    protected function ensureAttributeExists(string $slug, string $name): ?string
    {
        if ($this->attributesTable === null) {
            return null;
        }

        if (isset($this->attributeCache[$slug])) {
            return $this->attributeCache[$slug];
        }

        $table = $this->attributesTable;
        $pk = $this->primaryKeyOf($table) ?? 'id';
        $existing = DB::table($table)->where('slug', $slug)->first();
        if ($existing !== null) {
            $id = $existing->{$pk} ?? null;
            if ($id !== null) {
                $this->attributeCache[$slug] = (string) $id;

                return (string) $id;
            }
        }

        $insert = ['slug' => $slug];
        if (Schema::hasColumn($table, 'name')) {
            $insert['name'] = $name;
        }
        if (Schema::hasColumn($table, 'type')) {
            $insert['type'] = 'select';
        }
        if (Schema::hasColumn($table, 'is_enabled')) {
            $insert['is_enabled'] = true;
        }
        if (Schema::hasColumn($table, 'is_filterable')) {
            $insert['is_filterable'] = true;
        }
        if (Schema::hasColumn($table, 'created_at')) {
            $insert['created_at'] = Carbon::now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $insert['updated_at'] = Carbon::now();
        }

        $id = DB::table($table)->insertGetId($insert, $pk);
        if (! $id) {
            DB::table($table)->insert($insert);
            $row = DB::table($table)->where('slug', $slug)->first();
            $id = $row->{$pk} ?? null;
        }

        if ($id === null) {
            return null;
        }

        $this->attributeCache[$slug] = (string) $id;

        return (string) $id;
    }

    /**
     * Ensure the attribute value exists and return its identifier.
     */
    protected function ensureAttributeValueExists(string $attributeSlug, string $attributeId, string $value): ?string
    {
        if ($this->attributeValuesTable === null) {
            return null;
        }

        $valueSlug = Str::slug($value);
        if ($valueSlug === '') {
            $valueSlug = Str::slug(substr($value, 0, 32)) ?: strtolower(substr(hash('sha1', $value), 0, 12));
        }

        $cacheKey = $attributeSlug . '|' . $valueSlug;
        if (isset($this->attributeValueCache[$cacheKey])) {
            return $this->attributeValueCache[$cacheKey];
        }

        $table = $this->attributeValuesTable;
        $pk = $this->primaryKeyOf($table) ?? 'id';
        $existing = DB::table($table)
            ->where('attribute_id', $attributeId)
            ->where('slug', $valueSlug)
            ->first();

        if ($existing !== null) {
            $id = $existing->{$pk} ?? null;
            if ($id !== null) {
                $this->attributeValueCache[$cacheKey] = (string) $id;

                return (string) $id;
            }
        }

        $insert = [
            'attribute_id' => $attributeId,
            'slug'         => $valueSlug,
        ];

        if (Schema::hasColumn($table, 'value')) {
            $insert['value'] = $value;
        }
        if (Schema::hasColumn($table, 'is_enabled')) {
            $insert['is_enabled'] = true;
        }
        if (Schema::hasColumn($table, 'created_at')) {
            $insert['created_at'] = Carbon::now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $insert['updated_at'] = Carbon::now();
        }

        $id = DB::table($table)->insertGetId($insert, $pk);
        if (! $id) {
            DB::table($table)->insert($insert);
            $row = DB::table($table)
                ->where('attribute_id', $attributeId)
                ->where('slug', $valueSlug)
                ->first();
            $id = $row->{$pk} ?? null;
        }

        if ($id === null) {
            return null;
        }

        $this->attributeValueCache[$cacheKey] = (string) $id;

        return (string) $id;
    }

    /**
     * Attach the product_attributes pivot if necessary.
     */
    protected function syncProductAttributePivot(string $productId, string $attributeId, string $attributeValueId): void
    {
        if ($this->productAttributesTable === null) {
            return;
        }

        $table = $this->productAttributesTable;
        $query = DB::table($table)
            ->where('product_id', $productId)
            ->where('attribute_id', $attributeId);

        if ($query->exists()) {
            if (Schema::hasColumn($table, 'attribute_value_id')) {
                $payload = ['attribute_value_id' => $attributeValueId];
                if (Schema::hasColumn($table, 'updated_at')) {
                    $payload['updated_at'] = Carbon::now();
                }
                $query->update($payload);
            }

            return;
        }

        $payload = [
            'product_id'   => $productId,
            'attribute_id' => $attributeId,
        ];

        if (Schema::hasColumn($table, 'attribute_value_id')) {
            $payload['attribute_value_id'] = $attributeValueId;
        }
        if (Schema::hasColumn($table, 'created_at')) {
            $payload['created_at'] = Carbon::now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = Carbon::now();
        }

        DB::table($table)->insert($payload);
    }

    /**
     * Attach the product_variant_attributes pivot if necessary.
     */
    protected function syncVariantAttributePivot(string $variantId, string $attributeId, string $attributeValueId): void
    {
        if ($this->productVariantAttributesTable === null) {
            return;
        }

        $table = $this->productVariantAttributesTable;
        $query = DB::table($table)
            ->where('variant_id', $variantId)
            ->where('attribute_id', $attributeId);

        if ($query->exists()) {
            $payload = ['attribute_value_id' => $attributeValueId];
            if (Schema::hasColumn($table, 'updated_at')) {
                $payload['updated_at'] = Carbon::now();
            }
            $query->update($payload);

            return;
        }

        $payload = [
            'variant_id'         => $variantId,
            'attribute_id'       => $attributeId,
            'attribute_value_id' => $attributeValueId,
        ];

        if (Schema::hasColumn($table, 'created_at')) {
            $payload['created_at'] = Carbon::now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = Carbon::now();
        }

        DB::table($table)->insert($payload);
    }

    /**
     * Convert a variant key into a human readable label.
     */
    protected function humanizeVariantKey(string $key): string
    {
        $normalized = preg_replace('/[\-_]+/', ' ', $key) ?? $key;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return ucwords(trim($normalized));
    }

    /**
     * Build a deterministic slug seed combining the product name and manufacturer.
     */
    protected function buildProductSlugBase(string $name, ?string $manufacturerName): string
    {
        $parts = array_filter([
            $name,
            $manufacturerName,
        ], static fn (?string $part): bool => $part !== null && trim($part) !== '');

        $base = trim(implode(' ', $parts));

        return $base !== '' ? $base : 'product';
    }

    /**
     * Generate a globally unique slug within the provided table, respecting column limits.
     */
    protected function generateUniqueSlug(string $table, string $base, ?string $ignoreId = null, int $maxLength = 191): string
    {
        $initial = Str::slug($base);
        if ($initial === '') {
            $initial = Str::slug('item-' . substr(hash('sha1', $base), 0, 12));
        }

        $slug = $this->truncateSlug($initial, $maxLength);
        $candidate = $slug;
        $suffix = 1;
        $pk = $this->primaryKeyOf($table) ?? 'id';

        while (
            DB::table($table)
                ->where('slug', $candidate)
                ->when($ignoreId, static fn (Builder $query, string $id): Builder => $query->where($pk, '!=', $id))
                ->exists()
        ) {
            $suffixString = '-' . $suffix++;
            $candidate = $this->truncateSlug($slug, $maxLength - strlen($suffixString)) . $suffixString;
        }

        return $candidate;
    }

    /**
     * Trim a slug to the desired length without leaving dangling separators.
     */
    protected function truncateSlug(string $slug, int $maxLength): string
    {
        if (strlen($slug) <= $maxLength) {
            return $slug;
        }

        $trimmed = substr($slug, 0, $maxLength);

        return rtrim($trimmed, '-_');
    }

    /**
     * Normalise SKU strings, stripping unsupported characters and enforcing length.
     */
    protected function normalizeSku(mixed $value): ?string
    {
        $stringValue = $this->nullableStringValue($value);
        if ($stringValue === null) {
            return null;
        }

        $ascii = Str::upper(Str::ascii($stringValue));
        $sanitised = preg_replace('/[^A-Z0-9-]+/', '', $ascii) ?? '';
        $sanitised = trim($sanitised, '-');

        if ($sanitised === '') {
            return null;
        }

        return substr($sanitised, 0, 64);
    }

    /**
     * Resolve a SKU for the products table, falling back to generated seeds when needed.
     *
     * @param array<string, mixed> $attributes
     */
    protected function resolveSkuForTable(string $table, ?string $providedSku, string $name, array $attributes, ?string $ignoreId): ?string
    {
        $candidate = $providedSku;
        if ($candidate === null || $candidate === '') {
            $seed = $this->generateSkuSeed($name, $attributes);
            if ($seed === null) {
                return null;
            }

            $candidate = $seed;
        }

        return $this->ensureUniqueColumnValue($table, 'sku', $candidate, $ignoreId, 64);
    }

    /**
     * Build a deterministic SKU seed using the product name and high-signal attributes.
     *
     * @param array<string, mixed> $attributes
     */
    protected function generateSkuSeed(string $name, array $attributes): ?string
    {
        $base = preg_replace('/[^A-Z0-9]+/', '', Str::upper(Str::ascii($name))) ?? '';
        $base = substr($base, 0, 24);

        $descriptorParts = [];
        foreach (['color', 'size', 'pack_qty', 'material'] as $key) {
            $value = $this->nullableStringValue($attributes[$key] ?? null);
            if ($value !== null) {
                $descriptorParts[] = preg_replace('/[^A-Z0-9]+/', '', Str::upper(Str::ascii($value))) ?? '';
            }
        }

        $descriptorParts = array_filter($descriptorParts, static fn (string $part): bool => $part !== '');

        $candidate = $base;
        if ($descriptorParts !== []) {
            $candidate = trim($base . '-' . implode('-', $descriptorParts), '-');
        }

        if ($candidate === '') {
            return strtoupper(substr(hash('sha1', $name . json_encode($attributes, JSON_UNESCAPED_UNICODE)), 0, 16));
        }

        return substr($candidate, 0, 64);
    }

    /**
     * Ensure a given column value is unique within the specified table.
     */
    protected function ensureUniqueColumnValue(string $table, string $column, string $candidate, ?string $ignoreId, int $maxLength): string
    {
        $value = substr($candidate, 0, $maxLength);
        if ($value === '') {
            $value = strtoupper(substr(hash('sha1', $candidate . $table . $column), 0, $maxLength));
        }

        $pk = $this->primaryKeyOf($table) ?? 'id';
        $suffix = 1;

        while (
            DB::table($table)
                ->where($column, $value)
                ->when($ignoreId, static fn (Builder $query, string $id): Builder => $query->where($pk, '!=', $id))
                ->exists()
        ) {
            $suffixString = '-' . $suffix++;
            $value = substr($candidate, 0, max(1, $maxLength - strlen($suffixString))) . $suffixString;
        }

        return $value;
    }

    /**
     * Attach the pivot relation if the link does not exist yet.
     */
    protected function ensurePivot(string $productId, string $categoryId): void
    {
        $table = $this->pivotCategoryProduct;
        if ($table === null) {
            return;
        }

        $productFk = Schema::hasColumn($table, 'product_id') ? 'product_id' : null;
        $categoryFk = Schema::hasColumn($table, 'category_id') ? 'category_id' : null;
        if ($productFk === null || $categoryFk === null) {
            return;
        }

        $exists = DB::table($table)
            ->where($productFk, $productId)
            ->where($categoryFk, $categoryId)
            ->exists();

        if (! $exists) {
            DB::table($table)->insert([
                $productFk  => $productId,
                $categoryFk => $categoryId,
            ]);
        }
    }

    /**
     * Heuristic to detect the primary key column for the given table.
     */
    protected function primaryKeyOf(string $table): ?string
    {
        foreach (['id', "{$table}_id", 'uuid', 'ulid'] as $pk) {
            if (Schema::hasColumn($table, $pk)) {
                return $pk;
            }
        }

        return null;
    }

    /**
     * Add a value to the payload if the column exists in the schema.
     *
     * @param array<string, mixed> $payload
     * @param array<int, string>   $columns
     */
    protected function putIfColumnExists(array &$payload, array $columns, string $column, mixed $value): void
    {
        if (in_array($column, $columns, true) && $value !== null && $value !== '') {
            $payload[$column] = $value;
        }
    }

    /**
     * Convert common spreadsheet number formats into floats.
     */
    protected function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $stringValue = preg_replace('/[^\d,.-]+/', '', $value) ?? '';
            if (str_contains($stringValue, ',') && ! str_contains($stringValue, '.')) {
                $stringValue = str_replace(',', '.', $stringValue);
            } else {
                $stringValue = str_replace(',', '', $stringValue);
            }

            return is_numeric($stringValue) ? (float) $stringValue : null;
        }

        return null;
    }

    /**
     * Normalise arbitrary values into trimmed strings.
     */
    protected function normalizeStringValue(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_int($value) || is_float($value)) {
            return trim((string) $value);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return '';
    }

    /**
     * Convert arbitrary values into trimmed strings, returning null when the result is empty.
     */
    protected function nullableStringValue(mixed $value): ?string
    {
        $normalized = $this->normalizeStringValue($value);

        return $normalized === '' ? null : $normalized;
    }
}
