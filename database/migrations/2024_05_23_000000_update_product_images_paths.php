<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
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

                    if (Str::startsWith($normalized, 'storage/')) {
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
