<?php

namespace App\Modules\Authority\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Person;
use App\Modules\TenantIdentity\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeAppointment extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'committee_appointments';

    protected $fillable = [
        'person_id',
        'role_id',
        'designation',
        'start_date',
        'end_date',
        'appointment_method',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
