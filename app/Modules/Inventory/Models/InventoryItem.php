<?php

namespace App\Modules\Inventory\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasUuid;
use App\Modules\TenantIdentity\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $table = 'inventory_items';

    protected $fillable = [
        'organization_id',
        'name',
        'sku',
        'category',
        'unit',
        'min_stock_level',
        'stock_quantity',
        'reserved_quantity',
        'unit_cost',
        'allocated_budget',
        'storage_location',
        'remarks',
    ];

    protected $casts = [
        'min_stock_level' => 'integer',
        'stock_quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'allocated_budget' => 'decimal:2',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_item_id');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_quantity);
    }

    public function getTotalValuationAttribute(): float
    {
        return (float) ($this->stock_quantity * $this->unit_cost);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }
}
