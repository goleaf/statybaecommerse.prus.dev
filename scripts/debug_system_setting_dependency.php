<?php

declare(strict_types=1);

use App\Models\SystemSetting;
use App\Models\SystemSettingDependency;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->loadEnvironmentFrom('.env');

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => ':memory:']);
config(['database.connections.sqlite.foreign_key_constraints' => true]);

Schema::dropAllTables();

Schema::create('activity_log', function (Blueprint $table) {
    $table->id();
    $table->string('log_name')->nullable();
    $table->text('description')->nullable();
    $table->string('event')->nullable();
    $table->string('subject_type')->nullable();
    $table->unsignedBigInteger('subject_id')->nullable();
    $table->string('causer_type')->nullable();
    $table->unsignedBigInteger('causer_id')->nullable();
    $table->json('properties')->nullable();
    $table->uuid('batch_uuid')->nullable();
    $table->timestamps();
});

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name')->nullable();
    $table->string('email')->unique();
    $table->string('password')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('is_admin')->default(false);
    $table->rememberToken();
    $table->softDeletes();
    $table->timestamps();
});

Schema::create('system_setting_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('system_settings', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('category_id')->nullable();
    $table->string('key')->unique();
    $table->string('name');
    $table->text('value')->nullable();
    $table->string('type')->default('string');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('system_setting_dependencies', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('setting_id');
    $table->unsignedBigInteger('depends_on_setting_id');
    $table->string('condition')->nullable();
    $table->string('condition_value')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

User::create([
    'name'     => 'Test User',
    'email'    => 'system@statyba.test',
    'password' => bcrypt('password'),
]);

$setting1 = SystemSetting::create(['key' => 'setting1', 'name' => 'Setting 1']);
$setting2 = SystemSetting::create(['key' => 'setting2', 'name' => 'Setting 2']);

$dependency1 = SystemSettingDependency::create([
    'setting_id'            => $setting1->id,
    'depends_on_setting_id' => $setting2->id,
    'condition'             => 'setting2.value == "enabled"',
    'is_active'             => true,
]);

echo "Created dependency ID: {$dependency1->id}\n";

echo 'Belongs to setting ID: ' . $dependency1->setting->id . "\n";

echo 'Belongs to dependsOnSetting ID: ' . ($dependency1->dependsOnSetting?->id ?? 'null') . "\n";

$found = SystemSettingDependency::search('setting2')->get();
echo 'Search result count: ' . $found->count() . "\n";

$ordered = SystemSettingDependency::orderByCreatedAt()->get();
echo 'First ordered ID: ' . $ordered->first()->id . "\n";
