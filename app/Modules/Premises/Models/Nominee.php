<?php

namespace App\Modules\Premises\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nominee extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'nominees';

    protected $fillable = [
        'membership_id',
        'name',
        'relationship',
        'dob',
        'mobile',
        'email',
        'address',
        'percentage_share',
        'guardian_name',
    ];

    protected $casts = [
        'dob' => 'date',
        'percentage_share' => 'decimal:2',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
