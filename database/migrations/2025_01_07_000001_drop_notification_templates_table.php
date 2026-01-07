<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove foreign key constraint from email_campaigns table
        if (Schema::hasTable('email_campaigns') && Schema::hasColumn('email_campaigns', 'template_id')) {
            Schema::table('email_campaigns', function (Blueprint $table): void {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            });
        }

        // Drop the notification_templates table
        Schema::dropIfExists('notification_templates');
    }

    public function down(): void
    {
        // Recreate notification_templates table
        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // email, sms, push, database
            $table->string('event'); // order_created, user_registered, etc.
            $table->json('subject')->nullable(); // multilingual
            $table->json('content'); // multilingual
            $table->json('variables')->nullable(); // available variables
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Recreate foreign key constraint in email_campaigns table
        if (Schema::hasTable('email_campaigns')) {
            Schema::table('email_campaigns', function (Blueprint $table): void {
                $table->foreignId('template_id')->nullable()->after('status')->constrained('notification_templates')->nullOnDelete();
            });
        }
    }
};
