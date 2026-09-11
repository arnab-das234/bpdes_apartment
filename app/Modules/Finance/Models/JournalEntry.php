<?php

namespace App\Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Models\User;
use App\Modules\Execution\Models\Project;
use App\Modules\Planning\Models\Proposal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use BelongsToTenant, HasUuid;

    protected $table = 'finance_journal_entries';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'project_id',
        'proposal_id',
        'reference',
        'type', // DEBIT, CREDIT
        'amount',
        'account_name',
        'description',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the linked project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the linked proposal for audit/traceability.
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    /**
     * Get the user who recorded this entry.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
