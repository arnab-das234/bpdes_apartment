<?php

namespace App\Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\TenantIdentity\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'organization_id',
        'bank_name',
        'branch',
        'account_name',
        'account_number',
        'ifsc',
        'account_type',
        'opening_date',
        'purpose',
        'cheque_signing_rules',
        'documents_json',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'documents_json' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
