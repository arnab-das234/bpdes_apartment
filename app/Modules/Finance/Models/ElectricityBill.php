<?php

namespace App\Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectricityBill extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'electricity_bills';

    protected $fillable = [
        'organization_id',
        'unit_id',
        'meter_number',
        'submeter_number',
        'billing_month',
        'amount',
        'previous_reading',
        'current_reading',
        'units_consumed',
        'common_meter_total_units',
        'energy_charge',
        'electricity_duty',
        'fixed_charge',
        'meter_rent',
        'lpsc_exclusion',
        'gross_bill_amount',
        'recommended_rate_per_unit',
        'personal_charge',
        'common_share',
        'total_payable',
        'total_flats_count',
        'billing_cycle_months',
        'receipt_path',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'energy_charge' => 'decimal:2',
        'electricity_duty' => 'decimal:2',
        'fixed_charge' => 'decimal:2',
        'meter_rent' => 'decimal:2',
        'lpsc_exclusion' => 'decimal:2',
        'gross_bill_amount' => 'decimal:2',
        'recommended_rate_per_unit' => 'decimal:4',
        'personal_charge' => 'decimal:2',
        'common_share' => 'decimal:2',
        'total_payable' => 'decimal:2',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
