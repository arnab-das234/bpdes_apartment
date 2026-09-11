<?php

namespace App\Modules\Planning\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalDecision extends Model
{
    use BelongsToTenant, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'proposal_id',
        'decided_by',
        'decision',
        'remarks',
    ];

    /**
     * Get the associated proposal.
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    /**
     * User who made the decision.
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
