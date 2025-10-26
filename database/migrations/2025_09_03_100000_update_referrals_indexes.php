<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            // Drop the unique constraint that prevented a referrer from using the same
            // referral code for multiple invitees. Each user should own a unique code,
            // but that code must be reusable for the entire referral tree.
            if (Schema::hasColumn('referrals', 'referral_code') && $this->hasUniqueIndex('referrals', 'referrals_referral_code_unique')) {
                $table->dropUnique('referrals_referral_code_unique');
            }

            // Enforce uniqueness on the referred user so we do not create duplicate
            // referral records for the same person when multiple codes are attempted.
            if (! $this->hasUniqueIndex('referrals', 'referrals_referred_id_unique')) {
                $table->unique('referred_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            if (! $this->hasUniqueIndex('referrals', 'referrals_referral_code_unique')) {
                $table->unique('referral_code');
            }

            if ($this->hasUniqueIndex('referrals', 'referrals_referred_id_unique')) {
                $table->dropUnique('referrals_referred_id_unique');
            }
        });
    }

    /**
     * Determine if the given table already has a unique index for the columns.
     */
    private function hasUniqueIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        if ($connection->getDriverName() !== 'mysql') {
            // SQLite and other drivers used in testing environments do not
            // expose INFORMATION_SCHEMA in the same way, so skip the lookup.
            return false;
        }

        $database = $connection->getDatabaseName();

        // Inspect the INFORMATION_SCHEMA to determine whether the unique index
        // already exists. This approach avoids the optional Doctrine DBAL
        // dependency while remaining portable across MySQL-compatible drivers.
        $result = DB::select(
            'SELECT COUNT(*) AS total FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? AND non_unique = 0',
            [$database, $table, $indexName]
        );

        return (int) ($result[0]->total ?? 0) > 0;
    }
};
