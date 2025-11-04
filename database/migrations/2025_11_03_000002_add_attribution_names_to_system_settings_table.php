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
            if (! Schema::hasColumn('system_settings', 'created_by_name') && Schema::hasColumn('system_settings', 'created_by')) {
                $table->string('created_by_name')->nullable()->after('created_by');
            }

            if (! Schema::hasColumn('system_settings', 'updated_by_name') && Schema::hasColumn('system_settings', 'updated_by')) {
                $table->string('updated_by_name')->nullable()->after('updated_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('system_settings', 'created_by_name')) {
                $table->dropColumn('created_by_name');
            }

            if (Schema::hasColumn('system_settings', 'updated_by_name')) {
                $table->dropColumn('updated_by_name');
            }
        });
    }
};


