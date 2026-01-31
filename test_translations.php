<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test English translations
$enNav = __('admin.navigation', [], 'en');
echo "English navigation translations:\n";
var_dump(array_key_exists('toggle_menu', $enNav));
if (array_key_exists('toggle_menu', $enNav)) {
    echo 'toggle_menu: ' . $enNav['toggle_menu'] . "\n";
}

// Test Lithuanian translations
$ltNav = __('admin.navigation', [], 'lt');
echo "\nLithuanian navigation translations:\n";
var_dump(array_key_exists('toggle_menu', $ltNav));
if (array_key_exists('toggle_menu', $ltNav)) {
    echo 'toggle_menu: ' . $ltNav['toggle_menu'] . "\n";
}

// Test table translations
$enTable = __('admin.table', [], 'en');
echo "\nEnglish table translations:\n";
var_dump(array_key_exists('toggle_search', $enTable));

// Test form translations
$enForm = __('admin.form', [], 'en');
echo "\nEnglish form translations:\n";
var_dump($enForm);
