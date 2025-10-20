<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addCreatedAtIndex('orders', 'orders_created_at_index');
        $this->addCreatedAtIndex('products', 'products_created_at_index');
        $this->addCreatedAtIndex('users', 'users_created_at_index');
    }

    public function down(): void
    {
        $this->dropCreatedAtIndex('orders', 'orders_created_at_index');
        $this->dropCreatedAtIndex('products', 'products_created_at_index');
        $this->dropCreatedAtIndex('users', 'users_created_at_index');
    }

    private function addCreatedAtIndex(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, 'created_at')) {
            return;
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->index('created_at', $indexName);
        });
    }

    private function dropCreatedAtIndex(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();

        if (! method_exists($connection, 'getDoctrineSchemaManager')) {
            return false;
        }

        $schemaManager = $connection->getDoctrineSchemaManager();
        $indexes = $schemaManager->listTableIndexes($connection->getTablePrefix().$table);

        return array_key_exists($indexName, $indexes);
    }
};
