<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variant_analytics', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id')->nullable()->after('variant_id');
            $table->string('date_bucket')->nullable()->after('date');
        });

        $variantProductMap = DB::table('product_variants')->pluck('product_id', 'id');

        DB::table('variant_analytics')
            ->orderBy('id')
            ->lazyById()
            ->each(function ($record) use ($variantProductMap): void {
                $productId = $variantProductMap[$record->variant_id] ?? null;
                $bucketDate = Carbon::parse($record->date)->toDateString();

                DB::table('variant_analytics')
                    ->where('id', $record->id)
                    ->update([
                        'product_id'  => $productId,
                        'date_bucket' => sprintf('daily:%s', $bucketDate),
                    ]);
            });

        Schema::table('variant_analytics', function (Blueprint $table): void {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->dropUnique('variant_analytics_variant_id_date_unique');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE variant_analytics MODIFY product_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE variant_analytics MODIFY date_bucket VARCHAR(191) NOT NULL');
        }

        Schema::table('variant_analytics', function (Blueprint $table): void {
            $table->unique(['product_id', 'variant_id', 'date_bucket'], 'variant_analytics_product_variant_bucket_unique');
        });
    }

    public function down(): void
    {
        Schema::table('variant_analytics', function (Blueprint $table): void {
            $table->dropUnique('variant_analytics_product_variant_bucket_unique');
            $table->unique(['variant_id', 'date'], 'variant_analytics_variant_id_date_unique');
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'date_bucket']);
        });
    }
};
