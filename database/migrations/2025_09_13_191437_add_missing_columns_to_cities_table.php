<?php

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
        if (! Schema::hasTable('cities')) {
            return;
        }

        Schema::table('cities', function (Blueprint $table) {
            $columns = [
                'type' => fn () => $table->string('type')->nullable(),
                'area' => fn () => $table->decimal('area', 10, 2)->nullable(),
                'density' => fn () => $table->decimal('density', 10, 2)->nullable(),
                'elevation' => fn () => $table->decimal('elevation', 10, 2)->nullable(),
                'timezone' => fn () => $table->string('timezone')->nullable(),
                'currency_code' => fn () => $table->string('currency_code', 3)->nullable(),
                'currency_symbol' => fn () => $table->string('currency_symbol', 5)->nullable(),
                'language_code' => fn () => $table->string('language_code', 5)->nullable(),
                'language_name' => fn () => $table->string('language_name')->nullable(),
                'phone_code' => fn () => $table->string('phone_code', 10)->nullable(),
                'postal_code' => fn () => $table->string('postal_code', 20)->nullable(),
            ];

            foreach ($columns as $name => $definition) {
                if (! Schema::hasColumn('cities', $name)) {
                    $definition();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('cities')) {
            return;
        }

        Schema::table('cities', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('cities', 'type') ? 'type' : null,
                Schema::hasColumn('cities', 'area') ? 'area' : null,
                Schema::hasColumn('cities', 'density') ? 'density' : null,
                Schema::hasColumn('cities', 'elevation') ? 'elevation' : null,
                Schema::hasColumn('cities', 'timezone') ? 'timezone' : null,
                Schema::hasColumn('cities', 'currency_code') ? 'currency_code' : null,
                Schema::hasColumn('cities', 'currency_symbol') ? 'currency_symbol' : null,
                Schema::hasColumn('cities', 'language_code') ? 'language_code' : null,
                Schema::hasColumn('cities', 'language_name') ? 'language_name' : null,
                Schema::hasColumn('cities', 'phone_code') ? 'phone_code' : null,
                Schema::hasColumn('cities', 'postal_code') ? 'postal_code' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
