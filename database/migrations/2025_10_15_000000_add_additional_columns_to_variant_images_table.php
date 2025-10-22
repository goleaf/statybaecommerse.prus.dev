<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('variant_images', function (Blueprint $table): void {
            // Ensure images can be toggled without removing records entirely.
            if (! Schema::hasColumn('variant_images', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_primary');
            }

            // Store the original file size so we can display it later without disk access.
            if (! Schema::hasColumn('variant_images', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('is_active');
            }

            // Persist the image dimensions to avoid repeatedly reading image metadata from disk.
            if (! Schema::hasColumn('variant_images', 'dimensions')) {
                $table->string('dimensions')->nullable()->after('file_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToDrop = collect(['dimensions', 'file_size', 'is_active'])
            ->filter(fn (string $column): bool => Schema::hasColumn('variant_images', $column))
            ->values()
            ->all();

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('variant_images', function (Blueprint $table) use ($columnsToDrop): void {
            // Remove the dynamically added columns if they exist during rollback.
            $table->dropColumn($columnsToDrop);
        });
    }
};
