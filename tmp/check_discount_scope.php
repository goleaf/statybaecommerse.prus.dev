<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../tests/Support/TestingDatabase.php';

use Tests\Support\TestingDatabase;
use Illuminate\Contracts\Console\Kernel;
use App\Models\Discount;

$databasePath = TestingDatabase::path();
TestingDatabase::ensureExists();

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=' . $databasePath);
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $databasePath;
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = $databasePath;

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

TestingDatabase::configure($app);
TestingDatabase::migrate();

Discount::query()->delete();

Discount::factory()->create(['name' => 'Zebra Savings']);
Discount::factory()->create(['name' => 'Alpha Deal']);
Discount::factory()->create(['name' => 'Midnight Offer']);

$asc = Discount::query()->orderedByName()->pluck('name')->all();
$desc = Discount::query()->orderedByName('desc')->pluck('name')->all();
$invalid = Discount::query()->orderedByName('sideways')->pluck('name')->all();

var_export([
    'asc' => $asc,
    'desc' => $desc,
    'invalid' => $invalid,
]);
