<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_discounts') && !Schema::hasColumn('sh_discounts', 'min_required')) {
            Schema::table('sh_discounts', function (Blueprint $table): void {
                $table->unsignedDecimal('min_required', 12, 2)->default(0)->after('apply_to');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (!Schema::hasColumn('users', 'first_name')) {
                    $table->string('first_name')->nullable()->after('id');
                }
                if (!Schema::hasColumn('users', 'last_name')) {
                    $table->string('last_name')->nullable()->after('first_name');
                }
            });
        }

        if (!Schema::hasTable('sh_zones')) {
            Schema::create('sh_zones', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('code')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->unsignedBigInteger('currency_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sh_categories')) {
            Schema::create('sh_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // No destructive down required for test support
    }
};
