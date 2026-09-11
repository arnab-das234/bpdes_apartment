<?php

namespace App\Modules\Premises\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Authority\Models\CommitteeAppointment;
use App\Modules\TenantIdentity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'persons';

    protected $fillable = [
        'organization_id',
        'name',
        'guardian_name',
        'dob',
        'gender',
        'family_members',
        'is_professional',
        'car_parking',
        'mobile',
        'email',
        'address',
        'id_type',
        'id_number',
        'photo_path',
        'signature_path',
        'occupation',
        'pan',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function committeeAppointment(): HasOne
    {
        return $this->hasOne(CommitteeAppointment::class)->where('status', 'active');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
