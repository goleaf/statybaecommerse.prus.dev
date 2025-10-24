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
                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                } else {
                    $skipped++;
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
        $this->table(['Created', 'Updated', 'Skipped', 'Errors'], [[(string) $created, (string) $updated, (string) $skipped, (string) $errors]]);

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
     * Insert or update a product row derived from the spreadsheet entry.
     *
     * @param array<string, mixed> $item
     */
    protected function upsertProductFromItem(array $item, string $delimiter, float $vat): string
    {
        $productsTable = $this->productsTable;
        /** @var array<int, string> $columns */
        $columns = Schema::getColumnListing($productsTable);
        $now = Carbon::now();

        $name = $this->normalizeStringValue($item['name'] ?? null);
        $desc = $this->normalizeStringValue($item['description'] ?? null);
        $manufacturerName = $this->nullableStringValue($item['manufacturer'] ?? null);

        // Resolve category hierarchy and ensure related records exist.
        $categoryPath = $this->extractCategoryPath($item, $delimiter);
        $categoryId = null;
        if ($this->categoriesTable !== null && $categoryPath !== null) {
            $categoryId = $this->ensureCategoryPath($categoryPath, $delimiter);
        }

        // Handle brand/manufacturer inference.
        $brandId = null;
        if ($manufacturerName !== null) {
            $brandId = $this->ensureBrandOrManufacturer($manufacturerName);
        }

        // Collect attributes used for metadata or uniqueness.
        $knownKeys = [
            'image',
            'name',
            'description',
            'pack_qty',
            'dimensions',
            'color',
            'size',
            'weight',
            'material',
            'pkg_length',
            'pkg_width',
            'pkg_height',
            'product_url',
            'price_ex_vat',
            'manufacturer',
            'category_path',
            'category2',
            'category3',
            'category4',
            'category5',
            'catalog_urls',
            'ean',
            'sku',
        ];

        $attributes = [
            'ean'          => $this->nullableStringValue($item['ean'] ?? null),
            'sku'          => $this->nullableStringValue($item['sku'] ?? null),
            'dimensions'   => $item['dimensions'] ?? null,
            'color'        => $item['color'] ?? null,
            'size'         => $item['size'] ?? null,
            'pack_qty'     => $item['pack_qty'] ?? null,
            'material'     => $item['material'] ?? null,
            'pkg_length'   => $item['pkg_length'] ?? null,
            'pkg_width'    => $item['pkg_width'] ?? null,
            'pkg_height'   => $item['pkg_height'] ?? null,
            'product_url'  => $item['product_url'] ?? null,
            'catalog_urls' => $item['catalog_urls'] ?? null,
        ];

        foreach ($item as $key => $value) {
            if (! in_array($key, $knownKeys, true) && $value !== null && $value !== '') {
                $attributes[$key] = $value;
            }
        }

        // Build a slug using meaningful attributes to help deduplicate similar entries.
        $uniqueKeyParts = [$name];
        foreach (['dimensions', 'color', 'size', 'pack_qty'] as $attributeKey) {
            $attributeValue = $this->normalizeStringValue($attributes[$attributeKey] ?? null);
            if ($attributeValue !== '') {
                $uniqueKeyParts[] = $attributeValue;
            }
        }

        $slug = Str::slug(implode(' ', array_filter($uniqueKeyParts, static fn (string $part): bool => $part !== '')));

        // Parse price and convert VAT-inclusive value when supported by the schema.
        $priceEx = $this->toFloat($item['price_ex_vat'] ?? null);

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

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = $now;
        }

        // Choose the most reliable unique field, preferring SKU.
        $uniqueField = null;
        $skuFromSheet = $attributes['sku'] ?? null;
        if (in_array('sku', $columns, true) && is_string($skuFromSheet) && $skuFromSheet !== '') {
            $uniqueField = 'sku';
            $payload['sku'] = $skuFromSheet;
        } elseif (in_array('sku', $columns, true)) {
            $generatedSkuBase = preg_replace('/[^A-Za-z0-9]+/', '', $slug) ?? '';
            $payload['sku'] = strtoupper(substr($generatedSkuBase, 0, 48));
            $uniqueField = 'sku';
        } elseif (in_array('slug', $columns, true)) {
            $uniqueField = 'slug';
        } elseif (in_array('name', $columns, true)) {
            $uniqueField = 'name';
        } else {
            throw new RuntimeException("Could not determine unique field for upsert in table '{$productsTable}'.");
        }

        // Perform the upsert using the detected primary key.
        $existing = DB::table($productsTable)->where($uniqueField, $payload[$uniqueField])->first();
        if ($existing !== null) {
            $pkName = $this->primaryKeyOf($productsTable) ?? 'id';
            DB::table($productsTable)->where($pkName, $existing->{$pkName})->update($payload);
            $id = $existing->{$pkName} ?? null;
            $action = 'updated';
        } else {
            if (in_array('created_at', $columns, true)) {
                $payload['created_at'] = $now;
            }

            // insertGetId might not return ULID/UUID, so fall back to select-after-insert.
            $primaryKey = $this->primaryKeyOf($productsTable) ?? 'id';
            $inserted = DB::table($productsTable)->insertGetId($payload, $primaryKey);
            if (! $inserted) {
                DB::table($productsTable)->insert($payload);
                $row = DB::table($productsTable)->where($uniqueField, $payload[$uniqueField])->first();
                $inserted = $row->{$primaryKey} ?? null;
            }

            $id = $inserted;
            $action = 'created';
        }

        // Attach the pivot relationship when both identifiers exist.
        if ($id !== null && $categoryId !== null && $this->pivotCategoryProduct !== null && (is_string($id) || is_int($id))) {
            $this->ensurePivot((string) $id, $categoryId);
        }

        return $action;
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
        if ($this->categoriesTable === null) {
            return null;
        }

        if ($path === '') {
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

        $parentId = null;
        foreach ($segments as $segment) {
            $slug = Str::slug($segment);

            $query = DB::table($this->categoriesTable)
                ->when(
                    $parentId,
                    fn (Builder $builder): Builder => $builder->where('parent_id', $parentId),
                    fn (Builder $builder): Builder => $builder->whereNull('parent_id')
                )
                ->where(function (Builder $builder) use ($slug, $segment): void {
                    $builder->where('slug', $slug);

                    if ($this->categoriesTable !== null && Schema::hasColumn($this->categoriesTable, 'name')) {
                        $builder->orWhere('name', $segment);
                    }
                });

            $existing = $query->first();
            if ($existing !== null) {
                $pk = $this->primaryKeyOf($this->categoriesTable) ?? 'id';
                $parentId = $existing->{$pk} ?? null;

                continue;
            }

            $insert = ['slug' => $slug];
            if (Schema::hasColumn($this->categoriesTable, 'name')) {
                $insert['name'] = $segment;
            }

            if (Schema::hasColumn($this->categoriesTable, 'parent_id')) {
                $insert['parent_id'] = $parentId;
            }

            if (Schema::hasColumn($this->categoriesTable, 'created_at')) {
                $insert['created_at'] = Carbon::now();
            }

            if (Schema::hasColumn($this->categoriesTable, 'updated_at')) {
                $insert['updated_at'] = Carbon::now();
            }

            $pk = $this->primaryKeyOf($this->categoriesTable) ?? 'id';
            $newId = DB::table($this->categoriesTable)->insertGetId($insert, $pk);
            if (! $newId) {
                DB::table($this->categoriesTable)->insert($insert);
                $row = DB::table($this->categoriesTable)
                    ->when(
                        $parentId,
                        fn (Builder $builder): Builder => $builder->where('parent_id', $parentId),
                        fn (Builder $builder): Builder => $builder->whereNull('parent_id')
                    )
                    ->where('slug', $slug)
                    ->first();
                $newId = $row->{$pk} ?? null;
            }

            $parentId = $newId;
        }

        if ($parentId === null) {
            return null;
        }

        if (is_string($parentId) || is_int($parentId)) {
            return (string) $parentId;
        }

        return null;
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
