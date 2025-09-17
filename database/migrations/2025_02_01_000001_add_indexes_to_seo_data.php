<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seo_data')) {
            Schema::table('seo_data', function (Blueprint $table) {
                if (! Schema::hasColumn('seo_data', 'created_at')) {
                    $table->timestamps();
                }
                $table->index(['locale']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('seo_data')) {
            Schema::table('seo_data', function (Blueprint $table) {
                // indexes will be dropped automatically with table or can be left
            });
        }
    }
};
