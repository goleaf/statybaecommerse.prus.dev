<?php

declare(strict_types=1);

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->boot();

echo "Checking for product_histories tables...\n";

try {
    $tables = DB::select("SHOW TABLES LIKE '%product_histor%'");

    if (empty($tables)) {
        echo "No product_histories tables found.\n";
    } else {
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            echo "Found table: $tableName\n";
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
