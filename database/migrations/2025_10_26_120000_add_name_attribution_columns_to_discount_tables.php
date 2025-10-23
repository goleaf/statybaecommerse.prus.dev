<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('discount_codes')) {
            Schema::table('discount_codes', function (Blueprint $table): void {
                if (! Schema::hasColumn('discount_codes', 'created_by_name')) {
                    $table->string('created_by_name')->nullable()->after('created_by');
                }

                if (! Schema::hasColumn('discount_codes', 'updated_by_name')) {
                    $table->string('updated_by_name')->nullable()->after('updated_by');
                }
            });
        }

        if (Schema::hasTable('discount_redemptions')) {
            Schema::table('discount_redemptions', function (Blueprint $table): void {
                if (! Schema::hasColumn('discount_redemptions', 'created_by_name')) {
                    $table->string('created_by_name')->nullable()->after('created_by');
                }

                if (! Schema::hasColumn('discount_redemptions', 'updated_by_name')) {
                    $table->string('updated_by_name')->nullable()->after('updated_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('discount_codes')) {
            Schema::table('discount_codes', function (Blueprint $table): void {
                if (Schema::hasColumn('discount_codes', 'created_by_name')) {
                    $table->dropColumn('created_by_name');
                }

                if (Schema::hasColumn('discount_codes', 'updated_by_name')) {
                    $table->dropColumn('updated_by_name');
                }
            });
        }

        if (Schema::hasTable('discount_redemptions')) {
            Schema::table('discount_redemptions', function (Blueprint $table): void {
                if (Schema::hasColumn('discount_redemptions', 'created_by_name')) {
                    $table->dropColumn('created_by_name');
                }

                if (Schema::hasColumn('discount_redemptions', 'updated_by_name')) {
                    $table->dropColumn('updated_by_name');
                }
            });
        }
    }
};
