<?php

namespace Yousef\FreePbx\Console\Commands;

use Illuminate\Console\Command;
use React\EventLoop\Loop;
use Yousef\FreePbx\Ami\AmiManager;

class StartAmiListenerCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'freepbx:ami-listen
                            {--tenant= : Tenant ID to listen for}';

    /**
     * The console command description.
     */
    protected $description = 'Start AMI event listener for real-time events';

    /**
     * Execute the console command.
     */
    public function handle(AmiManager $amiManager): int
    {
        $tenantId = $this->option('tenant');

        $this->info('Starting AMI listener...');

        if ($tenantId) {
            $this->info("Listening for tenant: {$tenantId}");
        }

        try {
            $connection = $amiManager->getConnection($tenantId);

            $this->info('AMI connection established');
            $this->info('Listening for events... (Press Ctrl+C to stop)');

            // Run the event loop
            Loop::run();
        } catch (\Exception $e) {
            $this->error("Failed to start AMI listener: {$e->getMessage()}");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
