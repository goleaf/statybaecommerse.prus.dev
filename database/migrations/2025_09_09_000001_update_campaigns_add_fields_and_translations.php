<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('discount_campaigns')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                if (! Schema::hasColumn('discount_campaigns', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('status');
                }
                if (! Schema::hasColumn('discount_campaigns', 'send_notifications')) {
                    $table->boolean('send_notifications')->default(true)->after('is_featured');
                }
                if (! Schema::hasColumn('discount_campaigns', 'track_conversions')) {
                    $table->boolean('track_conversions')->default(true)->after('send_notifications');
                }
                if (! Schema::hasColumn('discount_campaigns', 'max_uses')) {
                    $table->unsignedInteger('max_uses')->nullable()->after('track_conversions');
                }
                if (! Schema::hasColumn('discount_campaigns', 'budget_limit')) {
                    $table->decimal('budget_limit', 12, 2)->nullable()->after('max_uses');
                }
            });
        }

        if (! Schema::hasTable('campaign_translations')) {
            Schema::create('campaign_translations', function (Blueprint $table) {
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
    }

    public function down(): void
    {
        if (Schema::hasTable('campaign_translations')) {
            Schema::dropIfExists('campaign_translations');
        }

        if (Schema::hasTable('discount_campaigns')) {
            Schema::table('discount_campaigns', function (Blueprint $table) {
                foreach (['is_featured', 'send_notifications', 'track_conversions', 'max_uses', 'budget_limit'] as $column) {
                    if (Schema::hasColumn('discount_campaigns', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};


