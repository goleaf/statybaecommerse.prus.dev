<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('review_translations')) {
            Schema::create('review_translations', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('review_id');
                $table->string('locale', 10);
                $table->string('title')->nullable();
                $table->text('comment')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['review_id', 'locale'], 'rt_review_locale_unique');
                $table->index(['locale', 'review_id']);
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                if (!Schema::hasColumn('reviews', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('is_approved');
                }
                if (!Schema::hasColumn('reviews', 'metadata')) {
                    if (Schema::hasColumn('reviews', 'comment')) {
                        $table->json('metadata')->nullable()->after('comment');
                    } elseif (Schema::hasColumn('reviews', 'content')) {
                        $table->json('metadata')->nullable()->after('content');
                    } else {
                        $table->json('metadata')->nullable();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('review_translations');

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                if (Schema::hasColumn('reviews', 'metadata')) {
                    $table->dropColumn('metadata');
                }
                if (Schema::hasColumn('reviews', 'is_featured')) {
                    $table->dropColumn('is_featured');
                }
            });
        }
    }
};
