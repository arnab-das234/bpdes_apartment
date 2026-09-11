<?php

namespace App\Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\TenantIdentity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $table = 'vendors';

    protected $fillable = [
        'organization_id',
        'name',
        'contact_person',
        'mobile',
        'email',
        'pan',
        'gstin',
        'service_category',
        'status',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
