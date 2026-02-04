<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('product_similarities')) {
            $indexes = Schema::getIndexes('product_similarities');
            $indexNames = array_column($indexes, 'name');

            Schema::table('product_similarities', function (Blueprint $table) use ($indexNames) {
                if (in_array('product_similarities_product_id_similarity_score_index', $indexNames)) {
                    $table->dropIndex('product_similarities_product_id_similarity_score_index');
                }
                
                if (in_array('product_similarities_similar_product_id_similarity_score_index', $indexNames)) {
                    $table->dropIndex('product_similarities_similar_product_id_similarity_score_index');
                }

                if (Schema::hasColumn('product_similarities', 'similarity_score')) {
                    $table->dropColumn('similarity_score');
                }
                if (Schema::hasColumn('product_similarities', 'algorithm_type')) {
                    $table->dropColumn('algorithm_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_similarities', function (Blueprint $table) {
            $table->decimal('similarity_score', 10, 6)->nullable();
            $table->string('algorithm_type')->nullable();
        });
    }
};