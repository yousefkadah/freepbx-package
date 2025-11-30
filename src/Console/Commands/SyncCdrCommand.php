<?php

namespace yousefkadah\FreePbx\Console\Commands;

use Illuminate\Console\Command;
use yousefkadah\FreePbx\Sync\CdrSyncService;

class SyncCdrCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'freepbx:sync-cdr
                            {--tenant= : Tenant ID to sync for}
                            {--since= : Sync records since this date (Y-m-d H:i:s)}
                            {--all : Sync all records, not just incremental}';

    /**
     * The console command description.
     */
    protected $description = 'Sync Call Detail Records from FreePBX';

    /**
     * Execute the console command.
     */
    public function handle(CdrSyncService $syncService): int
    {
        $tenantId = $this->option('tenant');
        $since = $this->option('since');
        
        if ($this->option('all')) {
            $since = null;
        }

        $this->info('Starting CDR sync...');

        if ($tenantId) {
            $this->info("Syncing for tenant: {$tenantId}");
        }

        if ($since) {
            $this->info("Syncing records since: {$since}");
        }

        $startTime = microtime(true);
        $count = $syncService->sync($tenantId, $since);
        $duration = round(microtime(true) - $startTime, 2);

        $this->info("Synced {$count} CDR records in {$duration} seconds");

        return Command::SUCCESS;
    }
}
