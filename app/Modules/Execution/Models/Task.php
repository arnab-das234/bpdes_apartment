<?php

namespace App\Modules\Execution\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Models\User;
use App\Models\OutboxEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use BelongsToTenant, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'project_id',
        'title',
        'description',
        'assigned_to',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // Status Constants
    public const STATUS_NOT_STARTED = 'Not Started';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_ACCEPTED = 'Accepted';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_SUBMITTED = 'Submitted';
    public const STATUS_UNDER_VERIFICATION = 'Under Verification';
    public const STATUS_CORRECTION_REQUIRED = 'Correction Required';
    public const STATUS_VERIFIED = 'Verified';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CLOSED = 'Closed';

    /**
     * Define the valid transitions for tasks.
     */
    protected static array $validTransitions = [
        self::STATUS_NOT_STARTED => [self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS],
        self::STATUS_ASSIGNED => [self::STATUS_ACCEPTED, self::STATUS_NOT_STARTED],
        self::STATUS_ACCEPTED => [self::STATUS_IN_PROGRESS],
        self::STATUS_IN_PROGRESS => [self::STATUS_SUBMITTED],
        self::STATUS_SUBMITTED => [self::STATUS_UNDER_VERIFICATION],
        self::STATUS_UNDER_VERIFICATION => [self::STATUS_VERIFIED, self::STATUS_CORRECTION_REQUIRED],
        self::STATUS_CORRECTION_REQUIRED => [self::STATUS_IN_PROGRESS],
        self::STATUS_VERIFIED => [self::STATUS_COMPLETED],
        self::STATUS_COMPLETED => [self::STATUS_CLOSED],
        self::STATUS_CLOSED => [], // End state
    ];

    /**
     * Check if a state transition is allowed.
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        $current = $this->status;
        if (!isset(self::$validTransitions[$current])) {
            return false;
        }

        return in_array($targetStatus, self::$validTransitions[$current]);
    }

    /**
     * Transition the task and log in transactional outbox.
     */
    public function transitionTo(string $targetStatus): bool
    {
        if ($targetStatus !== $this->status && !$this->canTransitionTo($targetStatus)) {
            throw new \InvalidArgumentException("Invalid task transition from '{$this->status}' to '{$targetStatus}'");
        }

        $oldStatus = $this->status;
        $this->status = $targetStatus;
        $saved = $this->save();

        if ($saved) {
            OutboxEvent::record('task.status_changed', [
                'task_id' => $this->id,
                'project_id' => $this->project_id,
                'old_status' => $oldStatus,
                'new_status' => $targetStatus,
                'title' => $this->title,
            ], 'default');
        }

        return $saved;
    }

    /**
     * Get the associated project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the user assigned to this task.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
