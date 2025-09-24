<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Test Configuration', function () {
    it('uses sqlite database for testing', function () {
        expect(Config::get('database.default'))
            ->toBe('sqlite')
            ->and(Config::get('database.connections.sqlite.database'))
            ->toBe(':memory:');
    });

    it('can run migrations successfully', function () {
        Artisan::call('migrate:fresh');

        expect(Artisan::output())->toContain('Migrated');
    });

    it('has all required tables', function () {
        $requiredTables = [
            'users',
            'products',
            'product_variants',
            'categories',
            'brands',
            'orders',
            'order_items',
            'documents',
            'document_templates',
            'reviews',
            'cart_items',
            'addresses',
            'countries',
            'zones',
            'currencies',
            'settings',
        ];

        foreach ($requiredTables as $table) {
            expect(DB::getSchemaBuilder()->hasTable($table))
                ->toBeTrue("Table {$table} should exist");
        }
    });

    it('can seed database successfully', function () {
        Artisan::call('db:seed');

        expect(Artisan::output())->not->toContain('ERROR');
    });

    it('has proper test environment configuration', function () {
        expect(Config::get('app.env'))
            ->toBe('testing')
            ->and(Config::get('app.debug'))
            ->toBeTrue()
            ->and(Config::get('cache.default'))
            ->toBe('array')
            ->and(Config::get('session.driver'))
            ->toBe('array')
            ->and(Config::get('queue.default'))
            ->toBe('sync');
    });

    it('has mail configured for testing', function () {
        expect(Config::get('mail.default'))->toBe('array');
    });

    it('has storage configured for testing', function () {
        expect(Config::get('filesystems.default'))->toBe('local');
    });

    it('can create test users with factories', function () {
        $user = \App\Models\User::factory()->create();

        expect($user)
            ->toBeInstanceOf(\App\Models\User::class)
            ->and($user->email)
            ->toBeString()
            ->and($user->name)
            ->toBeString();
    });

    it('can create test products with factories', function () {
        $product = \App\Models\Product::factory()->create();

        expect($product)
            ->toBeInstanceOf(\App\Models\Product::class)
            ->and($product->name)
            ->toBeString()
            ->and($product->is_visible)
            ->toBeBool();
    });

    it('can create test orders with factories', function () {
        $order = \App\Models\Order::factory()->create();

        expect($order)
            ->toBeInstanceOf(\App\Models\Order::class)
            ->and($order->total)
            ->toBeInt()
            ->and($order->status)
            ->toBeString();
    });

    it('can create test documents with factories', function () {
        $document = \App\Models\Document::factory()->create();

        expect($document)
            ->toBeInstanceOf(\App\Models\Document::class)
            ->and($document->title)
            ->toBeString()
            ->and($document->status)
            ->toBeString();
    });

    it('can create test document templates with factories', function () {
        $template = \App\Models\DocumentTemplate::factory()->create();

        expect($template)
            ->toBeInstanceOf(\App\Models\DocumentTemplate::class)
            ->and($template->name)
            ->toBeString()
            ->and($template->type)
            ->toBeString();
    });

    it('has proper foreign key constraints', function () {
        $user = \App\Models\User::factory()->create();
        $template = \App\Models\DocumentTemplate::factory()->create();

        $document = \App\Models\Document::factory()->create([
            'document_template_id' => $template->id,
            'created_by' => $user->id,
        ]);

        expect($document->template)
            ->toBeInstanceOf(\App\Models\DocumentTemplate::class)
            ->and($document->creator)
            ->toBeInstanceOf(\App\Models\User::class);
    });

    it('has proper model relationships', function () {
        $product = \App\Models\Product::factory()->create();
        $category = \App\Models\Category::factory()->create();
        $brand = \App\Models\Brand::factory()->create();

        $product->categories()->attach($category->id);
        $product->update(['brand_id' => $brand->id]);

        expect($product->categories)
            ->toHaveCount(1)
            ->and($product->brand)
            ->toBeInstanceOf(\App\Models\Brand::class);
    });

    it('can handle soft deletes properly', function () {
        $product = \App\Models\Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        expect(\App\Models\Product::find($productId))
            ->toBeNull()
            ->and(\App\Models\Product::withTrashed()->find($productId))
            ->not
            ->toBeNull();
    });

    it('has proper validation rules', function () {
        expect(fn () => \App\Models\User::create([]))
            ->toThrow(\Illuminate\Database\QueryException::class);

        expect(fn () => \App\Models\Product::create([]))
            ->toThrow(\Illuminate\Database\QueryException::class);
    });
});
