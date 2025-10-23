<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('api_keys')) {
            return;
        }

        Schema::table('api_keys', function (Blueprint $table): void {
            if (! Schema::hasColumn('api_keys', 'partner_id')) {
                $table->foreignId('partner_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('partners')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('api_keys', 'permissions')) {
                $table->json('permissions')->nullable()->after('secret');
            }

            if (! Schema::hasColumn('api_keys', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('api_keys')) {
            return;
        }

        Schema::table('api_keys', function (Blueprint $table): void {
            if (Schema::hasColumn('api_keys', 'partner_id')) {
                $table->dropForeign(['partner_id']);
                $table->dropColumn('partner_id');
            }
        });
    }
};
