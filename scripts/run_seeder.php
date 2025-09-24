<?php

require_once 'vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__.'/database/database.sqlite',
    'prefix' => '',
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create tables if they don't exist
if (! Capsule::schema()->hasTable('system_setting_categories')) {
    Capsule::schema()->create('system_setting_categories', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->string('icon')->nullable();
        $table->string('color')->default('primary');
        $table->integer('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

if (! Capsule::schema()->hasTable('system_settings')) {
    Capsule::schema()->create('system_settings', function ($table) {
        $table->id();
        $table->unsignedBigInteger('category_id')->nullable();
        $table->string('key')->unique();
        $table->string('name');
        $table->text('value')->nullable();
        $table->string('type')->default('string');
        $table->string('group')->default('general');
        $table->text('description')->nullable();
        $table->text('help_text')->nullable();
        $table->boolean('is_public')->default(false);
        $table->boolean('is_required')->default(false);
        $table->boolean('is_encrypted')->default(false);
        $table->boolean('is_readonly')->default(false);
        $table->json('validation_rules')->nullable();
        $table->json('options')->nullable();
        $table->text('default_value')->nullable();
        $table->integer('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

// Create categories
$categories = [
    [
        'name' => 'General',
        'slug' => 'general',
        'description' => 'General system settings',
        'icon' => 'heroicon-o-cog-6-tooth',
        'color' => 'primary',
        'sort_order' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'E-commerce',
        'slug' => 'ecommerce',
        'description' => 'E-commerce specific settings',
        'icon' => 'heroicon-o-shopping-cart',
        'color' => 'success',
        'sort_order' => 2,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Email',
        'slug' => 'email',
        'description' => 'Email configuration settings',
        'icon' => 'heroicon-o-envelope',
        'color' => 'info',
        'sort_order' => 3,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Payment',
        'slug' => 'payment',
        'description' => 'Payment gateway settings',
        'icon' => 'heroicon-o-credit-card',
        'color' => 'warning',
        'sort_order' => 4,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Shipping',
        'slug' => 'shipping',
        'description' => 'Shipping and delivery settings',
        'icon' => 'heroicon-o-truck',
        'color' => 'secondary',
        'sort_order' => 5,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'SEO',
        'slug' => 'seo',
        'description' => 'Search engine optimization settings',
        'icon' => 'heroicon-o-magnifying-glass',
        'color' => 'info',
        'sort_order' => 6,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Security',
        'slug' => 'security',
        'description' => 'Security and authentication settings',
        'icon' => 'heroicon-o-shield-check',
        'color' => 'danger',
        'sort_order' => 7,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'API',
        'slug' => 'api',
        'description' => 'API configuration settings',
        'icon' => 'heroicon-o-code-bracket',
        'color' => 'secondary',
        'sort_order' => 8,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Appearance',
        'slug' => 'appearance',
        'description' => 'Theme and appearance settings',
        'icon' => 'heroicon-o-paint-brush',
        'color' => 'primary',
        'sort_order' => 9,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => 'Notifications',
        'slug' => 'notifications',
        'description' => 'Notification system settings',
        'icon' => 'heroicon-o-bell',
        'color' => 'warning',
        'sort_order' => 10,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ],
];

foreach ($categories as $category) {
    Capsule::table('system_setting_categories')->insert($category);
}

// Get category IDs
$categoryIds = Capsule::table('system_setting_categories')->pluck('id', 'slug');

// Create some basic settings
$settings = [
    [
        'category_id' => $categoryIds['general'],
        'key' => 'app.name',
        'name' => 'Application Name',
        'value' => 'Statybos E-commerce',
        'type' => 'string',
        'group' => 'general',
        'description' => 'The name of your application',
        'help_text' => 'This will be displayed in the browser title and throughout the application',
        'is_public' => true,
        'is_required' => true,
        'is_encrypted' => false,
        'is_readonly' => false,
        'is_active' => true,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'category_id' => $categoryIds['general'],
        'key' => 'app.currency',
        'name' => 'Default Currency',
        'value' => 'EUR',
        'type' => 'select',
        'group' => 'general',
        'description' => 'Default currency for the application',
        'help_text' => 'All prices will be displayed in this currency by default',
        'is_public' => true,
        'is_required' => true,
        'is_encrypted' => false,
        'is_readonly' => false,
        'is_active' => true,
        'options' => json_encode([
            'EUR' => 'Euro (€)',
            'USD' => 'US Dollar ($)',
            'GBP' => 'British Pound (£)',
        ]),
        'sort_order' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'category_id' => $categoryIds['ecommerce'],
        'key' => 'ecommerce.tax_rate',
        'name' => 'Default Tax Rate',
        'value' => '21.0',
        'type' => 'number',
        'group' => 'ecommerce',
        'description' => 'Default VAT rate percentage',
        'help_text' => 'This rate will be applied to products without specific tax rates',
        'is_public' => true,
        'is_required' => true,
        'is_encrypted' => false,
        'is_readonly' => false,
        'is_active' => true,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'category_id' => $categoryIds['email'],
        'key' => 'mail.from_address',
        'name' => 'From Email Address',
        'value' => 'noreply@statybaecommerse.prus.dev',
        'type' => 'string',
        'group' => 'email',
        'description' => 'Default sender email address',
        'help_text' => 'This email will be used as the sender for all system emails',
        'is_public' => false,
        'is_required' => true,
        'is_encrypted' => false,
        'is_readonly' => false,
        'is_active' => true,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ],
];

foreach ($settings as $setting) {
    Capsule::table('system_settings')->insert($setting);
}

echo "System settings seeder completed successfully!\n";
echo 'Created '.count($categories).' categories and '.count($settings)." settings.\n";
