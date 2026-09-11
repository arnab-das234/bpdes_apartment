<?php

namespace App\Modules\Execution\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\Planning\Models\Proposal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToTenant, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'proposal_id',
        'title',
        'description',
        'budget',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the source proposal.
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    /**
     * Get tasks linked to this project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    /**
     * Get milestones linked to this project.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id')->orderBy('due_date', 'asc');
    }
}
