<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use function collect;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function str_contains;

use Tests\TestCase;

final class SchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_shippings_requires_a_valid_order_reference(): void
    {
        $this->assertTrue(Schema::hasTable('orders'), 'orders table should exist.');
        $this->assertTrue(Schema::hasTable('order_shippings'), 'order_shippings table should exist.');
        $this->assertTrue(
            Schema::hasColumn('order_shippings', 'order_id'),
            'order_shippings table should contain the order_id column.',
        );

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->assertSqliteForeignKey();
            $this->assertSqliteIndex();

            return;
        }

        if ($driver === 'mysql') {
            $this->assertMySqlForeignKey();
            $this->assertMySqlIndex();

            return;
        }

        $this->markTestSkipped("Schema integrity assertions are not implemented for driver [{$driver}].");
    }

    private function assertSqliteForeignKey(): void
    {
        $foreignKeys = collect(DB::select("PRAGMA foreign_key_list('order_shippings')"));

        $hasConstraint = $foreignKeys->contains(static function (object $foreignKey): bool {
            return ($foreignKey->table ?? null) === 'orders'
                && ($foreignKey->from ?? null) === 'order_id';
        });

        $this->assertTrue($hasConstraint, 'order_shippings.order_id must reference orders.id in SQLite.');
    }

    private function assertSqliteIndex(): void
    {
        $indexes = collect(DB::select("PRAGMA index_list('order_shippings')"));

        $hasIndex = $indexes->contains(static function (object $index): bool {
            $name = (string) ($index->name ?? '');

            return $name === 'order_shippings_order_id_index'
                || $name === 'order_shippings_order_id_foreign'
                || str_contains($name, 'sqlite_autoindex_order_shippings');
        });

        $this->assertTrue($hasIndex, 'order_shippings.order_id must be indexed in SQLite.');
    }

    private function assertMySqlForeignKey(): void
    {
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();

        $foreignKey = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'order_shippings' AND COLUMN_NAME = 'order_id' AND REFERENCED_TABLE_NAME = 'orders' LIMIT 1",
            [$databaseName],
        );

        $this->assertNotNull($foreignKey, 'order_shippings.order_id must reference orders.id in MySQL.');
    }

    private function assertMySqlIndex(): void
    {
        $indexes = DB::select("SHOW INDEX FROM `order_shippings` WHERE Key_name = 'order_shippings_order_id_index'");

        $this->assertNotEmpty($indexes, 'order_shippings.order_id must be indexed in MySQL.');
    }
}
