<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('product_images') || ! Schema::hasColumn('product_images', 'path')) {
            return;
        }

        DB::table('product_images')
            ->select('id', 'path')
            ->orderBy('id')
            ->chunkById(100, static function ($images): void {
                foreach ($images as $image) {
                    $path = $image->path ?? '';

                    if (! is_string($path) || $path === '') {
                        continue;
                    }

                    $normalized = ltrim($path, '/');

                    if (Str::contains($normalized, '://') || Str::startsWith($normalized, 'data:')) {
                        // Skip remote assets or embedded data URIs to avoid corrupting external paths.
                        continue;
                    }

                    if (str_contains($normalized, '..')) {
                        // Skip suspicious relative segments that could introduce traversal issues.
                        continue;
                    }

                    if (Str::startsWith($normalized, 'storage/')) {
                        continue;
                    }

                    if (preg_match('/^[a-z0-9]+:\/\//i', $normalized) === 1) {
                        continue;
                    }

                    if (Str::startsWith($normalized, 'data:')) {
                        continue;
                    }

                    if (str_contains($normalized, '..')) {
                        continue;
                    }

                    if (Str::startsWith($normalized, 'public/')) {
                        $normalized = ltrim(Str::after($normalized, 'public/'), '/');
                    }

                    $updatedPath = 'storage/' . ltrim($normalized, '/');

                    DB::table('product_images')
                        ->where('id', $image->id)
                        ->update(['path' => $updatedPath]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_images') || ! Schema::hasColumn('product_images', 'path')) {
            return;
        }

        DB::table('product_images')
            ->select('id', 'path')
            ->orderBy('id')
            ->chunkById(100, static function ($images): void {
                foreach ($images as $image) {
                    $path = $image->path ?? '';

                    if (! is_string($path) || $path === '') {
                        continue;
                    }

                    $normalized = ltrim($path, '/');

                    if (! Str::startsWith($normalized, 'storage/')) {
                        continue;
                    }

                    $updatedPath = ltrim(Str::after($normalized, 'storage/'), '/');

                    DB::table('product_images')
                        ->where('id', $image->id)
                        ->update(['path' => $updatedPath]);
                }
            });
    }
};
