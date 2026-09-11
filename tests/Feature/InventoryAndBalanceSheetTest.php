<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Asset;
use App\Livewire\ControlCenter;
use App\Livewire\PresidentInbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryAndBalanceSheetTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'name' => 'Royal Palm Co-Operative Society',
            'short_name' => 'Royal Palm',
            'subdomain' => 'royalpalm',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'email' => 'treasurer@society.com',
            'role' => 'treasurer',
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_create_new_inventory_item(): void
    {
        Livewire::test(ControlCenter::class)
            ->call('openCreateInventoryItemModal')
            ->set('invName', 'LED Streetlight 100W')
            ->set('invSku', 'ELE-STR-100W')
            ->set('invCategory', 'Electrical')
            ->set('invUnit', 'pcs')
            ->set('invMinStock', 3)
            ->set('invUnitCost', 2200.00)
            ->set('invStorageLocation', 'Main Electrical Room')
            ->call('saveInventoryItem');

        $this->assertDatabaseHas('inventory_items', [
            'name' => 'LED Streetlight 100W',
            'sku' => 'ELE-STR-100W',
            'category' => 'Electrical',
            'min_stock_level' => 3,
            'unit_cost' => 2200.00,
        ]);
    }

    public function test_can_record_stock_purchase_and_issue(): void
    {
        $item = InventoryItem::create([
            'organization_id' => $this->org->id,
            'name' => 'CPVC Pipe 2 Inch',
            'sku' => 'PLM-PIP-20',
            'category' => 'Plumbing',
            'unit' => 'pcs',
            'min_stock_level' => 5,
            'stock_quantity' => 10,
            'unit_cost' => 450.00,
        ]);

        // 1. Stock IN (Purchase +10)
        Livewire::test(ControlCenter::class)
            ->call('openStockMovementModal', $item->id, 'Purchase')
            ->set('transQuantity', 10)
            ->set('transUnitPrice', 450.00)
            ->set('transRemarks', 'Purchased from Hardware Store')
            ->call('saveStockMovement');

        $item->refresh();
        $this->assertEquals(20, $item->stock_quantity);

        // 2. Stock OUT (Issue -4)
        Livewire::test(ControlCenter::class)
            ->call('openStockMovementModal', $item->id, 'Issue')
            ->set('transQuantity', 4)
            ->set('transRemarks', 'Issued for Block B Pipe Leakage')
            ->call('saveStockMovement');

        $item->refresh();
        $this->assertEquals(16, $item->stock_quantity);

        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id,
            'type' => 'Issue',
            'quantity' => 4,
        ]);
    }

    public function test_can_reserve_stock_with_dedicated_budget(): void
    {
        $item = InventoryItem::create([
            'organization_id' => $this->org->id,
            'name' => 'Solar Inverter 5KW',
            'sku' => 'SOL-INV-5KW',
            'category' => 'Electrical',
            'unit' => 'pcs',
            'min_stock_level' => 1,
            'stock_quantity' => 2,
            'reserved_quantity' => 0,
            'unit_cost' => 45000.00,
            'allocated_budget' => 0.00,
        ]);

        Livewire::test(PresidentInbox::class)
            ->call('openStockMovementModal', $item->id, 'Reserve')
            ->set('transQuantity', 1)
            ->set('transUnitPrice', 45000.00)
            ->set('transRemarks', 'Reserved for Rooftop Solar Project')
            ->call('saveStockMovement');

        $item->refresh();
        $this->assertEquals(1, $item->reserved_quantity);
        $this->assertEquals(45000.00, $item->allocated_budget);
    }

    public function test_balance_sheet_financial_statement_is_balanced(): void
    {
        $component = Livewire::test(ControlCenter::class);
        $data = $component->instance()->getBalanceSheetData();

        $this->assertIsArray($data);
        $this->assertTrue($data['is_balanced']);
        $this->assertEquals(
            $data['assets']['total_assets'],
            $data['liabilities_and_equity']['total_liabilities_and_equity']
        );
    }
}
