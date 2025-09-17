<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // No-op: stock tables are created and managed in earlier base migrations
    }

    public function down(): void
    {
        // No-op
    }
};
