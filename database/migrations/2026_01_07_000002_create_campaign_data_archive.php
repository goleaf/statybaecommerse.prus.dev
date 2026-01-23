<?php

declare(strict_types=1);

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
        // Create archive table for campaign data before removal
        Schema::create('campaign_data_archive', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->json('original_data');
            $table->timestamp('archived_at');
            $table->string('archive_reason')->default('feature_removal');
            $table->index(['table_name', 'archived_at']);
        });

        // Archive existing campaign data
        $this->archiveCampaignData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_data_archive');
    }

    /**
     * Archive campaign data before removal
     */
    private function archiveCampaignData(): void
    {
        $campaignTables = [
            'discount_campaigns',
            'email_campaigns',
            'referral_campaigns',
            'campaign_conversions',
            'campaign_views',
            'campaign_schedules',
            'campaign_translations',
            'campaign_categories',
            'campaign_products',
            'campaign_customer_groups',
            'campaign_customer_segments',
            'campaign_product_targets',
            'campaign_discount',
            'campaign_conversion_translations',
        ];

        foreach ($campaignTables as $table) {
            if (Schema::hasTable($table)) {
                $data = DB::table($table)->get();

                foreach ($data as $record) {
                    DB::table('campaign_data_archive')->insert([
                        'table_name'     => $table,
                        'original_data'  => json_encode($record),
                        'archived_at'    => now(),
                        'archive_reason' => 'feature_removal',
                    ]);
                }
            }
        }
    }
};
