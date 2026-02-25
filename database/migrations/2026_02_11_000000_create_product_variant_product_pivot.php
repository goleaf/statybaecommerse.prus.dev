<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_variant_product')) {
            Schema::create('product_variant_product', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variant_id');
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                $table->unique(['product_id', 'product_variant_id'], 'product_variant_product_unique');
                $table->index(['product_variant_id'], 'product_variant_product_variant_idx');
            });
        }

        if (Schema::hasTable('product_variants') && Schema::hasTable('product_variant_product')) {
            $now = now();

            DB::table('product_variants')
                ->select(['id', 'product_id'])
                ->whereNotNull('product_id')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($now): void {
                    $payload = [];

                    foreach ($rows as $row) {
                        $payload[] = [
                            'product_id'         => $row->product_id,
                            'product_variant_id' => $row->id,
                            'created_at'         => $now,
                            'updated_at'         => $now,
                        ];
                    }

                    if ($payload !== []) {
                        DB::table('product_variant_product')->upsert(
                            $payload,
                            ['product_id', 'product_variant_id'],
                            ['updated_at']
                        );
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_product');
    }
};
