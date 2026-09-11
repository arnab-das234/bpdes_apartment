<?php

namespace App\Modules\Premises\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membership extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'memberships';

    protected $fillable = [
        'unit_id',
        'person_id',
        'membership_number',
        'primary_owner',
        'co_owner',
        'membership_date',
        'membership_status',
        'voting_rights',
    ];

    protected $casts = [
        'primary_owner' => 'boolean',
        'co_owner' => 'boolean',
        'voting_rights' => 'boolean',
        'membership_date' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function nominees(): HasMany
    {
        return $this->hasMany(Nominee::class);
    }
}
