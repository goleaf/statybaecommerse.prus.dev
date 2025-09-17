<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_campaigns', function (Blueprint $table): void {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('send_notifications')->default(true)->after('is_featured');
            $table->boolean('track_conversions')->default(true)->after('send_notifications');
            $table->unsignedInteger('max_uses')->nullable()->after('track_conversions');
            $table->decimal('budget_limit', 15, 2)->nullable()->after('max_uses');
        });

        Schema::create('campaign_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('discount_campaigns')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('subject')->nullable();
            $table->longText('content')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('banner_alt_text')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'locale']);
            $table->index(['locale']);
            $table->index(['slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_translations');

        Schema::table('discount_campaigns', function (Blueprint $table): void {
            $table->dropColumn([
                'is_featured',
                'send_notifications',
                'track_conversions',
                'max_uses',
                'budget_limit',
            ]);
        });
    }
};
