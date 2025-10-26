<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_variant_translations')) {
            Schema::create('product_variant_translations', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_variant_id');
                $table->string('locale', 10);
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamps();

                $table->foreign('product_variant_id')
                    ->references('id')
                    ->on('product_variants')
                    ->cascadeOnDelete();

                $table->unique(['product_variant_id', 'locale'], 'product_variant_locale_unique');
                $table->index('locale', 'product_variant_translations_locale_idx');
            });
        }

        if (! Schema::hasTable('product_variants') || ! Schema::hasTable('product_variant_translations')) {
            return;
        }

        $columns = collect([
            'variant_name_lt',
            'variant_name_en',
            'description_lt',
            'description_en',
            'seo_title_lt',
            'seo_title_en',
            'seo_description_lt',
            'seo_description_en',
        ])->filter(static fn (string $column): bool => Schema::hasColumn('product_variants', $column))
            ->values();

        if ($columns->isEmpty()) {
            return;
        }

        DB::table('product_variants')
            ->select([
                'id',
                'name',
                'variant_name_lt',
                'variant_name_en',
                'description_lt',
                'description_en',
                'seo_title_lt',
                'seo_title_en',
                'seo_description_lt',
                'seo_description_en',
            ])
            ->orderBy('id')
            ->chunkById(500, function (Collection $variants): void {
                $timestamp = now();
                $payload = [];

                foreach ($variants as $variant) {
                    $baseName = $variant->name;

                    $payload[] = [
                        'product_variant_id' => $variant->id,
                        'locale'             => 'lt',
                        'name'               => $variant->variant_name_lt ?? $baseName,
                        'description'        => $variant->description_lt,
                        'seo_title'          => $variant->seo_title_lt,
                        'seo_description'    => $variant->seo_description_lt,
                        'created_at'         => $timestamp,
                        'updated_at'         => $timestamp,
                    ];

                    $payload[] = [
                        'product_variant_id' => $variant->id,
                        'locale'             => 'en',
                        'name'               => $variant->variant_name_en ?? $baseName,
                        'description'        => $variant->description_en,
                        'seo_title'          => $variant->seo_title_en,
                        'seo_description'    => $variant->seo_description_en,
                        'created_at'         => $timestamp,
                        'updated_at'         => $timestamp,
                    ];
                }

                if ($payload !== []) {
                    DB::table('product_variant_translations')
                        ->upsert(
                            $payload,
                            ['product_variant_id', 'locale'],
                            ['name', 'description', 'seo_title', 'seo_description', 'updated_at']
                        );
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('product_variant_translations')) {
            Schema::dropIfExists('product_variant_translations');
        }
    }
};
