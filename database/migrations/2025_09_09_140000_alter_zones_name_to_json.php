<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('zones')) {
            return;
        }

        Schema::table('zones', function (Blueprint $table) {
            if (Schema::hasColumn('zones', 'name')) {
                // On SQLite, altering column types requires recreate or a JSON type can be stored in TEXT.
                // We'll drop and re-add with JSON where supported; for SQLite it becomes TEXT but casted in model.
                $table->json('name')->change();
            }
        });
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
