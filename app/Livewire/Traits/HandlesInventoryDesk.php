<?php

namespace App\Livewire\Traits;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Organization;
use App\Models\Asset;
use App\Services\TenantManager;
use Illuminate\Support\Facades\DB;

trait HandlesInventoryDesk
{
    // Search & Filters
    public string $inventorySearch = '';
    public string $inventoryCategoryFilter = 'all';

    // Modal state
    public bool $showInventoryModal = false;
    public string $inventoryModalMode = 'create'; // create, edit, movement, reserve
    public ?string $editingItemId = null;

    // Item Form fields
    public string $invName = '';
    public string $invSku = '';
    public string $invCategory = 'Electrical'; // Electrical, Plumbing, Maintenance, Cleaning, Security, Hardware, Office
    public string $invUnit = 'pcs'; // pcs, kgs, liters, meters, rolls, boxes
    public int $invMinStock = 5;
    public float $invUnitCost = 0.00;
    public float $invAllocatedBudget = 0.00;
    public string $invStorageLocation = 'Main Store Room';
    public string $invRemarks = '';

    // Transaction / Movement Form fields
    public string $transType = 'Purchase'; // Purchase, Issue, Reserve, Adjustment
    public int $transQuantity = 1;
    public float $transUnitPrice = 0.00;
    public ?string $transProposalId = null;
    public ?string $transProjectId = null;
    public ?string $transMilestoneId = null;
    public string $transRemarks = '';

    public function openCreateInventoryItemModal(): void
    {
        $this->resetInventoryForm();
        $this->inventoryModalMode = 'create';
        $this->showInventoryModal = true;
    }

    public function openEditInventoryItemModal(string $itemId): void
    {
        $item = InventoryItem::find($itemId);
        if (!$item) return;

        $this->editingItemId = $item->id;
        $this->invName = $item->name;
        $this->invSku = $item->sku ?? '';
        $this->invCategory = $item->category ?? 'Electrical';
        $this->invUnit = $item->unit ?? 'pcs';
        $this->invMinStock = $item->min_stock_level;
        $this->invUnitCost = (float) $item->unit_cost;
        $this->invAllocatedBudget = (float) $item->allocated_budget;
        $this->invStorageLocation = $item->storage_location ?? 'Main Store Room';
        $this->invRemarks = $item->remarks ?? '';

        $this->inventoryModalMode = 'edit';
        $this->showInventoryModal = true;
    }

    public function openStockMovementModal(string $itemId, string $defaultType = 'Purchase'): void
    {
        $item = InventoryItem::find($itemId);
        if (!$item) return;

        $this->editingItemId = $item->id;
        $this->transType = $defaultType;
        $this->transQuantity = 1;
        $this->transUnitPrice = (float) $item->unit_cost;
        $this->transProposalId = null;
        $this->transProjectId = null;
        $this->transMilestoneId = null;
        $this->transRemarks = '';

        $this->inventoryModalMode = 'movement';
        $this->showInventoryModal = true;
    }

    public function closeInventoryModal(): void
    {
        $this->showInventoryModal = false;
        $this->resetInventoryForm();
    }

    public function resetInventoryForm(): void
    {
        $this->editingItemId = null;
        $this->invName = '';
        $this->invSku = '';
        $this->invCategory = 'Electrical';
        $this->invUnit = 'pcs';
        $this->invMinStock = 5;
        $this->invUnitCost = 0.00;
        $this->invAllocatedBudget = 0.00;
        $this->invStorageLocation = 'Main Store Room';
        $this->invRemarks = '';

        $this->transType = 'Purchase';
        $this->transQuantity = 1;
        $this->transUnitPrice = 0.00;
        $this->transProposalId = null;
        $this->transProjectId = null;
        $this->transMilestoneId = null;
        $this->transRemarks = '';
    }

    public function saveInventoryItem(): void
    {
        $this->validate([
            'invName' => 'required|string|max:255',
            'invCategory' => 'required|string',
            'invUnit' => 'required|string',
            'invMinStock' => 'required|integer|min:0',
            'invUnitCost' => 'required|numeric|min:0',
        ]);

        $orgId = app(TenantManager::class)->getTenantId() ?? Organization::first()?->id;

        if ($this->inventoryModalMode === 'edit' && $this->editingItemId) {
            $item = InventoryItem::find($this->editingItemId);
            if ($item) {
                $item->update([
                    'name' => $this->invName,
                    'sku' => $this->invSku ?: strtoupper(substr($this->invCategory, 0, 3) . '-' . rand(100, 999)),
                    'category' => $this->invCategory,
                    'unit' => $this->invUnit,
                    'min_stock_level' => $this->invMinStock,
                    'unit_cost' => $this->invUnitCost,
                    'allocated_budget' => $this->invAllocatedBudget,
                    'storage_location' => $this->invStorageLocation,
                    'remarks' => $this->invRemarks,
                ]);
                session()->flash('message', "Inventory item '{$item->name}' updated successfully.");
            }
        } else {
            $item = InventoryItem::create([
                'organization_id' => $orgId,
                'name' => $this->invName,
                'sku' => $this->invSku ?: strtoupper(substr($this->invCategory, 0, 3) . '-' . rand(100, 999)),
                'category' => $this->invCategory,
                'unit' => $this->invUnit,
                'min_stock_level' => $this->invMinStock,
                'stock_quantity' => 0,
                'reserved_quantity' => 0,
                'unit_cost' => $this->invUnitCost,
                'allocated_budget' => $this->invAllocatedBudget,
                'storage_location' => $this->invStorageLocation,
                'remarks' => $this->invRemarks,
            ]);
            session()->flash('message', "New Inventory Item '{$item->name}' created successfully.");
        }

        $this->closeInventoryModal();
    }

    public function saveStockMovement(): void
    {
        $this->validate([
            'transType' => 'required|in:Purchase,Issue,Reserve,Adjustment',
            'transQuantity' => 'required|integer|min:1',
            'transUnitPrice' => 'required|numeric|min:0',
        ]);

        if (!$this->editingItemId) return;
        $item = InventoryItem::find($this->editingItemId);
        if (!$item) return;

        $orgId = app(TenantManager::class)->getTenantId() ?? Organization::first()?->id;

        DB::transaction(function () use ($item, $orgId) {
            if ($this->transType === 'Purchase') {
                $item->stock_quantity += $this->transQuantity;
                if ($this->transUnitPrice > 0) {
                    $item->unit_cost = $this->transUnitPrice;
                }
                $item->save();

                InventoryTransaction::create([
                    'organization_id' => $orgId,
                    'inventory_item_id' => $item->id,
                    'type' => 'Purchase',
                    'quantity' => $this->transQuantity,
                    'unit_price' => $this->transUnitPrice,
                    'proposal_id' => $this->transProposalId ?: null,
                    'project_id' => $this->transProjectId ?: null,
                    'milestone_id' => $this->transMilestoneId ?: null,
                    'recorded_by' => auth()->id(),
                    'remarks' => $this->transRemarks ?: 'Purchased & Received Stock IN',
                ]);

                session()->flash('message', "Recorded Stock IN (+{$this->transQuantity} {$item->unit}) for '{$item->name}'.");
            } elseif ($this->transType === 'Issue') {
                if ($item->stock_quantity < $this->transQuantity) {
                    $this->addError('transQuantity', "Cannot issue {$this->transQuantity} {$item->unit}. Current stock is only {$item->stock_quantity} {$item->unit}.");
                    return;
                }
                $item->stock_quantity -= $this->transQuantity;
                $item->save();

                InventoryTransaction::create([
                    'organization_id' => $orgId,
                    'inventory_item_id' => $item->id,
                    'type' => 'Issue',
                    'quantity' => $this->transQuantity,
                    'unit_price' => $item->unit_cost,
                    'proposal_id' => $this->transProposalId ?: null,
                    'project_id' => $this->transProjectId ?: null,
                    'milestone_id' => $this->transMilestoneId ?: null,
                    'recorded_by' => auth()->id(),
                    'remarks' => $this->transRemarks ?: 'Issued for Building Maintenance / Project',
                ]);

                session()->flash('message', "Recorded Stock OUT (-{$this->transQuantity} {$item->unit}) for '{$item->name}'.");
            } elseif ($this->transType === 'Reserve') {
                $item->reserved_quantity += $this->transQuantity;
                $item->allocated_budget += ($this->transQuantity * $this->transUnitPrice);
                $item->save();

                InventoryTransaction::create([
                    'organization_id' => $orgId,
                    'inventory_item_id' => $item->id,
                    'type' => 'Reserve',
                    'quantity' => $this->transQuantity,
                    'unit_price' => $this->transUnitPrice,
                    'proposal_id' => $this->transProposalId ?: null,
                    'project_id' => $this->transProjectId ?: null,
                    'milestone_id' => $this->transMilestoneId ?: null,
                    'recorded_by' => auth()->id(),
                    'remarks' => $this->transRemarks ?: 'Reserved for upcoming project purchase with dedicated budget',
                ]);

                session()->flash('message', "Reserved {$this->transQuantity} {$item->unit} of '{$item->name}' with dedicated budget.");
            } elseif ($this->transType === 'Adjustment') {
                $diff = $this->transQuantity - $item->stock_quantity;
                $item->stock_quantity = $this->transQuantity;
                $item->save();

                InventoryTransaction::create([
                    'organization_id' => $orgId,
                    'inventory_item_id' => $item->id,
                    'type' => 'Adjustment',
                    'quantity' => abs($diff),
                    'unit_price' => $item->unit_cost,
                    'recorded_by' => auth()->id(),
                    'remarks' => $this->transRemarks ?: "Stock level audit adjustment to {$this->transQuantity} {$item->unit}",
                ]);

                session()->flash('message', "Adjusted stock level for '{$item->name}' to {$this->transQuantity} {$item->unit}.");
            }
        });

        $this->closeInventoryModal();
    }

    public function ensureSampleInventorySeeded(): void
    {
        $orgId = app(TenantManager::class)->getTenantId() ?? Organization::first()?->id;
        if (!$orgId) return;

        if (InventoryItem::where('organization_id', $orgId)->count() === 0) {
            $samples = [
                [
                    'name' => 'LED Floodlight 50W Outdoor',
                    'sku' => 'ELE-FLD-50W',
                    'category' => 'Electrical',
                    'unit' => 'pcs',
                    'min_stock_level' => 5,
                    'stock_quantity' => 12,
                    'reserved_quantity' => 2,
                    'unit_cost' => 1450.00,
                    'allocated_budget' => 2900.00,
                    'storage_location' => 'Electric Control Room',
                ],
                [
                    'name' => 'CPVC Pipe 1.5 Inch (10ft)',
                    'sku' => 'PLM-PIP-15',
                    'category' => 'Plumbing',
                    'unit' => 'pcs',
                    'min_stock_level' => 10,
                    'stock_quantity' => 24,
                    'reserved_quantity' => 5,
                    'unit_cost' => 380.00,
                    'allocated_budget' => 1900.00,
                    'storage_location' => 'Plumbing Basement Depot',
                ],
                [
                    'name' => 'Submersible Water Pump Motor Seal (3HP)',
                    'sku' => 'PLM-MOT-SEAL',
                    'category' => 'Plumbing',
                    'unit' => 'pcs',
                    'min_stock_level' => 2,
                    'stock_quantity' => 1, // LOW STOCK
                    'reserved_quantity' => 0,
                    'unit_cost' => 2800.00,
                    'allocated_budget' => 0.00,
                    'storage_location' => 'Main Pump Room',
                ],
                [
                    'name' => 'Floor Sanitizer & Cleaner (5L)',
                    'sku' => 'CLN-SAN-05L',
                    'category' => 'Cleaning',
                    'unit' => 'cans',
                    'min_stock_level' => 4,
                    'stock_quantity' => 8,
                    'reserved_quantity' => 0,
                    'unit_cost' => 650.00,
                    'allocated_budget' => 0.00,
                    'storage_location' => 'Janitor Store',
                ],
                [
                    'name' => 'CCTV Security Camera Power Supply 12V',
                    'sku' => 'SEC-PWR-12V',
                    'category' => 'Security',
                    'unit' => 'pcs',
                    'min_stock_level' => 3,
                    'stock_quantity' => 2, // LOW STOCK
                    'reserved_quantity' => 1,
                    'unit_cost' => 850.00,
                    'allocated_budget' => 850.00,
                    'storage_location' => 'Guard Room Rack',
                ],
            ];

            foreach ($samples as $sample) {
                $item = InventoryItem::create(array_merge($sample, ['organization_id' => $orgId]));

                // Create initial transaction log
                InventoryTransaction::create([
                    'organization_id' => $orgId,
                    'inventory_item_id' => $item->id,
                    'type' => 'Purchase',
                    'quantity' => $sample['stock_quantity'],
                    'unit_price' => $sample['unit_cost'],
                    'remarks' => 'Initial inventory opening balance',
                ]);
            }
        }

        if (Asset::where('organization_id', $orgId)->count() === 0) {
            $assets = [
                [
                    'name' => 'Kirloskar 125 KVA Silent Diesel Generator Set',
                    'category' => 'Power Backup',
                    'location' => 'Basement Power Room',
                    'purchase_date' => '2022-04-15',
                    'purchase_amount' => 680000.00,
                    'current_value' => 540000.00,
                    'status' => 'active',
                    'responsible_person' => 'Electrician Ramu',
                ],
                [
                    'name' => 'Schindler 8-Passenger Automatic Elevator (Block A)',
                    'category' => 'Elevators',
                    'location' => 'Block A Shaft',
                    'purchase_date' => '2021-11-10',
                    'purchase_amount' => 1450000.00,
                    'current_value' => 1160000.00,
                    'status' => 'active',
                    'responsible_person' => 'Schindler AMC Team',
                ],
                [
                    'name' => 'Crompton Greaves 7.5 HP Submersible Water Pump Set',
                    'category' => 'Water Supply',
                    'location' => 'Borewell No. 1',
                    'purchase_date' => '2023-01-20',
                    'purchase_amount' => 85000.00,
                    'current_value' => 72000.00,
                    'status' => 'active',
                    'responsible_person' => 'Plumber Sankar',
                ],
            ];

            foreach ($assets as $asset) {
                Asset::create(array_merge($asset, ['organization_id' => $orgId]));
            }
        }
    }
}
