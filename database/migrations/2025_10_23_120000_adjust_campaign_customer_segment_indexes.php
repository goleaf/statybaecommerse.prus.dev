<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campaign_customer_segments')) {
            return;
        }

        Schema::table('campaign_customer_segments', function (Blueprint $table): void {
            // Allow multiple segments per campaign and customer group by removing the legacy unique constraint.
            $table->dropUnique('campaign_customer_segments_campaign_id_customer_group_id_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('campaign_customer_segments')) {
            return;
        }

        Schema::table('campaign_customer_segments', function (Blueprint $table): void {
            // Revert to the stricter constraint that only allowed one segment per campaign/customer group pair.
            $table->unique(
                ['campaign_id', 'customer_group_id'],
                'campaign_customer_segments_campaign_id_customer_group_id_unique'
            );
        });
    }
};
