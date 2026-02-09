<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'size')) {
                $table->string('size')->nullable();
            }
            if (! Schema::hasColumn('products', 'size_type')) {
                $table->string('size_type')->nullable();
            }
            if (! Schema::hasColumn('products', 'color')) {
                $table->string('color')->nullable();
            }
            if (! Schema::hasColumn('products', 'pack_size')) {
                $table->string('pack_size')->nullable();
            }
            if (! Schema::hasColumn('products', 'pack_size_type')) {
                $table->string('pack_size_type')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'pack_size_type')) {
                $table->dropColumn('pack_size_type');
            }
            if (Schema::hasColumn('products', 'pack_size')) {
                $table->dropColumn('pack_size');
            }
            if (Schema::hasColumn('products', 'color')) {
                $table->dropColumn('color');
            }
            if (Schema::hasColumn('products', 'size_type')) {
                $table->dropColumn('size_type');
            }
            if (Schema::hasColumn('products', 'size')) {
                $table->dropColumn('size');
            }
        });
    }
};
