<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('system_settings', 'category')) {
                $table->string('category')->nullable()->after('type');
                $table->index('category');
            }

            if (! Schema::hasColumn('system_settings', 'unit')) {
                $table->string('unit')->nullable()->after('default_value');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('system_settings', 'category')) {
                $table->dropIndex(['category']);
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('system_settings', 'unit')) {
                $table->dropColumn('unit');
            }
        });
    }
};

