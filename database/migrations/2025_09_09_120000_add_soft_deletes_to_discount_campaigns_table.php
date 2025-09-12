<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('discount_campaigns') && ! Schema::hasColumn('discount_campaigns', 'deleted_at')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('discount_campaigns') && Schema::hasColumn('discount_campaigns', 'deleted_at')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
