<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            // Composite index to speed up queries filtering by model and collection
            if (! $this->mysqlIndexExists('media', 'media_model_collection_index')) {
                $table->index(['model_type', 'model_id', 'collection_name'], 'media_model_collection_index');
            }

            // Helpful single index on collection_name for ad-hoc filters
            if (! $this->mysqlIndexExists('media', 'media_collection_name_index')) {
                $table->index('collection_name', 'media_collection_name_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            if ($this->mysqlIndexExists('media', 'media_model_collection_index')) {
                $table->dropIndex('media_model_collection_index');
            }
            if ($this->mysqlIndexExists('media', 'media_collection_name_index')) {
                $table->dropIndex('media_collection_name_index');
            }
        });
    }

    private function mysqlIndexExists(string $table, string $index): bool
    {
        try {
            $database = DB::getDatabaseName();
            $result = DB::select(
                'SELECT COUNT(1) as cnt FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$database, DB::getTablePrefix().$table, $index]
            );
            return (int)($result[0]->cnt ?? 0) > 0;
        } catch (\Throwable) {
            return false;
        }
    }
};
