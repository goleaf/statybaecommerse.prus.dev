<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                // Composite index to speed up counts and lookups by model and collection
                if (!$this->indexExists('media', 'media_model_type_model_id_collection_name_index')) {
                    $table->index(['model_type', 'model_id', 'collection_name'], 'media_model_type_model_id_collection_name_index');
                }

                // Helpful index for ordering within collection
                if (!$this->indexExists('media', 'media_collection_name_order_column_index')) {
                    $table->index(['collection_name', 'order_column'], 'media_collection_name_order_column_index');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                if ($this->indexExists('media', 'media_model_type_model_id_collection_name_index')) {
                    $table->dropIndex('media_model_type_model_id_collection_name_index');
                }
                if ($this->indexExists('media', 'media_collection_name_order_column_index')) {
                    $table->dropIndex('media_collection_name_order_column_index');
                }
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?", [$table, $index]);
            return !empty($result);
        }
        return false;
    }
};
