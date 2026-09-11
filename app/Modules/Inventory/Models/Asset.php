<?php

namespace App\Modules\Inventory\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\TenantIdentity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $table = 'assets';

    protected $fillable = [
        'organization_id',
        'name',
        'category',
        'location',
        'purchase_date',
        'purchase_amount',
        'warranty_expiry',
        'amc_details',
        'depreciation_method',
        'current_value',
        'status',
        'responsible_person',
    ];

    protected $casts = [
        'purchase_amount' => 'decimal:2',
        'current_value' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
