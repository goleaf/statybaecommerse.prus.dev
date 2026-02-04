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
        $tableName = 'job_batches';
        $missingFailedJobs = ! Schema::hasColumn($tableName, 'failed_jobs');
        $missingFailedJobIds = ! Schema::hasColumn($tableName, 'failed_job_ids');

        if (! $missingFailedJobs && ! $missingFailedJobIds) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($missingFailedJobs, $missingFailedJobIds): void {
            if ($missingFailedJobs) {
                $table->integer('failed_jobs')->default(0);
            }

            if ($missingFailedJobIds) {
                $table->longText('failed_job_ids')->nullable();
            }
        });

        if ($missingFailedJobIds) {
            DB::table($tableName)
                ->whereNull('failed_job_ids')
                ->update(['failed_job_ids' => '[]']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = 'job_batches';
        $hasFailedJobs = Schema::hasColumn($tableName, 'failed_jobs');
        $hasFailedJobIds = Schema::hasColumn($tableName, 'failed_job_ids');

        if (! $hasFailedJobs && ! $hasFailedJobIds) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($hasFailedJobs, $hasFailedJobIds): void {
            if ($hasFailedJobIds) {
                $table->dropColumn('failed_job_ids');
            }

            if ($hasFailedJobs) {
                $table->dropColumn('failed_jobs');
            }
        });
    }
};
