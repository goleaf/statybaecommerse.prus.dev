<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }

        $hasNameColumn = Schema::hasColumn('customer_groups', 'name');
        $hasDescriptionColumn = Schema::hasColumn('customer_groups', 'description');

        Schema::table('customer_groups', function (Blueprint $table) use ($hasNameColumn, $hasDescriptionColumn) {
            // Add JSON columns for translations
            if ($hasNameColumn) {
                $table->json('name')->nullable()->change();
            } else {
                $table->json('name')->nullable();
            }

            if ($hasDescriptionColumn) {
                $table->json('description')->nullable()->change();
            } else {
                $table->json('description')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_groups')) {
            return;
        }

        Schema::table('customer_groups', function (Blueprint $table) {
            // Revert to string columns where possible
            if (Schema::hasColumn('customer_groups', 'name')) {
                $table->string('name')->nullable()->change();
            }

            if (Schema::hasColumn('customer_groups', 'description')) {
                $table->text('description')->nullable()->change();
            }
        });
    }
};
