<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test if we can load the raw translation file
$enAdmin = include 'lang/en/admin.php';
echo "Raw English admin translations loaded:\n";
var_dump(array_key_exists('navigation', $enAdmin));
if (array_key_exists('navigation', $enAdmin)) {
    var_dump(array_key_exists('toggle_menu', $enAdmin['navigation']));
}

// Test Laravel's translation system
echo "\nLaravel translation system:\n";
$nav = trans('admin.navigation');
var_dump($nav);

echo "\nSpecific key:\n";
$toggleMenu = trans('admin.navigation.toggle_menu');
var_dump($toggleMenu);