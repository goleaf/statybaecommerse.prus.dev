<?php

declare(strict_types=1);

use App\Models\EmailCampaign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(EmailCampaign::class)->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('status')->default('pending');
            $table->json('metadata')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('bounce_reason')->nullable();
            $table->string('error_message')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedSmallInteger('delivery_attempts')->default(0);
            $table->boolean('is_delivered')->default(false);
            $table->boolean('is_opened')->default(false);
            $table->boolean('is_clicked')->default(false);
            $table->boolean('is_bounced')->default(false);
            $table->boolean('is_unsubscribed')->default(false);
            $table->timestamps();

            $table->unique(['email_campaign_id', 'email']);
            $table->index('status');
            $table->index('is_delivered');
            $table->index('is_bounced');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
    }
};
