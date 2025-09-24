<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('campaign_discount')) {
            return;
        }

        $campaignForeign = 'campaign_discount_campaign_id_foreign';
        $discountForeign = 'campaign_discount_discount_id_foreign';
        $uniqueConstraint = 'campaign_discount_campaign_id_discount_id_unique';
        $campaignIndex = 'campaign_discount_campaign_id_index';
        $discountIndex = 'campaign_discount_discount_id_index';

        $shouldAddCampaignForeign = ! $this->hasForeignKey('campaign_discount', $campaignForeign);
        $shouldAddDiscountForeign = ! $this->hasForeignKey('campaign_discount', $discountForeign);
        $shouldAddUnique = ! $this->hasUniqueConstraint('campaign_discount', $uniqueConstraint);
        $shouldAddCampaignIndex = ! $this->hasIndex('campaign_discount', $campaignIndex);
        $shouldAddDiscountIndex = ! $this->hasIndex('campaign_discount', $discountIndex);

        if (! $shouldAddCampaignForeign && ! $shouldAddDiscountForeign && ! $shouldAddUnique && ! $shouldAddCampaignIndex && ! $shouldAddDiscountIndex) {
            return;
        }

        Schema::table('campaign_discount', function (Blueprint $table) use (
            $shouldAddCampaignForeign,
            $shouldAddDiscountForeign,
            $shouldAddUnique,
            $shouldAddCampaignIndex,
            $shouldAddDiscountIndex
        ) {
            if ($shouldAddCampaignForeign) {
                $table->foreign('campaign_id')->references('id')->on('discount_campaigns')->cascadeOnDelete();
            }

            if ($shouldAddDiscountForeign) {
                $table->foreign('discount_id')->references('id')->on('discounts')->cascadeOnDelete();
            }

            if ($shouldAddUnique) {
                $table->unique(['campaign_id', 'discount_id']);
            }

            if ($shouldAddCampaignIndex) {
                $table->index(['campaign_id']);
            }

            if ($shouldAddDiscountIndex) {
                $table->index(['discount_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('campaign_discount')) {
            return;
        }

        $campaignForeign = 'campaign_discount_campaign_id_foreign';
        $discountForeign = 'campaign_discount_discount_id_foreign';
        $uniqueConstraint = 'campaign_discount_campaign_id_discount_id_unique';
        $campaignIndex = 'campaign_discount_campaign_id_index';
        $discountIndex = 'campaign_discount_discount_id_index';

        $shouldDropCampaignForeign = $this->hasForeignKey('campaign_discount', $campaignForeign);
        $shouldDropDiscountForeign = $this->hasForeignKey('campaign_discount', $discountForeign);
        $shouldDropUnique = $this->hasUniqueConstraint('campaign_discount', $uniqueConstraint);
        $shouldDropCampaignIndex = $this->hasIndex('campaign_discount', $campaignIndex);
        $shouldDropDiscountIndex = $this->hasIndex('campaign_discount', $discountIndex);

        if (! $shouldDropCampaignForeign && ! $shouldDropDiscountForeign && ! $shouldDropUnique && ! $shouldDropCampaignIndex && ! $shouldDropDiscountIndex) {
            return;
        }

        Schema::table('campaign_discount', function (Blueprint $table) use (
            $shouldDropCampaignForeign,
            $shouldDropDiscountForeign,
            $shouldDropUnique,
            $shouldDropCampaignIndex,
            $shouldDropDiscountIndex
        ) {
            if ($shouldDropCampaignForeign) {
                $table->dropForeign(['campaign_id']);
            }

            if ($shouldDropDiscountForeign) {
                $table->dropForeign(['discount_id']);
            }

            if ($shouldDropUnique) {
                $table->dropUnique(['campaign_id', 'discount_id']);
            }

            if ($shouldDropCampaignIndex) {
                $table->dropIndex(['campaign_id']);
            }

            if ($shouldDropDiscountIndex) {
                $table->dropIndex(['discount_id']);
            }
        });
    }

    private function hasForeignKey(string $table, string $foreignKeyName): bool
    {
        return $this->hasConstraint($table, $foreignKeyName, 'FOREIGN KEY');
    }

    private function hasUniqueConstraint(string $table, string $constraintName): bool
    {
        return $this->hasConstraint($table, $constraintName, 'UNIQUE');
    }

    private function hasConstraint(string $table, string $constraintName, string $type): bool
    {
        $connection = Schema::getConnection();

        // SQLite doesn't have information_schema, so we'll check using PRAGMA
        if ($connection->getDriverName() === 'sqlite') {
            try {
                $indexes = DB::select("PRAGMA index_list($table)");
                foreach ($indexes as $index) {
                    if ($index->name === $constraintName) {
                        return true;
                    }
                }

                return false;
            } catch (\Exception $e) {
                return false;
            }
        }

        $database = $connection->getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', $type)
            ->exists();
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();

        // SQLite doesn't have information_schema, so we'll check using PRAGMA
        if ($connection->getDriverName() === 'sqlite') {
            try {
                $indexes = DB::select("PRAGMA index_list($table)");
                foreach ($indexes as $index) {
                    if ($index->name === $indexName) {
                        return true;
                    }
                }

                return false;
            } catch (\Exception $e) {
                return false;
            }
        }

        $database = $connection->getDatabaseName();

        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};
