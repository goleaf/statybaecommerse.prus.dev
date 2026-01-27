<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('news_category_pivot');
        Schema::dropIfExists('news_category_translations');
        Schema::dropIfExists('news_categories');
        Schema::dropIfExists('news_approvals');

        if (Schema::hasTable('news')) {
            $columns = [];

            foreach ([
                'moderation_status',
                'moderation_notes',
                'moderated_at',
                'moderated_by',
            ] as $column) {
                if (Schema::hasColumn('news', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                Schema::table('news', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }

    public function down(): void {}
};
