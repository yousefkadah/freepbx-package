<?php

namespace yousefkadah\FreePbx\Sync;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yousefkadah\FreePbx\Models\CallDetailRecord;
use yousefkadah\FreePbx\Tenancy\TenantManager;

class CdrSyncService
{
    protected TenantManager $tenantManager;

    public function __construct(TenantManager $tenantManager)
    {
        $this->tenantManager = $tenantManager;
    }

    /**
     * Sync CDR records from FreePBX database.
     */
    public function sync(?string $tenantId = null, ?string $since = null): int
    {
        if (!config('freepbx.cdr.enabled', true)) {
            return 0;
        }

        // Set tenant context if provided
        if ($tenantId) {
            $this->tenantManager->setTenant($tenantId);
        }

        $connection = $this->getCdrConnection();
        $batchSize = config('freepbx.cdr.sync.batch_size', 1000);
        $syncedCount = 0;

        // Determine starting point
        if (!$since && config('freepbx.cdr.sync.incremental', true)) {
            $since = $this->getLastSyncTime($tenantId);
        }

        $query = $connection->table('cdr');

        if ($since) {
            $query->where('calldate', '>', $since);
        }

        $query->orderBy('calldate')
            ->chunk($batchSize, function ($records) use ($tenantId, &$syncedCount) {
                foreach ($records as $record) {
                    $this->syncRecord($record, $tenantId);
                    $syncedCount++;
                }
            });

        Log::info('CDR Sync Completed', [
            'tenant' => $tenantId,
            'synced' => $syncedCount,
        ]);

        return $syncedCount;
    }

    /**
     * Sync a single CDR record.
     */
    protected function syncRecord(object $record, ?string $tenantId): void
    {
        $data = [
            'tenant_id' => $tenantId,
            'call_date' => $record->calldate,
            'clid' => $record->clid ?? null,
            'src' => $record->src,
            'dst' => $record->dst,
            'dcontext' => $record->dcontext ?? null,
            'channel' => $record->channel ?? null,
            'dstchannel' => $record->dstchannel ?? null,
            'lastapp' => $record->lastapp ?? null,
            'lastdata' => $record->lastdata ?? null,
            'duration' => $record->duration ?? 0,
            'billsec' => $record->billsec ?? 0,
            'disposition' => $record->disposition,
            'amaflags' => $record->amaflags ?? 0,
            'accountcode' => $record->accountcode ?? null,
            'uniqueid' => $record->uniqueid,
            'userfield' => $record->userfield ?? null,
        ];

        // Link to CRM if enabled
        if (config('freepbx.cdr.crm_linking.enabled', true)) {
            $data['contact_id'] = $this->findContactId($record->src);
            $data['user_id'] = $this->findUserId($record->dst);
        }

        CallDetailRecord::updateOrCreate(
            ['uniqueid' => $record->uniqueid],
            $data
        );
    }

    /**
     * Find contact ID by phone number.
     */
    protected function findContactId(string $phoneNumber): ?int
    {
        $contactModel = config('freepbx.cdr.crm_linking.contact_model');
        
        if (!$contactModel || !class_exists($contactModel)) {
            return null;
        }

        $phoneField = config('freepbx.call_popup.contact_lookup.phone_field', 'phone');
        $contact = $contactModel::where($phoneField, $phoneNumber)->first();

        return $contact?->id;
    }

    /**
     * Find user ID by extension.
     */
    protected function findUserId(string $extension): ?int
    {
        $userModel = config('freepbx.cdr.crm_linking.user_model');
        
        if (!$userModel || !class_exists($userModel)) {
            return null;
        }

        // Assuming users have an 'extension' field
        $user = $userModel::where('extension', $extension)->first();

        return $user?->id;
    }

    /**
     * Get the last sync time for incremental sync.
     */
    protected function getLastSyncTime(?string $tenantId): ?string
    {
        $query = CallDetailRecord::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $lastRecord = $query->orderBy('call_date', 'desc')->first();

        return $lastRecord?->call_date?->format('Y-m-d H:i:s');
    }

    /**
     * Get CDR database connection.
     */
    protected function getCdrConnection()
    {
        $config = [
            'driver' => 'mysql',
            'host' => $this->tenantManager->getConfig('cdr.connection.host'),
            'port' => $this->tenantManager->getConfig('cdr.connection.port', 3306),
            'database' => $this->tenantManager->getConfig('cdr.connection.database'),
            'username' => $this->tenantManager->getConfig('cdr.connection.username'),
            'password' => $this->tenantManager->getConfig('cdr.connection.password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ];

        return DB::connection()->setPdo(
            new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password']
            )
        );
    }
}
