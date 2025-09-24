<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                if (! Schema::hasColumn('reviews', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_approved');
                }
                if (! Schema::hasColumn('reviews', 'metadata')) {
                    $table->json('metadata')->nullable()->after('content');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropColumn(['is_featured', 'metadata']);
            });
        }
    }
};
