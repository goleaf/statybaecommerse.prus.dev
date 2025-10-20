<?php

declare(strict_types=1);

use App\Enums\ModerationState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table): void {
                if (! Schema::hasColumn('news', 'moderation_state')) {
                    $table->string('moderation_state')->default(ModerationState::Draft->value)->after('is_featured');
                    $table->timestamp('submitted_for_review_at')->nullable()->after('moderation_state');
                    $table->timestamp('approved_at')->nullable()->after('submitted_for_review_at');
                    $table->foreignId('approved_by_id')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                    $table->index('moderation_state');
                }
            });
        }

        if (! Schema::hasTable('news_approvals')) {
            Schema::create('news_approvals', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('news_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('decision');
                $table->text('notes')->nullable();
                $table->timestamp('decided_at')->useCurrent();
                $table->timestamps();

                $table->index(['news_id', 'decided_at']);
            });
        }

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table): void {
                if (! Schema::hasColumn('posts', 'moderation_state')) {
                    $table->string('moderation_state')->default(ModerationState::Draft->value)->after('status');
                    $table->timestamp('submitted_for_review_at')->nullable()->after('moderation_state');
                    $table->timestamp('approved_at')->nullable()->after('submitted_for_review_at');
                    $table->foreignId('approved_by_id')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
                    $table->index('moderation_state');
                }
            });
        }

        if (! Schema::hasTable('post_approvals')) {
            Schema::create('post_approvals', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('decision');
                $table->text('notes')->nullable();
                $table->timestamp('decided_at')->useCurrent();
                $table->timestamps();

                $table->index(['post_id', 'decided_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table): void {
                if (Schema::hasColumn('news', 'moderation_state')) {
                    $table->dropIndex('news_moderation_state_index');
                    $table->dropForeign(['approved_by_id']);
                    $table->dropColumn(['moderation_state', 'submitted_for_review_at', 'approved_at', 'approved_by_id']);
                }
            });
        }

        Schema::dropIfExists('news_approvals');

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table): void {
                if (Schema::hasColumn('posts', 'moderation_state')) {
                    $table->dropIndex('posts_moderation_state_index');
                    $table->dropForeign(['approved_by_id']);
                    $table->dropColumn(['moderation_state', 'submitted_for_review_at', 'approved_at', 'approved_by_id']);
                }
            });
        }

        Schema::dropIfExists('post_approvals');
    }
};
