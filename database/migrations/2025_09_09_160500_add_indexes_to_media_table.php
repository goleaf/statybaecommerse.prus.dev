<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Forward-only: this migration is intentionally left empty to avoid duplicate/conditional index creation.
    }

    public function down(): void
    {
        // No-op: nothing to roll back since up() made no schema changes.
    }
};
