<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enhanced_settings_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('enhanced_setting_id');
            $table->string('locale', 10);
            $table->text('description')->nullable();
            $table->string('display_name')->nullable();
            $table->text('help_text')->nullable();
            $table->timestamps();

            $table->index('locale');
            $table->unique(['enhanced_setting_id', 'locale']);
            $table->foreign('enhanced_setting_id')
                ->references('id')
                ->on('enhanced_settings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::table('enhanced_settings', function (Blueprint $table): void {
            $table->string('locale', 10)->default('lt')->after('key');
            $table->index('locale');
            $table->dropUnique(['key']);
            $table->unique(['key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('enhanced_settings', function (Blueprint $table): void {
            $table->dropUnique(['key', 'locale']);
            $table->unique(['key']);
            $table->dropIndex(['locale']);
            $table->dropColumn('locale');
        });

        Schema::dropIfExists('enhanced_settings_translations');
    }
};
