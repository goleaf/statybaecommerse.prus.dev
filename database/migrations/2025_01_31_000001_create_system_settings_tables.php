<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // System Setting Categories Table
        Schema::create('system_setting_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('primary');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
            $table->index('parent_id');
            $table->foreign('parent_id')->references('id')->on('system_setting_categories')->onDelete('cascade');
        });

        // System Settings Table
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, text, number, boolean, array, json, file, image, select, color, date, datetime
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_readonly')->default(false);
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable(); // For select, checkbox, radio options
            $table->text('default_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active', 'sort_order']);
            $table->index(['group', 'is_active']);
            $table->index(['is_public', 'is_active']);
            $table->index('updated_by');

            $table->foreign('category_id')->references('id')->on('system_setting_categories')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // System Setting Translations Table
        Schema::create('system_setting_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('system_setting_id');
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('help_text')->nullable();
            $table->timestamps();

            $table->unique(['system_setting_id', 'locale']);
            $table->foreign('system_setting_id')->references('id')->on('system_settings')->onDelete('cascade');
        });

        // System Setting Category Translations Table
        Schema::create('system_setting_category_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('system_setting_category_id');
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['system_setting_category_id', 'locale'], 'system_setting_cat_locale_unique');
            $table->foreign('system_setting_category_id', 'system_setting_category_fk')->references('id')->on('system_setting_categories')->onDelete('cascade');
        });

        // System Setting History Table (for audit trail)
        Schema::create('system_setting_history', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('system_setting_id');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->string('change_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['system_setting_id', 'created_at']);
            $table->index('changed_by');

            $table->foreign('system_setting_id')->references('id')->on('system_settings')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
        });

        // System Setting Dependencies Table (for setting relationships)
        Schema::create('system_setting_dependencies', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('setting_id');
            $table->unsignedBigInteger('depends_on_setting_id');
            $table->json('condition')->nullable(); // JSON with operator and value
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['setting_id', 'is_active']);
            $table->index('depends_on_setting_id');

            $table->foreign('setting_id')->references('id')->on('system_settings')->onDelete('cascade');
            $table->foreign('depends_on_setting_id')->references('id')->on('system_settings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_setting_dependencies');
        Schema::dropIfExists('system_setting_history');
        Schema::dropIfExists('system_setting_category_translations');
        Schema::dropIfExists('system_setting_translations');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('system_setting_categories');
    }
};
