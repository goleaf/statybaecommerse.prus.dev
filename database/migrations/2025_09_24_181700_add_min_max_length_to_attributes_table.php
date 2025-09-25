<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attributes')) {
            return;
        }

        Schema::table('attributes', function (Blueprint $table) {
            if (! Schema::hasColumn('attributes', 'min_length')) {
                $table->integer('min_length')->nullable()->after('description');
            }
            if (! Schema::hasColumn('attributes', 'max_length')) {
                $table->integer('max_length')->nullable()->after('min_length');
            }

            if (! Schema::hasColumn('attributes', 'min_value')) {
                $table->decimal('min_value', 10, 2)->nullable()->after('max_length');
            }
            if (! Schema::hasColumn('attributes', 'max_value')) {
                $table->decimal('max_value', 10, 2)->nullable()->after('min_value');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('attributes')) {
            return;
        }

        Schema::table('attributes', function (Blueprint $table) {
            try {
                $table->dropColumn('min_length');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropColumn('max_length');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropColumn('min_value');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropColumn('max_value');
            } catch (\Throwable $e) {
            }
        });
    }
};
