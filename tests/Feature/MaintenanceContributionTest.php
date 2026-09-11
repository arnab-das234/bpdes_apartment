<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Person;
use App\Models\Membership;
use App\Models\User;
use App\Models\MaintenanceEntry;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MaintenanceContributionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $org = Organization::create([
            'name' => 'Royal Palm Co-Operative Society',
            'subdomain' => 'royalpalm',
            'status' => 'active',
        ]);

        app(TenantManager::class)->setTenantId($org->id);

        $prop = \App\Models\Property::create([
            'organization_id' => $org->id,
            'name' => 'Royal Palm Enclave',
            'address_line1' => 'Plot 4, Action Area II, New Town',
            'pin' => '700156',
        ]);

        $bldg = Building::create([
            'organization_id' => $org->id,
            'property_id' => $prop->id,
            'name' => 'Tower A',
            'floors' => 5,
            'units_count' => 4,
        ]);

        $unit101 = Unit::create([
            'organization_id' => $org->id,
            'building_id' => $bldg->id,
            'flat_number' => '101',
            'floor' => 1,
            'unit_type' => '3BHK',
            'monthly_maintenance_amount' => 3500.00,
            'outstanding_amount' => 0.00,
        ]);

        $person1 = Person::create([
            'organization_id' => $org->id,
            'name' => 'Dr. K. Raghavan',
            'email' => 'president@royalpalm.in',
            'mobile' => '9830098300',
        ]);

        Membership::create([
            'organization_id' => $org->id,
            'unit_id' => $unit101->id,
            'person_id' => $person1->id,
            'primary_owner' => true,
        ]);

        User::create([
            'organization_id' => $org->id,
            'person_id' => $person1->id,
            'unit_id' => $unit101->id,
            'name' => 'Dr. K. Raghavan',
            'email' => 'president@royalpalm.in',
            'password' => bcrypt('password'),
            'role' => 'president',
        ]);
    }

    public function test_can_create_month_wise_maintenance_entry_and_backlog(): void
    {
        $unit = Unit::where('flat_number', '101')->first();
        $user = User::where('role', 'president')->first();

        // 1. Create Backlog Entry
        $backlogEntry = MaintenanceEntry::create([
            'organization_id' => $unit->organization_id,
            'unit_id' => $unit->id,
            'billing_year' => 2025,
            'billing_month' => 12,
            'month_name' => 'December 2025',
            'title' => 'Historical Backlog Arrears Q4 2025',
            'is_backlog' => true,
            'base_maintenance' => 0.00,
            'backlog_amount' => 3500.00,
            'total_due' => 3500.00,
            'amount_paid' => 0.00,
            'status' => 'Overdue',
            'recorded_by' => $user->id,
        ]);

        // 2. Create Regular Monthly Entry
        $monthlyEntry = MaintenanceEntry::create([
            'organization_id' => $unit->organization_id,
            'unit_id' => $unit->id,
            'billing_year' => 2026,
            'billing_month' => 9,
            'month_name' => 'September 2026',
            'title' => 'Monthly Maintenance - September 2026',
            'is_backlog' => false,
            'base_maintenance' => 3500.00,
            'total_due' => 3500.00,
            'amount_paid' => 0.00,
            'status' => 'Unpaid',
            'recorded_by' => $user->id,
        ]);

        $unit->load('maintenanceEntries');

        // Assert calculated attributes
        $this->assertEquals(7000.00, $unit->calculated_total_maintenance);
        $this->assertEquals(0.00, $unit->calculated_total_paid);
        $this->assertEquals(7000.00, $unit->calculated_outstanding);
        $this->assertEquals(3500.00, $unit->calculated_backlog_dues);
    }

    public function test_president_inbox_livewire_can_save_entry_and_generate_bills(): void
    {
        $user = User::where('role', 'president')->first();
        $unit = Unit::where('flat_number', '101')->first();

        $this->actingAs($user);

        Livewire::test(\App\Livewire\PresidentInbox::class)
            ->set('activeTab', 'maintenance-collection')
            ->call('openEntryModal', $unit->id, false)
            ->assertSee('Build Month-Wise Maintenance Entry')
            ->set('entryBillingMonth', 9)
            ->set('entryBillingYear', 2026)
            ->set('entryTitle', 'Monthly Maintenance - September 2026')
            ->set('entryBaseAmount', 3500.00)
            ->call('saveMaintenanceEntry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('maintenance_entries', [
            'unit_id' => $unit->id,
            'billing_month' => 9,
            'billing_year' => 2026,
            'total_due' => 3500.00,
        ]);

        // Test quick payment
        $entry = MaintenanceEntry::where('unit_id', $unit->id)->first();
        Livewire::test(\App\Livewire\PresidentInbox::class)
            ->call('recordQuickPayment', $entry->id, 3500.00, 'UPI');

        $this->assertDatabaseHas('maintenance_entries', [
            'id' => $entry->id,
            'amount_paid' => 3500.00,
            'status' => 'Paid',
        ]);
    }

    public function test_control_center_can_register_resident_with_voter_id_and_submeter(): void
    {
        $user = User::where('role', 'president')->first();
        $unit = Unit::where('flat_number', '101')->first();

        $this->actingAs($user);

        Livewire::test(\App\Livewire\ControlCenter::class)
            ->set('residentName', 'Shyamal Sen')
            ->set('residentMobile', '9830098301')
            ->set('residentEmail', 'shyamal@sen.com')
            ->set('residentFlatId', $unit->id)
            ->set('residentType', 'Owner')
            ->set('familyMembersCount', 5)
            ->set('residentIdType', 'Voter ID')
            ->set('residentIdNumber', 'ABC1234567')
            ->set('electricityConnectionType', 'submeter')
            ->set('residentSubmeterNumber', 'SUB-A-101')
            ->call('registerResident')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('persons', [
            'name' => 'Shyamal Sen',
            'mobile' => '9830098301',
            'family_members' => 5,
            'id_type' => 'Voter ID',
            'id_number' => 'ABC1234567',
        ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'electricity_connection_type' => 'submeter',
            'submeter_number' => 'SUB-A-101',
        ]);
    }

    public function test_can_access_maintenance_invoice_and_resident_statement(): void
    {
        $user = User::where('role', 'president')->first();
        $unit = Unit::where('flat_number', '101')->first();

        $entry = MaintenanceEntry::create([
            'organization_id' => $unit->organization_id,
            'unit_id' => $unit->id,
            'billing_year' => 2026,
            'billing_month' => 9,
            'month_name' => 'September 2026',
            'title' => 'Monthly Maintenance - September 2026',
            'is_backlog' => false,
            'base_maintenance' => 3500.00,
            'total_due' => 3500.00,
            'amount_paid' => 3500.00,
            'status' => 'Paid',
            'recorded_by' => $user->id,
        ]);

        $this->actingAs($user);

        // Test Invoice view route
        $responseInvoice = $this->get(route('maintenance.invoice', $entry->id));
        $responseInvoice->assertStatus(200);
        $responseInvoice->assertSee('Maintenance Receipt');
        $responseInvoice->assertSee('Flat 101');
        $responseInvoice->assertSee('3,500.00');

        // Test Statement view route
        $responseStatement = $this->get(route('maintenance.statement', $unit->id));
        $responseStatement->assertStatus(200);
        $responseStatement->assertSee('STATEMENT OF ACCOUNT');
        $responseStatement->assertSee('Flat 101');
        $responseStatement->assertSee('Dr. K. Raghavan');
    }
}
