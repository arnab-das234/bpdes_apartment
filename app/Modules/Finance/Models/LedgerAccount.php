<?php

namespace App\Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    use HasUuid, BelongsToTenant;

    protected $table = 'ledger_accounts';

    protected $fillable = [
        'name',
        'type',
        'code',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(LedgerAccount::class, 'parent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
