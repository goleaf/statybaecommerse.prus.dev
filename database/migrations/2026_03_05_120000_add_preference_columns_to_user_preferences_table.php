<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_preferences')) {
            return;
        }

        Schema::table('user_preferences', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_preferences', 'preference_type')) {
                $table->string('preference_type')->nullable();
            }

            if (! Schema::hasColumn('user_preferences', 'preference_key')) {
                $table->string('preference_key')->nullable();
            }

            if (! Schema::hasColumn('user_preferences', 'preference_score')) {
                $table->decimal('preference_score', 10, 6)->nullable();
            }

            if (! Schema::hasColumn('user_preferences', 'last_updated')) {
                $table->timestamp('last_updated')->nullable();
            }

            if (! Schema::hasColumn('user_preferences', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_preferences')) {
            return;
        }

        $columns = [
            'metadata',
            'last_updated',
            'preference_score',
            'preference_key',
            'preference_type',
        ];

        $existing = array_values(array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn('user_preferences', $column),
        ));

        if ($existing === []) {
            return;
        }

        Schema::table('user_preferences', function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }
};
