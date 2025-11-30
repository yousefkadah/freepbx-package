<?php

namespace Yousef\FreePbx\Models;

use Illuminate\Database\Eloquent\Model;

class TenantConfig extends Model
{
    protected $table = 'freepbx_tenant_configs';

    protected $fillable = [
        'tenant_id',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    /**
     * Get decrypted configuration.
     */
    public function getConfigAttribute($value): array
    {
        $config = json_decode($value, true) ?? [];
        
        if (config('freepbx.multi_tenant.encrypt_credentials')) {
            $repository = new \Yousef\FreePbx\Tenancy\TenantConfigRepository();
            return $repository->decrypt($config);
        }

        return $config;
    }

    /**
     * Set encrypted configuration.
     */
    public function setConfigAttribute($value): void
    {
        if (config('freepbx.multi_tenant.encrypt_credentials')) {
            $repository = new \Yousef\FreePbx\Tenancy\TenantConfigRepository();
            $value = $repository->encrypt($value);
        }

        $this->attributes['config'] = json_encode($value);
    }
}
