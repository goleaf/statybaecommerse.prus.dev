<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('enhanced_settings', 'is_active')) {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                $table->boolean('is_active')->nullable()->default(true)->after('is_encrypted');
            });

            DB::table('enhanced_settings')
                ->whereNull('is_active')
                ->update(['is_active' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('enhanced_settings', 'is_active')) {
            Schema::table('enhanced_settings', function (Blueprint $table): void {
                $table->dropColumn('is_active');
            });
        }
    }
};
