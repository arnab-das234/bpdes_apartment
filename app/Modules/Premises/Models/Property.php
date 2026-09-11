<?php

namespace App\Modules\Premises\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'properties';

    protected $fillable = [
        'name',
        'address_line1',
        'address_line2',
        'street',
        'locality',
        'ward',
        'municipality',
        'police_station',
        'district',
        'state',
        'pin',
        'plot_number',
        'dag_number',
        'khatian_number',
        'mouza',
        'jl_number',
        'total_land_area',
        'common_areas',
    ];

    protected $casts = [
        'common_areas' => 'array',
        'total_land_area' => 'decimal:2',
    ];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }
}
