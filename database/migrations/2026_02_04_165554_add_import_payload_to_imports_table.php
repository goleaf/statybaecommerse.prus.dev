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
        Schema::table('imports', function (Blueprint $table): void {
            if (! Schema::hasColumn('imports', 'column_map')) {
                $table->json('column_map')->nullable()->after('importer');
            }
            if (! Schema::hasColumn('imports', 'options')) {
                $table->json('options')->nullable()->after('column_map');
            }
            if (! Schema::hasColumn('imports', 'file_disk')) {
                $table->string('file_disk')->nullable()->after('file_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table): void {
            if (Schema::hasColumn('imports', 'file_disk')) {
                $table->dropColumn('file_disk');
            }
            if (Schema::hasColumn('imports', 'options')) {
                $table->dropColumn('options');
            }
            if (Schema::hasColumn('imports', 'column_map')) {
                $table->dropColumn('column_map');
            }
        });
    }
};
