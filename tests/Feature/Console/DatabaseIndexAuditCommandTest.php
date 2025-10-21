<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DatabaseIndexAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_indexes_are_flagged_and_cleanup_resolves_them(): void
    {
        Schema::dropIfExists('duplicate_index_examples');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('cart_items');

        Schema::create('duplicate_index_examples', static function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });

        Schema::table('duplicate_index_examples', static function (Blueprint $table): void {
            $table->index(['slug'], 'duplicate_index_examples_slug_idx');
            $table->index(['slug'], 'duplicate_index_examples_slug_idx_duplicate');
            $table->index(['category_id', 'slug'], 'duplicate_index_examples_category_slug_idx');
            $table->index(['category_id', 'slug'], 'duplicate_index_examples_category_slug_idx_duplicate');
        });

        Schema::create('orders', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('status');
            $table->timestamps();

            $table->index(['customer_id'], 'orders_customer_id_idx');
        });

        Schema::create('order_items', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->timestamps();

            $table->index(['order_id'], 'order_items_order_id_idx');
        });

        Schema::create('cart_items', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        $exitCode = Artisan::call('db:audit-indexes');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode, 'Expected command to exit with failure when duplicates exist.');
        $this->assertStringContainsString('duplicate_index_examples', $output);
        $this->assertStringContainsString('duplicate_index_examples_slug_idx_duplicate', $output);
        $this->assertStringContainsString('duplicate_index_examples_category_slug_idx_duplicate', $output);
        $this->assertStringContainsString('Suggested composite indexes for commerce tables', $output);
        $this->assertStringContainsString('orders on [customer_id, status]', $output);
        $this->assertStringContainsString('orders on [status, created_at]', $output);
        $this->assertStringContainsString('order_items on [order_id, product_id]', $output);
        $this->assertStringContainsString('cart_items on [cart_id, product_id]', $output);

        Schema::table('duplicate_index_examples', static function (Blueprint $table): void {
            $table->dropIndex('duplicate_index_examples_slug_idx_duplicate');
            $table->dropIndex('duplicate_index_examples_category_slug_idx_duplicate');
        });

        $exitCodeAfterCleanup = Artisan::call('db:audit-indexes');
        $outputAfterCleanup = Artisan::output();

        $this->assertSame(0, $exitCodeAfterCleanup, 'Expected command to pass after removing duplicates.');
        $this->assertStringContainsString('No duplicate indexes found', $outputAfterCleanup);
        $this->assertStringContainsString('Suggested composite indexes for commerce tables', $outputAfterCleanup);
        $this->assertStringContainsString('orders on [customer_id, status]', $outputAfterCleanup);
    }
}
