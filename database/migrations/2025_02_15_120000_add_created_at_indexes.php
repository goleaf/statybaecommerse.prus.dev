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

    private function addCreatedAtIndex(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexName): void {
            if ($this->indexExists($tableName, $indexName)) {
                return;
            }

            $table->index('created_at', $indexName);
        });
    }

    private function dropCreatedAtIndex(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $schemaManager->listTableIndexes($tableName);

        return array_key_exists($indexName, $indexes);
    }
};
