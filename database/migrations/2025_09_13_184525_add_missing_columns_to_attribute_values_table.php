<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            if (! Schema::hasColumn('attribute_values', 'description')) {
                $table->text('description')->nullable()->after('value');
            }

            if (! Schema::hasColumn('attribute_values', 'hex_color')) {
                $table->string('hex_color')->nullable()->after('color_code');
            }

            if (! Schema::hasColumn('attribute_values', 'image')) {
                $table->string('image')->nullable()->after('hex_color');
            }

            if (! Schema::hasColumn('attribute_values', 'metadata')) {
                $table->json('metadata')->nullable()->after('image');
            }

            if (! Schema::hasColumn('attribute_values', 'display_value')) {
                $table->string('display_value')->nullable()->after('metadata');
            }

            if (! Schema::hasColumn('attribute_values', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $columns = [
                'description',
                'hex_color',
                'image',
                'metadata',
                'display_value',
                'is_active',
            ];

            $existing = array_filter($columns, static fn (string $column): bool => Schema::hasColumn('attribute_values', $column));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
