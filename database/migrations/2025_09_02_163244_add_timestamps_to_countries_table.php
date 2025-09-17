<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                if (! Schema::hasColumn('countries', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                if (Schema::hasColumn('countries', 'created_at')) {
                    $table->dropTimestamps();
                }
            });
        }
    }
};
