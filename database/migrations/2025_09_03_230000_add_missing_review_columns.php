<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                if (!Schema::hasColumn('reviews', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('is_approved');
                }
                if (!Schema::hasColumn('reviews', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('reviews', 'locale')) {
                    $table->string('locale', 10)->default('lt')->after('content');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table): void {
                $table->dropColumn(['approved_at', 'rejected_at', 'locale']);
            });
        }
    }
};



