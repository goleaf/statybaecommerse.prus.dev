<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update price_lists table to add new fields (forward-only)
        if (Schema::hasTable('price_lists')) {
            Schema::table('price_lists', function (Blueprint $table): void {
                if (!Schema::hasColumn('price_lists', 'description')) {
                    if (Schema::hasColumn('price_lists', 'code')) {
                        $table->text('description')->nullable()->after('code');
                    } else {
                        $table->text('description')->nullable();
                    }
                }
                if (!Schema::hasColumn('price_lists', 'metadata')) {
                    $table->json('metadata')->nullable()->after('description');
                }
                if (!Schema::hasColumn('price_lists', 'is_default')) {
                    $table->boolean('is_default')->default(false)->after('is_enabled');
                }
                if (!Schema::hasColumn('price_lists', 'auto_apply')) {
                    $table->boolean('auto_apply')->default(false)->after('is_default');
                }
                if (!Schema::hasColumn('price_lists', 'min_order_amount')) {
                    $table->decimal('min_order_amount', 15, 2)->nullable()->after('auto_apply');
                }
                if (!Schema::hasColumn('price_lists', 'max_order_amount')) {
                    $table->decimal('max_order_amount', 15, 2)->nullable()->after('min_order_amount');
                }
            });
        }

        // Create price_list_translations table
        if (!Schema::hasTable('price_list_translations')) {
            Schema::create('price_list_translations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('price_list_id');
                $table->string('locale', 5);
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->json('meta_keywords')->nullable();
                $table->timestamps();

                $table->unique(['price_list_id', 'locale']);
                $table->index(['locale']);
                $table->foreign('price_list_id', 'plt_price_list_fk')->references('id')->on('price_lists')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_translations');

        if (Schema::hasTable('price_lists')) {
            Schema::table('price_lists', function (Blueprint $table): void {
                foreach ([
                    'description',
                    'metadata',
                    'is_default',
                    'auto_apply',
                    'min_order_amount',
                    'max_order_amount',
                ] as $col) {
                    if (Schema::hasColumn('price_lists', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
