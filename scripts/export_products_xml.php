<?php

declare(strict_types=1);

// Standalone exporter that reads the SQLite DB directly (no Laravel bootstrap)
// Usage: php scripts/export_products_xml.php [output_path]

ini_set('memory_limit', '512M');
date_default_timezone_set('UTC');

$projectRoot = dirname(__DIR__);
$dbPath = $projectRoot.'/database/database.sqlite';
$outputPath = $argv[1] ?? ($projectRoot.'/public/catalog-products.xml');

if (! is_file($dbPath)) {
    fwrite(STDERR, "SQLite database not found: {$dbPath}\n");
    exit(1);
}

try {
    $pdo = new PDO('sqlite:'.$dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, 'Failed to open database: '.$e->getMessage()."\n");
    exit(1);
}

function boolToString(bool $v): string
{
    return $v ? 'true' : 'false';
}

function appendIfNotNull(DOMDocument $doc, DOMElement $parent, string $name, ?string $value): void
{
    if ($value === null || $value === '') {
        return;
    }
    $parent->appendChild($doc->createElement($name, $value));
}

// Probe column existence safely
function hasColumn(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->query('PRAGMA table_info('.$table.')');
    $cols = $stmt ? $stmt->fetchAll() : [];
    foreach ($cols as $c) {
        if (isset($c['name']) && $c['name'] === $column) {
            return true;
        }
    }

    return false;
}

// Prepare lookups
$hasIsVisible = hasColumn($pdo, 'products', 'is_visible');
$hasPublishedAt = hasColumn($pdo, 'products', 'published_at');

$where = [];
if ($hasIsVisible) {
    $where[] = 'is_visible = 1';
}
if ($hasPublishedAt) {
    $where[] = '(published_at IS NULL OR published_at <= CURRENT_TIMESTAMP)';
}
$whereSql = $where ? (' WHERE '.implode(' AND ', $where)) : '';

$sqlProducts = 'SELECT * FROM products'.$whereSql.' ORDER BY id';
$stmtProducts = $pdo->query($sqlProducts);
$products = $stmtProducts ? $stmtProducts->fetchAll() : [];

// Prime relations
$productIds = array_map(static fn ($p) => (int) $p['id'], $products);
$in = $productIds ? ('('.implode(',', $productIds).')') : '(NULL)';

// Translations
$translations = [];
if ($productIds) {
    $stmt = $pdo->query('SELECT * FROM product_translations WHERE product_id IN '.$in);
    foreach ($stmt ?: [] as $row) {
        $pid = (int) $row['product_id'];
        $translations[$pid][] = $row;
    }
}

// Categories by slug
$categorySlugsByProductId = [];
if ($productIds) {
    $sql = 'SELECT pc.product_id, c.slug FROM product_categories pc JOIN categories c ON c.id = pc.category_id WHERE pc.product_id IN '.$in.' ORDER BY c.slug';
    $stmt = $pdo->query($sql);
    foreach ($stmt ?: [] as $row) {
        $pid = (int) $row['product_id'];
        $categorySlugsByProductId[$pid][] = (string) $row['slug'];
    }
}

// Images
$imagesByProductId = [];
if ($productIds) {
    $stmt = $pdo->query('SELECT product_id, path, alt_text FROM product_images WHERE product_id IN '.$in.' ORDER BY sort_order');
    foreach ($stmt ?: [] as $row) {
        $pid = (int) $row['product_id'];
        $imagesByProductId[$pid][] = $row;
    }
}

// Create XML (structure mirrors XmlCatalogService for products)
$doc = new DOMDocument('1.0', 'UTF-8');
$doc->formatOutput = true;
$catalog = $doc->createElement('catalog');
$doc->appendChild($catalog);

$productsEl = $doc->createElement('products');
$catalog->appendChild($productsEl);

foreach ($products as $product) {
    $pid = (int) $product['id'];
    $pEl = $doc->createElement('product');

    appendIfNotNull($doc, $pEl, 'sku', isset($product['sku']) ? (string) $product['sku'] : '');
    appendIfNotNull($doc, $pEl, 'slug', isset($product['slug']) ? (string) $product['slug'] : '');

    $baseEl = $doc->createElement('base');
    $fields = [
        'price',
        'compare_price',
        'cost_price',
        'sale_price',
        'weight',
        'length',
        'width',
        'height',
        'status',
        'type',
        'brand_id',
        'tax_class',
        'shipping_class',
        'stock_quantity',
        'low_stock_threshold',
        'minimum_quantity',
    ];
    foreach ($fields as $f) {
        if (array_key_exists($f, $product) && $product[$f] !== null && $product[$f] !== '') {
            appendIfNotNull($doc, $baseEl, $f, (string) $product[$f]);
        }
    }
    foreach (['manage_stock', 'track_stock', 'allow_backorder', 'is_visible', 'is_featured', 'is_requestable'] as $bf) {
        if (array_key_exists($bf, $product) && $product[$bf] !== null) {
            appendIfNotNull($doc, $baseEl, $bf, boolToString((bool) $product[$bf]));
        }
    }
    if ($baseEl->childNodes->length > 0) {
        $pEl->appendChild($baseEl);
    }

    $pcEl = $doc->createElement('categories');
    foreach ($categorySlugsByProductId[$pid] ?? [] as $slug) {
        $pcEl->appendChild($doc->createElement('category_slug', (string) $slug));
    }
    $pEl->appendChild($pcEl);

    $translationsEl = $doc->createElement('translations');
    foreach ($translations[$pid] ?? [] as $tr) {
        $tEl = $doc->createElement('translation');
        $tEl->setAttribute('locale', (string) ($tr['locale'] ?? 'lt'));
        appendIfNotNull($doc, $tEl, 'name', (string) ($tr['name'] ?? ''));
        appendIfNotNull($doc, $tEl, 'slug', (string) ($tr['slug'] ?? ''));
        appendIfNotNull($doc, $tEl, 'description', (string) ($tr['description'] ?? ''));
        appendIfNotNull($doc, $tEl, 'short_description', (string) ($tr['short_description'] ?? ''));
        appendIfNotNull($doc, $tEl, 'seo_title', (string) ($tr['seo_title'] ?? ''));
        appendIfNotNull($doc, $tEl, 'seo_description', (string) ($tr['seo_description'] ?? ''));
        $translationsEl->appendChild($tEl);
    }
    $pEl->appendChild($translationsEl);

    $imagesEl = $doc->createElement('images');
    foreach ($imagesByProductId[$pid] ?? [] as $img) {
        $iEl = $doc->createElement('image');
        if (! empty($img['path'])) {
            $iEl->setAttribute('src', (string) $img['path']);
        }
        if (! empty($img['alt_text'])) {
            $iEl->setAttribute('alt', (string) $img['alt_text']);
        }
        $imagesEl->appendChild($iEl);
    }
    $pEl->appendChild($imagesEl);

    $productsEl->appendChild($pEl);
}

$xml = $doc->saveXML() ?: '';
if ($xml === '') {
    fwrite(STDERR, "Failed to generate XML\n");
    exit(1);
}

if (! is_dir(dirname($outputPath))) {
    @mkdir(dirname($outputPath), 0775, true);
}

file_put_contents($outputPath, $xml);

fwrite(STDOUT, "Exported products XML to: {$outputPath}\n");
exit(0);
