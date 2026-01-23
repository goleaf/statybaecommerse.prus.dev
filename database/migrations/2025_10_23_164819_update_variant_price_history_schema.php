<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Deprecated: variant price history schema migration is a no-op.
        // Variant history tables are removed and their schema changes are managed
        // by the centralized cleanup migration (see 2026_01_22_000002_cleanup_unused_models.php).

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this migration was deprecated when variant history tables were removed.

    }
};
