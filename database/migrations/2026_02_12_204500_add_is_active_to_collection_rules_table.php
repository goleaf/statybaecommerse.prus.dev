<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('collection_rules') || Schema::hasColumn('collection_rules', 'is_active')) {
            return;
        }

        Schema::table('collection_rules', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('position');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('collection_rules') || ! Schema::hasColumn('collection_rules', 'is_active')) {
            return;
        }

        Schema::table('collection_rules', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
