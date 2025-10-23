<?php

declare(strict_types=1);

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
        Schema::table('email_campaigns', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('name');
            $table->string('from_email')->nullable()->after('subject');
            $table->string('from_name')->nullable()->after('from_email');
            $table->string('reply_to')->nullable()->after('from_name');
            $table->boolean('is_active')->default(true)->after('sent_at');
            $table->foreignId('template_id')->nullable()->after('status')->constrained('notification_templates')->nullOnDelete();
            $table->json('settings')->nullable()->after('template_id');

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table): void {
            $table->dropIndex(['is_active']);
            $table->dropForeign(['template_id']);

            $table->dropColumn([
                'description',
                'from_email',
                'from_name',
                'reply_to',
                'is_active',
                'template_id',
                'settings',
            ]);
        });
    }
};
