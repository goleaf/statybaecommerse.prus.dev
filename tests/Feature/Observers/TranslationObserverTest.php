<?php

declare(strict_types=1);

use App\Models\Product;
use App\Observers\TranslationObserver;
use App\Services\TranslationHookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->observer = app(TranslationObserver::class);
    $this->service = app(TranslationHookService::class);

    // Ensure translation directory exists
    if (! File::isDirectory(lang_path())) {
        File::makeDirectory(lang_path(), 0755, true);
    }
});

afterEach(function () {
    // Clean up test translation files
    $testFiles = [
        lang_path('lt.json'),
        lang_path('en.json'),
    ];

    foreach ($testFiles as $file) {
        if (File::exists($file)) {
            File::delete($file);
        }
    }
});

it('processes translatable fields when model is saving', function () {
    $product = Product::factory()->make([
        'name'        => 'Test Product Name',
        'description' => 'Test Product Description',
    ]);

    // Simulate the saving event
    $this->observer->saving($product);

    // Verify translations were created
    $ltFile = lang_path('lt.json');
    expect(File::exists($ltFile))->toBeTrue();

    $translations = json_decode(File::get($ltFile), true);
    expect($translations)->toHaveKey('product.test_product_name');
    expect($translations)->toHaveKey('product.test_product_description');
});

it('creates translation entries for dirty fields only', function () {
    $product = Product::factory()->create([
        'name'        => 'Original Name',
        'description' => 'Original Description',
    ]);

    // Update only the name
    $product->name = 'Updated Name';
    $product->syncOriginal(); // Mark as not dirty
    $product->name = 'Final Name'; // This should be dirty

    $this->observer->saving($product);

    $ltFile = lang_path('lt.json');
    $translations = json_decode(File::get($ltFile), true);

    // Should have translation for the updated name
    expect($translations)->toHaveKey('product.final_name');
    // Should not have translation for unchanged description
    expect($translations)->not->toHaveKey('product.original_description');
});

it('handles models without translatable fields gracefully', function () {
    // Create a model instance that doesn't have translatable fields
    $model = new class extends \Illuminate\Database\Eloquent\Model
    {
        protected $fillable = ['non_translatable_field'];

        public $timestamps = false;
    };

    $model->non_translatable_field = 'Some Value';

    // This should not throw an exception
    expect(fn () => $this->observer->saving($model))->not->toThrow();
});

it('logs translation activity for created models', function () {
    $product = Product::factory()->create([
        'name' => 'New Product',
    ]);

    // Capture log output
    \Illuminate\Support\Facades\Log::shouldReceive('info')
        ->once()
        ->with('Translation hook processed', \Mockery::type('array'));

    $this->observer->created($product);
});

it('logs translation activity for updated models', function () {
    $product = Product::factory()->create([
        'name' => 'Updated Product',
    ]);

    // Capture log output
    \Illuminate\Support\Facades\Log::shouldReceive('info')
        ->once()
        ->with('Translation hook processed', \Mockery::type('array'));

    $this->observer->updated($product);
});

it('handles translation creation errors gracefully', function () {
    $product = Product::factory()->make([
        'name' => 'Test Product',
    ]);

    // Mock the service to throw an exception
    $this->mock(TranslationHookService::class)
        ->shouldReceive('generateTranslationKey')
        ->andThrow(new \Exception('Translation service error'));

    // Capture error log
    \Illuminate\Support\Facades\Log::shouldReceive('error')
        ->once()
        ->with('Failed to create translation entry', \Mockery::type('array'));

    // This should not throw an exception
    expect(fn () => $this->observer->saving($product))->not->toThrow();
});

it('stores translation key reference when model supports it', function () {
    // Create a test model that supports translation key storage
    $testModel = new class extends \Illuminate\Database\Eloquent\Model
    {
        protected $fillable = ['name', 'name_translation_key'];

        protected $translatableFields = ['name'];

        public $timestamps = false;

        public function isFillable($key): bool
        {
            return in_array($key, $this->fillable);
        }
    };

    $testModel->name = 'Test Name';
    $testModel->syncOriginal();
    $testModel->name = 'Updated Name'; // Make it dirty

    $this->observer->saving($testModel);

    expect($testModel->name_translation_key)->not->toBeNull();
    expect($testModel->name_translation_key)->toContain('updated_name');
});
