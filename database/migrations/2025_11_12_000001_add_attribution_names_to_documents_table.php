<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bail out early when the documents table has not been provisioned yet.
        if (! Schema::hasTable('documents')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table): void {
            // Store the human readable author name alongside the foreign key for auditing.
            if (! Schema::hasColumn('documents', 'created_by_name')) {
                $table->string('created_by_name')->nullable()->after('created_by');
            }

            // Mirror the author name for the last updater so UI listings remain denormalised.
            if (! Schema::hasColumn('documents', 'updated_by_name')) {
                $table->string('updated_by_name')->nullable()->after('updated_by');
            }
        });
    }

    public function down(): void
    {
        // Skip the rollback when the target table is missing (e.g. partial installs).
        if (! Schema::hasTable('documents')) {
            return;
        }

        Schema::table('documents', function (Blueprint $table): void {
            // Drop the attribution columns defensively to avoid exceptions on repeated rollbacks.
            foreach (['created_by_name', 'updated_by_name'] as $column) {
                if (Schema::hasColumn('documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
