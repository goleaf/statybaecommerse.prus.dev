<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('partners')) {
            return;
        }

        Schema::table('partners', function (Blueprint $table) {
            if (! Schema::hasColumn('partners', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('tier_id');
            }
            if (! Schema::hasColumn('partners', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_email');
            }
            if (! Schema::hasColumn('partners', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('contact_phone');
            }
            if (! Schema::hasColumn('partners', 'discount_rate')) {
                $table->decimal('discount_rate', 6, 4)->default(0)->after('is_enabled');
            }
            if (! Schema::hasColumn('partners', 'commission_rate')) {
                $table->decimal('commission_rate', 6, 4)->default(0)->after('discount_rate');
            }
            if (! Schema::hasColumn('partners', 'metadata')) {
                $table->json('metadata')->nullable()->after('commission_rate');
            }
        });
    }

    public function down(): void
    {
        // Intentionally no-op to avoid SQLite limitations during tests
    }
};
