<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DbAuditIndexesCommandTest extends TestCase
{
    public function test_reports_duplicate_indexes_and_suggests_improvements(): void
    {
        $connection = 'db_audit_indexes';
        $databasePath = storage_path('framework/testing/db-audit-indexes.sqlite');

        File::ensureDirectoryExists(dirname($databasePath));
        File::delete($databasePath);
        File::put($databasePath, '');

        config()->set("database.connections.{$connection}", [
            'driver'                  => 'sqlite',
            'database'                => $databasePath,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);

        Schema::connection($connection)->create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->string('status');
            $table->timestamps();

            $table->index(['user_id', 'status'], 'orders_user_status_idx');
            $table->index(['user_id', 'status'], 'orders_user_status_idx_dup');
        });

        Schema::connection($connection)->create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('product_id');
            $table->unsignedInteger('quantity');

            $table->index('order_id');
        });

        Schema::connection($connection)->create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku')->unique();
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_active')->default(true);
        });

        Schema::connection($connection)->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
        });

        Schema::connection($connection)->create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('slug');
        });

        Artisan::call('db:audit-indexes', ['--database' => $connection]);

        $output = Artisan::output();

        self::assertStringContainsString('Duplicate indexes detected:', $output);
        self::assertStringContainsString('orders on [user_id, status]', $output);
        self::assertStringContainsString('orders_user_status_idx_dup', $output);
        self::assertStringContainsString('Suggested composite indexes for commerce tables', $output);
        self::assertStringContainsString('orders on [customer_id, status]', $output);
        self::assertStringContainsString('order_items on [order_id, product_id]', $output);

        Schema::connection($connection)->drop('product_categories');
        Schema::connection($connection)->drop('users');
        Schema::connection($connection)->drop('products');
        Schema::connection($connection)->drop('order_items');
        Schema::connection($connection)->drop('orders');

        File::delete($databasePath);
    }
}
