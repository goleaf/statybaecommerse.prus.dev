<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Add translation fields
            $table->json('title_translations')->nullable()->after('title');
            $table->json('content_translations')->nullable()->after('content');
            $table->json('excerpt_translations')->nullable()->after('excerpt');
            $table->json('meta_title_translations')->nullable()->after('meta_title');
            $table->json('meta_description_translations')->nullable()->after('meta_description');

            // Add additional fields for better functionality
            $table->string('tags')->nullable()->after('meta_description');
            $table->json('tags_translations')->nullable()->after('tags');
            $table->integer('views_count')->default(0)->after('featured');
            $table->integer('likes_count')->default(0)->after('views_count');
            $table->integer('comments_count')->default(0)->after('likes_count');
            $table->boolean('allow_comments')->default(true)->after('comments_count');
            $table->boolean('is_pinned')->default(false)->after('allow_comments');

            // Add indexes for better performance
            $table->index(['status', 'featured', 'published_at']);
            $table->index(['user_id', 'status']);
            $table->index(['is_pinned', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'title_translations',
                'content_translations',
                'excerpt_translations',
                'meta_title_translations',
                'meta_description_translations',
                'tags',
                'tags_translations',
                'views_count',
                'likes_count',
                'comments_count',
                'allow_comments',
                'is_pinned',
            ]);

            $table->dropIndex(['status', 'featured', 'published_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['is_pinned', 'published_at']);
        });
    }
};

