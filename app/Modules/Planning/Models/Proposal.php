<?php

namespace App\Modules\Planning\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Models\User;
use App\Models\OutboxEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Proposal extends Model
{
    use BelongsToTenant, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'budget',
        'status',
        'checklist',
        'created_by',
        'execution_date',
        'deadline',
        'assigned_secretary_id',
        'document_path',
        'justification',
        'involved_members',
    ];

    protected $casts = [
        'checklist' => 'array',
        'budget' => 'decimal:2',
        'involved_members' => 'array',
        'execution_date' => 'date',
        'deadline' => 'date',
    ];

    // Status Constants
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_VERIFICATION = 'VERIFICATION';
    public const STATUS_VERIFIED = 'VERIFIED';
    public const STATUS_COMMITTEE_REVIEW = 'COMMITTEE_REVIEW';
    public const STATUS_RECOMMENDED = 'RECOMMENDED';
    public const STATUS_PRESIDENT_REVIEW = 'PRESIDENT_REVIEW';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_DEFERRED = 'DEFERRED';
    public const STATUS_RETURNED = 'RETURNED';

    /**
     * Define the valid transitions.
     */
    protected static array $validTransitions = [
        self::STATUS_DRAFT => [self::STATUS_SUBMITTED],
        self::STATUS_SUBMITTED => [self::STATUS_VERIFICATION],
        self::STATUS_VERIFICATION => [self::STATUS_VERIFIED, self::STATUS_RETURNED],
        self::STATUS_RETURNED => [self::STATUS_DRAFT, self::STATUS_SUBMITTED],
        self::STATUS_VERIFIED => [self::STATUS_COMMITTEE_REVIEW],
        self::STATUS_COMMITTEE_REVIEW => [self::STATUS_RECOMMENDED, self::STATUS_DEFERRED, self::STATUS_REJECTED],
        self::STATUS_RECOMMENDED => [self::STATUS_PRESIDENT_REVIEW],
        self::STATUS_PRESIDENT_REVIEW => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_DEFERRED],
    ];

    /**
     * Check if a transition to the target status is allowed.
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        // Allow super admin or specific overrides if needed
        $current = $this->status;
        if (!isset(self::$validTransitions[$current])) {
            return false;
        }

        return in_array($targetStatus, self::$validTransitions[$current]);
    }

    /**
     * Transition the proposal to a new state and record in outbox.
     */
    public function transitionTo(string $targetStatus): bool
    {
        if ($targetStatus !== $this->status && !$this->canTransitionTo($targetStatus)) {
            throw new \InvalidArgumentException("Invalid state transition from {$this->status} to {$targetStatus}");
        }

        $oldStatus = $this->status;
        $this->status = $targetStatus;
        $saved = $this->save();

        if ($saved) {
            // Write event to Transactional Outbox for asynchronous propagation (e.g. notifications)
            OutboxEvent::record('proposal.status_changed', [
                'proposal_id' => $this->id,
                'old_status' => $oldStatus,
                'new_status' => $targetStatus,
                'title' => $this->title,
            ], 'critical');
        }

        return $saved;
    }

    /**
     * Owner who created the proposal.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Decisions made on this proposal.
     */
    public function decisions(): HasMany
    {
        return $this->hasMany(ProposalDecision::class, 'proposal_id');
    }

    /**
     * Secretary assigned to run simulations/work on this proposal.
     */
    public function secretary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_secretary_id');
    }
}
