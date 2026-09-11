<?php

namespace App\Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Premises\Models\Unit;
use App\Modules\TenantIdentity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'transactions';

    protected $fillable = [
        'ledger_account_id',
        'project_id',
        'proposal_id',
        'unit_id',
        'voucher_number',
        'receipt_number',
        'transaction_date',
        'type',
        'amount',
        'payment_mode',
        'reference_number',
        'tax_amount',
        'description',
        'recorded_by',
        'approved_by',
        'verification_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
