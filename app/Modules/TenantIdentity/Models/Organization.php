<?php

namespace App\Modules\TenantIdentity\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasUuid;

    protected $table = 'organizations';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'short_name',
        'subdomain',
        'registration_type',
        'registration_number',
        'registration_date',
        'registration_authority',
        'registration_act',
        'pan',
        'tan',
        'gstin',
        'official_email',
        'official_mobile',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'registration_date' => 'date',
    ];

    /**
     * Get users belonging to this organization.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
