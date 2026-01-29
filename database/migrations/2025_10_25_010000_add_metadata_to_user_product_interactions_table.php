<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('user_product_interactions')) {
            return;
        }

        Schema::table('user_product_interactions', function (Blueprint $table): void {
            $table->string('notes', 500)->nullable()->after('count');
            $table->boolean('is_anonymous')->default(false)->after('notes');
            $table->string('ip_address', 45)->nullable()->after('is_anonymous');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('user_product_interactions')) {
            return;
        }

        Schema::table('user_product_interactions', function (Blueprint $table): void {
            $table->dropColumn(['notes', 'is_anonymous', 'ip_address']);
        });
    }
};
