<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy migration stub intentionally left empty. Variant inventories are created in 2025_09_09_000100_create_inventories_tables.php

    }

    public function down(): void
    {
        Schema::dropIfExists('sh_variant_inventories');
    }
};
