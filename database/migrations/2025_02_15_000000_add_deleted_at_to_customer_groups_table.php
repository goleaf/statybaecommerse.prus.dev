<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }

        Schema::table('customer_groups', function (Blueprint $table): void {
            if (! Schema::hasColumn('customer_groups', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }

        Schema::table('customer_groups', function (Blueprint $table): void {
            if (Schema::hasColumn('customer_groups', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
