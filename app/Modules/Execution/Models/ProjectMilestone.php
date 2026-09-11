<?php

namespace App\Modules\Execution\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    use BelongsToTenant, HasUuid;

    protected $table = 'project_milestones';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'organization_id',
        'project_id',
        'title',
        'description',
        'due_date',
        'status',
        'progress_percentage',
        'budget_allocation',
        'checklist',
    ];

    protected $casts = [
        'due_date' => 'date',
        'progress_percentage' => 'integer',
        'budget_allocation' => 'decimal:2',
        'checklist' => 'array',
    ];

    /**
     * Calculate progress percentage from weighted checklist items.
     */
    public function calculateProgressFromChecklist(): int
    {
        if (empty($this->checklist) || !is_array($this->checklist)) {
            return (int)($this->progress_percentage ?? 0);
        }

        $completedWeightage = 0;
        foreach ($this->checklist as $item) {
            if (!empty($item['completed'])) {
                $completedWeightage += (int)($item['weightage'] ?? 0);
            }
        }

        return min(100, max(0, $completedWeightage));
    }

    /**
     * Get the project that owns the milestone.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
