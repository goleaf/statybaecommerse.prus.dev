<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Load the raw file and check structure
$enAdmin = include 'lang/en/admin.php';
echo "English navigation section:\n";
if (isset($enAdmin['navigation'])) {
    echo 'Navigation keys: ' . implode(', ', array_keys($enAdmin['navigation'])) . "\n";
    if (isset($enAdmin['navigation']['toggle_menu'])) {
        echo 'toggle_menu found: ' . $enAdmin['navigation']['toggle_menu'] . "\n";
    } else {
        echo "toggle_menu NOT found\n";
    }
} else {
    echo "Navigation section not found\n";
}

// Test with explicit locale
echo "\nTesting with explicit English locale:\n";
app()->setLocale('en');
$navEn = trans('admin.navigation.toggle_menu', [], 'en');
echo 'Result: ' . $navEn . "\n";

echo "\nTesting with explicit Lithuanian locale:\n";
app()->setLocale('lt');
$navLt = trans('admin.navigation.toggle_menu', [], 'lt');
echo 'Result: ' . $navLt . "\n";
