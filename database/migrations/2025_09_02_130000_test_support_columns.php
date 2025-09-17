<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('sh_discounts')) {
            Schema::table('sh_discounts', function (Blueprint $table): void {
                if (!Schema::hasColumn('sh_discounts', 'min_required')) {
                    $table->decimal('min_required', 15, 2)->default(0)->after('apply_to');
                }
            });
        } elseif (Schema::hasTable('discounts')) {
            Schema::table('discounts', function (Blueprint $table): void {
                if (!Schema::hasColumn('discounts', 'min_required')) {
                    $table->decimal('min_required', 15, 2)->default(0)->after('apply_to');
                }
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

        if (!Schema::hasTable('sh_channels')) {
            Schema::create('sh_channels', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('slug')->nullable()->unique();
                $table->string('url')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_default')->default(false);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
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
        // Non-destructive
    }
};
