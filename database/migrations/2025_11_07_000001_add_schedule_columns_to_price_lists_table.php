<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bail out early if the owning table does not exist in the current schema snapshot.
        if (! Schema::hasTable('price_lists')) {
            return;
        }

        Schema::table('price_lists', function (Blueprint $table): void {
            // Add the starts_at column when upgrading legacy schemas that never exposed scheduling windows.
            if (! Schema::hasColumn('price_lists', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('priority');
            }

            // Add the ends_at column while gracefully handling installs that add starts_at in later patches.
            if (! Schema::hasColumn('price_lists', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after(
                    Schema::hasColumn('price_lists', 'starts_at') ? 'starts_at' : 'priority'
                );
            }
        });
    }

    public function down(): void
    {
        // Skip the rollback if the table is absent so down migrations stay idempotent in CI.
        if (! Schema::hasTable('price_lists')) {
            return;
        }

        Schema::table('price_lists', function (Blueprint $table): void {
            // Drop each column conditionally to support mixed environments without triggering SQL errors.
            if (Schema::hasColumn('price_lists', 'ends_at')) {
                $table->dropColumn('ends_at');
            }

            if (Schema::hasColumn('price_lists', 'starts_at')) {
                $table->dropColumn('starts_at');
            }
        });
    }
};
