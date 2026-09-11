<?php

namespace Tests\Feature;

use App\Modules\TenantIdentity\Models\Organization;
use App\Modules\TenantIdentity\Models\User;
use App\Modules\TenantIdentity\Models\OutboxEvent;
use App\Modules\Finance\Models\BuildingCashBill;
use App\Modules\Finance\Models\MaintenanceEntry;
use App\Modules\Premises\Models\Property;
use App\Modules\Premises\Models\Building;
use App\Modules\Premises\Models\Unit;
use App\Services\TenantManager;
use App\Livewire\ControlCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Tests\TestCase;

class AdvancedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'name' => 'Royal Palm Estate',
            'subdomain' => 'royalpalm',
            'status' => 'active',
        ]);

        $tenantManager = App::make(TenantManager::class);
        $tenantManager->setTenantId($this->org->id);

        $this->user = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Dr. K. Raghavan',
            'email' => 'president@royalpalm.in',
            'password' => bcrypt('password'),
            'role' => 'president',
        ]);
    }

    /**
     * Test Outbox background worker command.
     */
    public function test_outbox_worker_command_executes_successfully(): void
    {
        OutboxEvent::record('proposal.created', ['proposal_id' => 'dummy-id-123']);
        OutboxEvent::record('inventory.low_stock', ['item_id' => 'dummy-item-123']);

        $this->artisan('outbox:process')
            ->expectsOutputToContain('Outbox event processing complete')
            ->assertExitCode(0);

        $this->assertEquals(0, OutboxEvent::where('status', 'pending')->count());
    }

    /**
     * Test Sanctum API Login & Resident Invoices API.
     */
    public function test_mobile_api_authentication_and_resident_invoices(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'president@royalpalm.in',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'token', 'user']);

        $token = $response->json('token');

        $prop = Property::create([
            'organization_id' => $this->org->id,
            'name' => 'Royal Palm Estate',
            'address_line1' => 'Plot 4, Action Area II, New Town',
            'pin' => '700156',
        ]);

        $building = Building::create([
            'organization_id' => $this->org->id,
            'property_id' => $prop->id,
            'name' => 'Tower A',
        ]);

        $unit = Unit::create([
            'organization_id' => $this->org->id,
            'building_id' => $building->id,
            'flat_number' => '101',
            'floor' => 1,
            'unit_type' => '3BHK',
            'super_built_up_area' => 1500.00,
            'monthly_maintenance_amount' => 3500.00,
            'outstanding_amount' => 0.00,
        ]);

        $this->user->update(['unit_id' => $unit->id]);

        MaintenanceEntry::create([
            'organization_id' => $this->org->id,
            'unit_id' => $unit->id,
            'billing_year' => 2026,
            'billing_month' => 9,
            'month_name' => 'September 2026',
            'title' => 'Monthly Maintenance',
            'base_maintenance' => 3500.00,
            'total_due' => 3500.00,
            'amount_paid' => 0.00,
            'status' => 'Unpaid',
        ]);

        $apiResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/resident/invoices');

        $apiResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_outstanding', 3500);
    }

    /**
     * Test GST & TDS Compliance Calculation on Cash Release Bills.
     */
    public function test_gst_and_tds_tax_compliance_calculation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ControlCenter::class)
            ->set('billTitle', 'Elevator AMC Repairs')
            ->set('billAmount', 10000.00)
            ->set('billDate', now()->format('Y-m-d'))
            ->set('billCategory', 'Maintenance & Repairs')
            ->set('billFundSource', 'Main Cash Collection Fund')
            ->set('billGstType', 'cgst_sgst')
            ->set('billGstRate', 18.00)
            ->set('billTdsSection', '194C')
            ->set('billTdsRate', 2.00)
            ->set('billStatus', 'DISBURSED')
            ->call('saveCashBillEntry')
            ->assertHasNoErrors();

        $bill = BuildingCashBill::where('title', 'Elevator AMC Repairs')->first();
        $this->assertNotNull($bill);
        $this->assertEquals(1800.00, $bill->gst_amount); // 18% of 10000
        $this->assertEquals(200.00, $bill->tds_amount); // 2% of 10000
        $this->assertEquals(11600.00, $bill->net_payable); // 10000 + 1800 - 200
    }

    /**
     * Test Audit Trail tab in Control Center.
     */
    public function test_control_center_audit_logs_tab_renders(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ControlCenter::class)
            ->call('changeTab', 'audit-logs')
            ->assertSet('activeTab', 'audit-logs')
            ->assertSee('System Audit Trail & Security Log Inspector', false);
    }
}
