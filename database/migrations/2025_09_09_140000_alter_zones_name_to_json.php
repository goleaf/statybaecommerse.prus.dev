<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Retain string-based column to stay compatible with existing seeders and casts.
    }

    public function down(): void
    {
        if (! Schema::hasTable('zones')) {
            return;
        }

        Schema::table('zones', function (Blueprint $table) {
            if (Schema::hasColumn('zones', 'name')) {
                $table->string('name')->change();
            }
        });
    }
};
