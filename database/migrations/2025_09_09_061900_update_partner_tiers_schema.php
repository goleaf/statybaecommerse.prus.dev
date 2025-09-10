<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('partner_tiers')) {
            Schema::create('partner_tiers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->decimal('discount_rate', 6, 4)->default(0);
                $table->decimal('commission_rate', 6, 4)->default(0);
                $table->decimal('minimum_order_value', 12, 2)->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->json('benefits')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
            return;
        }

        Schema::table('partner_tiers', function (Blueprint $table) {
            if (! Schema::hasColumn('partner_tiers', 'code')) {
                $table->string('code')->nullable();
            }
            if (! Schema::hasColumn('partner_tiers', 'discount_rate')) {
                $table->decimal('discount_rate', 6, 4)->default(0);
            }
            if (! Schema::hasColumn('partner_tiers', 'commission_rate')) {
                $table->decimal('commission_rate', 6, 4)->default(0);
            }
            if (! Schema::hasColumn('partner_tiers', 'minimum_order_value')) {
                $table->decimal('minimum_order_value', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('partner_tiers', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true);
            }
            if (! Schema::hasColumn('partner_tiers', 'benefits')) {
                $table->json('benefits')->nullable();
            }
            if (! Schema::hasColumn('partner_tiers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Best-effort: add unique index if possible (skip if unsupported during tests)
        try {
            Schema::table('partner_tiers', function (Blueprint $table) {
                if (Schema::hasColumn('partner_tiers', 'code')) {
                    $table->unique('code');
                }
            });
        } catch (\Throwable) {
            // Ignore index creation errors on SQLite test env
        }
    }

    public function down(): void
    {
        // We won't drop columns to avoid SQLite limitations; leave table as-is
    }
};
