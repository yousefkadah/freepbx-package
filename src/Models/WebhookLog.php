<?php

namespace Yousef\FreePbx\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $table = 'freepbx_webhook_logs';

    protected $fillable = [
        'tenant_id',
        'url',
        'payload',
        'success',
        'status_code',
        'response',
        'duration_ms',
        'attempt',
    ];

    protected $casts = [
        'payload' => 'array',
        'success' => 'boolean',
        'status_code' => 'integer',
        'duration_ms' => 'float',
        'attempt' => 'integer',
    ];

    /**
     * Scope to filter by tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope for successful webhooks.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for failed webhooks.
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }
}
