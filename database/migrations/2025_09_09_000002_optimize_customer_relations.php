<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reviews: indexes and FKs
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                if (! $this->hasIndex('reviews', 'reviews_user_idx')) {
                    $table->index('user_id', 'reviews_user_idx');
                }
                if (! $this->hasIndex('reviews', 'reviews_product_idx')) {
                    $table->index('product_id', 'reviews_product_idx');
                }
                if (! $this->hasIndex('reviews', 'reviews_locale_idx')) {
                    $table->index('locale', 'reviews_locale_idx');
                }
                if (! $this->hasIndex('reviews', 'reviews_approved_idx')) {
                    $table->index('is_approved', 'reviews_approved_idx');
                }
            });
            // Attempt to add FK constraints (SQLite-safe try/catch)
            $this->safeFk('ALTER TABLE reviews ADD CONSTRAINT reviews_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
            $this->safeFk('ALTER TABLE reviews ADD CONSTRAINT reviews_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');
        }

        // Addresses: composite index already likely exists; ensure
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table): void {
                if (! $this->hasIndex('addresses', 'addresses_user_type_idx')) {
                    $table->index(['user_id', 'type'], 'addresses_user_type_idx');
                }
            });
            $this->safeFk('ALTER TABLE addresses ADD CONSTRAINT addresses_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        }

        // Orders: ensure user_id index exists (composite may exist)
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                if (! $this->hasIndex('orders', 'orders_user_idx')) {
                    $table->index('user_id', 'orders_user_idx');
                }
            });
            $this->safeFk('ALTER TABLE orders ADD CONSTRAINT orders_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
        }

        // Cart items: user/product indexes
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table): void {
                if (! $this->hasIndex('cart_items', 'cart_items_user_idx')) {
                    $table->index('user_id', 'cart_items_user_idx');
                }
                if (! $this->hasIndex('cart_items', 'cart_items_session_idx')) {
                    $table->index('session_id', 'cart_items_session_idx');
                }
            });
            $this->safeFk('ALTER TABLE cart_items ADD CONSTRAINT cart_items_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        }

        // User wishlists pivot
        if (Schema::hasTable('user_wishlists')) {
            $hasUserIdColumn = Schema::hasColumn('user_wishlists', 'user_id');
            $hasProductIdColumn = Schema::hasColumn('user_wishlists', 'product_id');

            Schema::table('user_wishlists', function (Blueprint $table) use ($hasUserIdColumn, $hasProductIdColumn): void {
                if ($hasUserIdColumn && ! $this->hasIndex('user_wishlists', 'user_wishlists_user_idx')) {
                    $table->index('user_id', 'user_wishlists_user_idx');
                }
                if ($hasProductIdColumn && ! $this->hasIndex('user_wishlists', 'user_wishlists_product_idx')) {
                    $table->index('product_id', 'user_wishlists_product_idx');
                }
            });

            if ($hasUserIdColumn) {
                $this->safeFk('ALTER TABLE user_wishlists ADD CONSTRAINT user_wishlists_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
            }

            if ($hasProductIdColumn) {
                $this->safeFk('ALTER TABLE user_wishlists ADD CONSTRAINT user_wishlists_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');
            }
        }

        // Discount redemptions (new table name without sh_ if exists)
        if (Schema::hasTable('discount_redemptions')) {
            Schema::table('discount_redemptions', function (Blueprint $table): void {
                if (! $this->hasIndex('discount_redemptions', 'discount_redemptions_user_idx')) {
                    $table->index('user_id', 'discount_redemptions_user_idx');
                }
                if (! $this->hasIndex('discount_redemptions', 'discount_redemptions_order_idx')) {
                    $table->index('order_id', 'discount_redemptions_order_idx');
                }
            });
            $this->safeFk('ALTER TABLE discount_redemptions ADD CONSTRAINT discount_redemptions_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
            $this->safeFk('ALTER TABLE discount_redemptions ADD CONSTRAINT discount_redemptions_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');
        }

        // Customer group user pivot
        if (Schema::hasTable('customer_group_user')) {
            $hasUserIdColumn = Schema::hasColumn('customer_group_user', 'user_id');
            $hasCustomerGroupIdColumn = Schema::hasColumn('customer_group_user', 'customer_group_id');
            $hasGroupIdColumn = Schema::hasColumn('customer_group_user', 'group_id');
            $groupColumn = $hasCustomerGroupIdColumn ? 'customer_group_id' : ($hasGroupIdColumn ? 'group_id' : null);

            Schema::table('customer_group_user', function (Blueprint $table) use ($hasUserIdColumn, $groupColumn): void {
                if ($hasUserIdColumn && ! $this->hasIndex('customer_group_user', 'customer_group_user_user_idx')) {
                    $table->index('user_id', 'customer_group_user_user_idx');
                }

                if ($groupColumn !== null && ! $this->hasIndex('customer_group_user', 'customer_group_user_group_idx')) {
                    $table->index($groupColumn, 'customer_group_user_group_idx');
                }
            });

            if ($hasUserIdColumn) {
                $this->safeFk('ALTER TABLE customer_group_user ADD CONSTRAINT cgu_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
            }

            if ($groupColumn !== null) {
                $this->safeFk("ALTER TABLE customer_group_user ADD CONSTRAINT cgu_group_fk FOREIGN KEY ({$groupColumn}) REFERENCES customer_groups(id) ON DELETE CASCADE");
            }
        }
    }

    public function down(): void
    {
        // Non-destructive: keep indexes/FKs
    }

    private function safeFk(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {  /* ignore */
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            if ($driver === 'sqlite') {
                $exists = DB::selectOne("SELECT name FROM sqlite_master WHERE type = 'index' AND name = ?", [$index]);

                return (bool) $exists;
            }

            // Fallback: attempt to create and catch
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
};
