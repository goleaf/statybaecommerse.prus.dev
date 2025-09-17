<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->index(['model_type', 'model_id', 'collection_name'], 'media_model_type_model_id_collection_name_index');
            $table->index(['collection_name', 'order_column'], 'media_collection_name_order_column_index');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_model_type_model_id_collection_name_index');
            $table->dropIndex('media_collection_name_order_column_index');
        });
    }
};
