<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Organization;
use App\Models\Person;
use App\Models\Unit;
use App\Models\User;
use App\Models\BuildingCashBill;
use App\Modules\Finance\Models\JournalEntry;
use App\Livewire\ControlCenter;
use App\Livewire\PresidentInbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BuildingCashBillTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_treasurer_and_president_can_record_bill_entry_and_release_cash(): void
    {
        $org = Organization::create([
            'name' => 'Narmada Housing Society',
            'subdomain' => 'narmada',
        ]);
        $tenantManager = \Illuminate\Support\Facades\App::make(\App\Services\TenantManager::class);
        $tenantManager->setTenantId($org->id);

        $user = User::create([
            'name' => 'Society Treasurer',
            'email' => 'treasurer@narmada.com',
            'password' => bcrypt('password'),
            'role' => 'treasurer',
        ]);
        $this->actingAs($user);

        $property = \App\Models\Property::create([
            'name' => 'Narmada Premises',
            'address_line1' => '123 Main Street',
            'pin' => '700156',
        ]);

        $building = Building::create([
            'property_id' => $property->id,
            'name' => 'Block A',
        ]);

        $unit = Unit::create([
            'organization_id' => $org->id,
            'building_id' => $building->id,
            'flat_number' => '301',
            'floor' => 3,
        ]);

        $person = Person::create([
            'organization_id' => $org->id,
            'name' => 'Rahul Sharma',
            'mobile' => '9876543210',
            'email' => 'rahul@narmada.com',
        ]);

        Livewire::test(ControlCenter::class)
            ->call('openCashBillModal', $unit->id)
            ->set('billTitle', 'Water Pump Motor Replacement')
            ->set('billCategory', 'Water Pump & Sanitation')
            ->set('billAmount', 4500.00)
            ->set('billDate', now()->format('Y-m-d'))
            ->set('billResponsiblePersonId', $person->id)
            ->set('billFundSource', 'Main Cash Collection Fund')
            ->set('billVendorName', 'Bengal Hardware')
            ->call('saveCashBillEntry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('building_cash_bills', [
            'organization_id' => $org->id,
            'title' => 'Water Pump Motor Replacement',
            'amount' => 4500.00,
            'responsible_person_name' => 'Rahul Sharma',
            'fund_source' => 'Main Cash Collection Fund',
        ]);

        $this->assertDatabaseHas('finance_journal_entries', [
            'organization_id' => $org->id,
            'type' => 'CREDIT',
            'account_name' => 'Main Cash Collection Fund',
            'amount' => 4500.00,
        ]);

        $this->assertDatabaseHas('finance_journal_entries', [
            'organization_id' => $org->id,
            'type' => 'DEBIT',
            'account_name' => 'Building Expense: Water Pump Motor Replacement',
            'amount' => 4500.00,
        ]);

        // President can view and manage cash release desk
        $president = User::create([
            'name' => 'Society President',
            'email' => 'president@narmada.com',
            'password' => bcrypt('password'),
            'role' => 'president',
        ]);
        $this->actingAs($president);

        Livewire::test(PresidentInbox::class)
            ->set('activeTab', 'cash-release-bills')
            ->assertSee('Water Pump Motor Replacement')
            ->assertSee('Rahul Sharma')
            ->assertSee('4,500');
    }

    public function test_president_and_cashier_can_upload_bill_document(): void
    {
        $org = Organization::create([
            'name' => 'Narmada Housing Society',
            'subdomain' => 'narmada',
        ]);
        $tenantManager = \Illuminate\Support\Facades\App::make(\App\Services\TenantManager::class);
        $tenantManager->setTenantId($org->id);

        $president = User::create([
            'name' => 'Society President',
            'email' => 'president_doc@narmada.com',
            'password' => bcrypt('password'),
            'role' => 'president',
        ]);
        $this->actingAs($president);

        $file = \Illuminate\Http\UploadedFile::fake()->create('voucher_bill.pdf', 500, 'application/pdf');

        Livewire::test(PresidentInbox::class)
            ->call('openCashBillModal')
            ->set('billTitle', 'Lift Generator Repair')
            ->set('billCategory', 'Elevator Maintenance')
            ->set('billAmount', 3200.00)
            ->set('billDate', now()->format('Y-m-d'))
            ->set('billDocument', $file)
            ->call('saveCashBillEntry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('building_cash_bills', [
            'organization_id' => $org->id,
            'title' => 'Lift Generator Repair',
            'amount' => 3200.00,
        ]);
    }

    public function test_can_connect_bill_entry_with_proposal_project_and_milestone(): void
    {
        $org = Organization::create([
            'name' => 'Narmada Housing Society',
            'subdomain' => 'narmada',
        ]);
        $tenantManager = \Illuminate\Support\Facades\App::make(\App\Services\TenantManager::class);
        $tenantManager->setTenantId($org->id);

        $user = User::create([
            'name' => 'Society Treasurer',
            'email' => 'treasurer_link@narmada.com',
            'password' => bcrypt('password'),
            'role' => 'treasurer',
        ]);
        $this->actingAs($user);

        $proposal = \App\Modules\Planning\Models\Proposal::create([
            'organization_id' => $org->id,
            'title' => 'CCTV Security Setup Proposal',
            'description' => 'Install 16 HD CCTV Cameras',
            'budget' => 45000.00,
            'status' => 'APPROVED',
            'created_by' => $user->id,
        ]);

        $project = \App\Modules\Execution\Models\Project::create([
            'organization_id' => $org->id,
            'proposal_id' => $proposal->id,
            'title' => 'CCTV Installation Project',
            'description' => 'Execution of 16 Cameras',
            'budget' => 45000.00,
            'status' => 'Active',
        ]);

        $milestone = \App\Modules\Execution\Models\ProjectMilestone::create([
            'project_id' => $project->id,
            'title' => 'Phase 1 Wiring & DVR Mount',
            'description' => 'First phase wiring',
            'weightage' => 40.00,
        ]);

        Livewire::test(ControlCenter::class)
            ->call('openCashBillModal')
            ->set('billTitle', 'CCTV Wiring Materials Purchase')
            ->set('billCategory', 'Equipment & Hardware')
            ->set('billAmount', 12500.00)
            ->set('billDate', now()->format('Y-m-d'))
            ->set('billProposalId', $proposal->id)
            ->set('billProjectId', $project->id)
            ->set('billMilestoneId', $milestone->id)
            ->call('saveCashBillEntry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('building_cash_bills', [
            'organization_id' => $org->id,
            'title' => 'CCTV Wiring Materials Purchase',
            'proposal_id' => $proposal->id,
            'project_id' => $project->id,
            'milestone_id' => $milestone->id,
            'amount' => 12500.00,
        ]);
    }

    public function test_cashier_user_login_redirects_directly_to_cash_release_desk(): void
    {
        $org = Organization::create([
            'name' => 'Narmada Housing Society',
            'subdomain' => 'narmada',
        ]);

        $cashierUser = User::create([
            'name' => 'Society Cashier',
            'email' => 'cashier@society.com',
            'password' => bcrypt('password'),
            'role' => 'cashier',
            'organization_id' => $org->id,
        ]);

        $this->assertEquals('/control-center?tab=cash-release-bills', $cashierUser->homeRoute());

        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'cashier@society.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect('/control-center?tab=cash-release-bills');
    }
}
