<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PruneAuditLogsCommand extends Command
{
    protected $signature = 'privacy:prune-audit-logs';

    protected $description = 'Remove activity and audit log entries older than the configured retention period.';

    public function handle(): int
    {
        $retentionDays = (int) config('privacy.retention.audit', 365);

        if ($retentionDays <= 0) {
            $this->warn('Audit log retention is disabled; no records were pruned.');

            return self::SUCCESS;
        }

        $cutoff = Carbon::now()->subDays($retentionDays);

        $tables = ['activity_log', 'admin_activity_logs'];
        $totalDeleted = 0;

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $deleted = DB::table($table)
                ->whereNotNull('created_at')
                ->where('created_at', '<', $cutoff)
                ->delete();

            $totalDeleted += $deleted;

            if ($deleted > 0) {
                $this->info("Pruned {$deleted} records from {$table}.");
            }
        }

        if ($totalDeleted === 0) {
            $this->info('No audit log records were eligible for pruning.');
        } else {
            $this->info("Audit log pruning completed; {$totalDeleted} total records removed.");
        }

        return self::SUCCESS;
    }
}
