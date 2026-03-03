<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news') || ! Schema::hasColumn('news', 'meta_data')) {
            return;
        }

        Schema::table('news', function (Blueprint $table): void {
            $table->dropColumn('meta_data');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('news') || Schema::hasColumn('news', 'meta_data')) {
            return;
        }

        Schema::table('news', function (Blueprint $table): void {
            $table->json('meta_data')->nullable()->after('view_count');
        });
    }
};
