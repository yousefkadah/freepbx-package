<?php

namespace yousefkadah\FreePbx\Tenancy;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;

class TenantConfigRepository
{
    /**
     * Encrypt sensitive configuration values.
     */
    public function encrypt(array $config): array
    {
        if (!config('freepbx.multi_tenant.encrypt_credentials')) {
            return $config;
        }

        $sensitiveKeys = [
            'api.password',
            'ami.secret',
            'webhooks.signing.secret',
            'cdr.connection.password',
        ];

        foreach ($sensitiveKeys as $key) {
            if ($value = data_get($config, $key)) {
                data_set($config, $key, Crypt::encryptString($value));
            }
        }

        return $config;
    }

    /**
     * Decrypt sensitive configuration values.
     */
    public function decrypt(array $config): array
    {
        if (!config('freepbx.multi_tenant.encrypt_credentials')) {
            return $config;
        }

        $sensitiveKeys = [
            'api.password',
            'ami.secret',
            'webhooks.signing.secret',
            'cdr.connection.password',
        ];

        foreach ($sensitiveKeys as $key) {
            if ($value = data_get($config, $key)) {
                try {
                    data_set($config, $key, Crypt::decryptString($value));
                } catch (\Exception $e) {
                    // Value might not be encrypted, leave as is
                }
            }
        }

        return $config;
    }
}
