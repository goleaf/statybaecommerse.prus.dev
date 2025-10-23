<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('shipping_options', 'shipping_matrix')) {
            Schema::table('shipping_options', function (Blueprint $table): void {
                $table->json('shipping_matrix')->nullable()->after('metadata');
            });
        }

        if (Schema::hasColumn('shipping_options', 'shipping_matrix')) {
            DB::table('shipping_options')
                ->whereNull('shipping_matrix')
                ->update(['shipping_matrix' => json_encode([])]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('shipping_options', 'shipping_matrix')) {
            Schema::table('shipping_options', function (Blueprint $table): void {
                $table->dropColumn('shipping_matrix');
            });
        }
    }
};
