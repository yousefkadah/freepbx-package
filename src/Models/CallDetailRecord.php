<?php

namespace yousefkadah\FreePbx\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CallDetailRecord extends Model
{
    protected $table = 'freepbx_cdr';

    protected $fillable = [
        'tenant_id',
        'call_date',
        'clid',
        'src',
        'dst',
        'dcontext',
        'channel',
        'dstchannel',
        'lastapp',
        'lastdata',
        'duration',
        'billsec',
        'disposition',
        'amaflags',
        'accountcode',
        'uniqueid',
        'userfield',
        'contact_id',
        'user_id',
    ];

    protected $casts = [
        'call_date' => 'datetime',
        'duration' => 'integer',
        'billsec' => 'integer',
        'amaflags' => 'integer',
    ];

    /**
     * Scope to filter by tenant.
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('call_date', [$startDate, $endDate]);
    }

    /**
     * Scope for answered calls.
     */
    public function scopeAnswered(Builder $query): Builder
    {
        return $query->where('disposition', 'ANSWERED');
    }

    /**
     * Scope for missed calls.
     */
    public function scopeMissed(Builder $query): Builder
    {
        return $query->whereIn('disposition', ['NO ANSWER', 'BUSY', 'FAILED']);
    }

    /**
     * Scope for outbound calls.
     */
    public function scopeOutbound(Builder $query): Builder
    {
        return $query->where('dcontext', 'like', '%from-internal%');
    }

    /**
     * Scope for inbound calls.
     */
    public function scopeInbound(Builder $query): Builder
    {
        return $query->where('dcontext', 'like', '%from-trunk%')
            ->orWhere('dcontext', 'like', '%from-did%');
    }

    /**
     * Get the contact associated with this call.
     */
    public function contact()
    {
        $contactModel = config('freepbx.cdr.crm_linking.contact_model');
        
        if (!$contactModel || !class_exists($contactModel)) {
            return null;
        }

        return $this->belongsTo($contactModel, 'contact_id');
    }

    /**
     * Get the user associated with this call.
     */
    public function user()
    {
        $userModel = config('freepbx.cdr.crm_linking.user_model');
        
        if (!$userModel || !class_exists($userModel)) {
            return null;
        }

        return $this->belongsTo($userModel, 'user_id');
    }
}
