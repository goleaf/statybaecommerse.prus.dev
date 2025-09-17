<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->text('description')->nullable()->after('value');
            $table->string('hex_color')->nullable()->after('color_code');
            $table->string('image')->nullable()->after('hex_color');
            $table->json('metadata')->nullable()->after('image');
            $table->string('display_value')->nullable()->after('metadata');
            $table->boolean('is_active')->default(true)->after('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'hex_color',
                'image',
                'metadata',
                'display_value',
                'is_active',
            ]);
        });
    }
};