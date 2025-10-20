<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table): void {
                if (! Schema::hasColumn('news', 'is_breaking')) {
                    $table->boolean('is_breaking')->default(false)->after('is_featured');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table): void {
                if (Schema::hasColumn('news', 'is_breaking')) {
                    $table->dropColumn('is_breaking');
                }
            });
        }
    }
};
