<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('discount_conditions')) {
            return;
        }

        Schema::table('discount_conditions', function (Blueprint $table): void {
            if (! Schema::hasColumn('discount_conditions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('position');
            }

            if (! Schema::hasColumn('discount_conditions', 'priority')) {
                $table->integer('priority')->default(0)->after('is_active');
            }

            if (! Schema::hasColumn('discount_conditions', 'metadata')) {
                $table->json('metadata')->nullable()->after('priority');
            }

            if (! $this->indexExists('discount_conditions', 'discount_conditions_is_active_index')) {
                $table->index(['is_active'], 'discount_conditions_is_active_index');
            }
            if (! $this->indexExists('discount_conditions', 'discount_conditions_priority_index')) {
                $table->index(['priority'], 'discount_conditions_priority_index');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('discount_conditions')) {
            return;
        }

        Schema::table('discount_conditions', function (Blueprint $table): void {
            if (Schema::hasColumn('discount_conditions', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('discount_conditions', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('discount_conditions', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            return Schema::getConnection()->getDoctrineSchemaManager()->introspectTable($table)->hasIndex($index);
        } catch (\Throwable $e) {
            return false;
        }
    }
};

