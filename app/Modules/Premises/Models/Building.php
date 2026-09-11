<?php

namespace App\Modules\Premises\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'buildings';

    protected $fillable = [
        'property_id',
        'name',
        'floors',
        'units_count',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
