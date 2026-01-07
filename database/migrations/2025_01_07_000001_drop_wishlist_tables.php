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
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('user_wishlists');
        Schema::dropIfExists('wishlists');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible - wishlist functionality has been completely removed
    }
};
