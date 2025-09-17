<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // No-op: cart_items schema finalized in earlier migrations
    }

    public function down(): void
    {
        // No-op
    }
};
