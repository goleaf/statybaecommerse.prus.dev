<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('system_logs');
    }

    public function down(): void
    {
        // Intentionally left blank: we do not recreate removed tables.
    }
};
