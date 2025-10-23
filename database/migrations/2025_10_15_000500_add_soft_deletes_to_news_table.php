<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news')) {
            return;
        }

        Schema::table('news', function (Blueprint $table): void {
            if (! Schema::hasColumn('news', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('news')) {
            return;
        }

        Schema::table('news', function (Blueprint $table): void {
            if (Schema::hasColumn('news', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
