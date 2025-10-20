<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DbAuditIndexesCommandTest extends TestCase
{
    public function test_reports_duplicate_indexes_and_suggests_improvements(): void
    {
        Schema::dropAllTables();

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->string('status');
            $table->timestamps();

            $table->index(['user_id', 'status'], 'orders_user_status_idx');
            $table->index(['user_id', 'status'], 'orders_user_status_idx_dup');
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('product_id');
            $table->unsignedInteger('quantity');

            $table->index('order_id');
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku')->unique();
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_active')->default(true);
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
        });

        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('slug');
        });

        Artisan::call('db:audit-indexes');

        $output = Artisan::output();

        self::assertStringContainsString('Duplicate index detected on orders for columns [user_id, status]', $output);
        self::assertStringContainsString('orders_user_status_idx_dup', $output);
        self::assertStringContainsString('Suggestion: add index on orders (created_at)', $output);
        self::assertStringContainsString('Suggestion: add index on order_items (order_id, product_id)', $output);

        Schema::drop('product_categories');
        Schema::drop('users');
        Schema::drop('products');
        Schema::drop('order_items');
        Schema::drop('orders');
    }
}
