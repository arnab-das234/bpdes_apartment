<?php

namespace App\Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Unit;
use App\Modules\Premises\Models\Person;
use App\Modules\TenantIdentity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceEntry extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'maintenance_entries';

    protected $fillable = [
        'organization_id',
        'unit_id',
        'person_id',
        'billing_year',
        'billing_month',
        'month_name',
        'title',
        'is_backlog',
        'base_maintenance',
        'backlog_amount',
        'late_fee',
        'utility_charge',
        'total_due',
        'amount_paid',
        'status',
        'due_date',
        'paid_at',
        'payment_mode',
        'reference_number',
        'remarks',
        'recorded_by',
    ];

    protected $casts = [
        'billing_year' => 'integer',
        'billing_month' => 'integer',
        'is_backlog' => 'boolean',
        'base_maintenance' => 'decimal:2',
        'backlog_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'utility_charge' => 'decimal:2',
        'total_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getOutstandingAttribute(): float
    {
        return max((float)$this->total_due - (float)$this->amount_paid, 0);
    }
}
