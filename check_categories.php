<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Current categories in database:\n";
echo "==============================\n";

$categories = \App\Models\Category::all(['id', 'name', 'slug', 'parent_id']);

foreach ($categories as $category) {
    $indent = $category->parent_id ? '  └─ ' : '';
    echo $indent . $category->id . ': ' . $category->name . ' (' . $category->slug . ')' . "\n";
}

echo "\nTotal categories: " . $categories->count() . "\n";



