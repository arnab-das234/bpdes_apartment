<?php

namespace App\Modules\Premises\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Finance\Models\MaintenanceEntry;
use App\Modules\Finance\Models\ElectricityBill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'units';

    protected $fillable = [
        'building_id',
        'flat_number',
        'floor',
        'unit_type',
        'carpet_area',
        'built_up_area',
        'super_built_up_area',
        'undivided_land_share',
        'parking_slots',
        'ownership_type',
        'occupancy_status',
        'maintenance_category',
        'monthly_maintenance_amount',
        'outstanding_amount',
        'electricity_connection_type',
        'meter_number',
        'submeter_number',
        'status',
    ];

    protected $casts = [
        'carpet_area' => 'decimal:2',
        'built_up_area' => 'decimal:2',
        'super_built_up_area' => 'decimal:2',
        'undivided_land_share' => 'decimal:2',
        'monthly_maintenance_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function maintenanceEntries(): HasMany
    {
        return $this->hasMany(MaintenanceEntry::class)->orderBy('billing_year', 'desc')->orderBy('billing_month', 'desc');
    }

    public function electricityBills(): HasMany
    {
        return $this->hasMany(ElectricityBill::class)->orderBy('created_at', 'desc');
    }

    public function getCalculatedTotalMaintenanceAttribute(): float
    {
        if ($this->relationLoaded('maintenanceEntries') && $this->maintenanceEntries->isNotEmpty()) {
            return (float)$this->maintenanceEntries->sum('total_due');
        }
        return (float)($this->monthly_maintenance_amount ?? 0);
    }

    public function getCalculatedTotalPaidAttribute(): float
    {
        if ($this->relationLoaded('maintenanceEntries') && $this->maintenanceEntries->isNotEmpty()) {
            return (float)$this->maintenanceEntries->sum('amount_paid');
        }
        return max((float)($this->monthly_maintenance_amount ?? 0) - (float)($this->outstanding_amount ?? 0), 0);
    }

    public function getCalculatedOutstandingAttribute(): float
    {
        if ($this->relationLoaded('maintenanceEntries') && $this->maintenanceEntries->isNotEmpty()) {
            return (float)$this->maintenanceEntries->sum(fn ($entry) => max($entry->total_due - $entry->amount_paid, 0));
        }
        return (float)($this->outstanding_amount ?? 0);
    }

    public function getCalculatedBacklogDuesAttribute(): float
    {
        if ($this->relationLoaded('maintenanceEntries') && $this->maintenanceEntries->isNotEmpty()) {
            return (float)$this->maintenanceEntries->where('is_backlog', true)->sum(fn ($entry) => max($entry->total_due - $entry->amount_paid, 0));
        }
        return 0;
    }
}
