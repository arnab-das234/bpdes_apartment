<?php

namespace App\Modules\Inventory\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\TenantIdentity\Models\Organization;
use App\Modules\TenantIdentity\Models\User;
use App\Modules\Planning\Models\Proposal;
use App\Modules\Execution\Models\Project;
use App\Modules\Execution\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'organization_id',
        'inventory_item_id',
        'type', // Purchase, Issue, Return, Reserve, Adjustment
        'quantity',
        'unit_price',
        'reference_id',
        'proposal_id',
        'project_id',
        'milestone_id',
        'recorded_by',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function milestone()
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getTotalCostAttribute(): float
    {
        return (float) ($this->quantity * $this->unit_price);
    }
}
