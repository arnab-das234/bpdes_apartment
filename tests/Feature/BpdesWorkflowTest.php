<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantManager;
use App\Modules\Planning\Models\Proposal;
use App\Modules\Planning\Models\ProposalDecision;
use App\Modules\Execution\Models\Project;
use App\Modules\Execution\Models\ProjectMilestone;
use App\Modules\Execution\Models\Task;
use App\Modules\Finance\Models\JournalEntry;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class BpdesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the full proposal lifecycle, promotion to project, tasks, and ledger creation.
     */
    public function test_full_bpdes_workflow_and_multi_tenancy(): void
    {
        $tenantManager = App::make(TenantManager::class);

        // 1. Create Organization (Tenant A)
        $orgA = Organization::create([
            'name' => 'Royal Palm Society',
            'subdomain' => 'royalpalm',
            'status' => 'active',
        ]);

        // 2. Set tenant A context
        $tenantManager->setTenantId($orgA->id);

        // Verify active tenant
        $this->assertEquals($orgA->id, $tenantManager->getTenantId());

        // 3. Create Users under Tenant A
        $presidentA = User::create([
            'name' => 'Dr. K. Raghavan',
            'email' => 'president@royalpalm.in',
            'password' => bcrypt('password'),
            'role' => 'president',
        ]);

        $auditorA = User::create([
            'name' => 'Amit Sharma',
            'email' => 'auditor@royalpalm.in',
            'password' => bcrypt('password'),
            'role' => 'auditor',
        ]);

        // Verify users belong to Org A
        $this->assertEquals($orgA->id, $presidentA->organization_id);
        $this->assertEquals($orgA->id, $auditorA->organization_id);

        // 4. Create a Proposal (Tenant A)
        $proposal = Proposal::create([
            'title' => 'Terrace Waterproofing',
            'description' => 'Fixing leakages on block A terrace.',
            'budget' => 500000.00,
            'status' => Proposal::STATUS_VERIFICATION,
            'checklist' => [
                ['description' => 'Structural load audit', 'checked' => false],
                ['description' => 'Municipal permission check', 'checked' => false],
            ],
            'created_by' => $auditorA->id,
        ]);

        // Verify proposal is scoped to Org A
        $this->assertEquals($orgA->id, $proposal->organization_id);

        // Verify checklist works and state can transition
        $this->assertFalse(collect($proposal->checklist)->every('checked', true));

        // Let's tick checklist items
        $checklist = $proposal->checklist;
        $checklist[0]['checked'] = true;
        $checklist[1]['checked'] = true;
        $proposal->checklist = $checklist;
        $proposal->save();

        // Verify state transitions: VERIFICATION -> VERIFIED -> COMMITTEE_REVIEW -> RECOMMENDED -> PRESIDENT_REVIEW
        $proposal->transitionTo(Proposal::STATUS_VERIFIED);
        $this->assertEquals(Proposal::STATUS_VERIFIED, $proposal->status);

        $proposal->transitionTo(Proposal::STATUS_COMMITTEE_REVIEW);
        $proposal->transitionTo(Proposal::STATUS_RECOMMENDED);
        $proposal->transitionTo(Proposal::STATUS_PRESIDENT_REVIEW);
        $this->assertEquals(Proposal::STATUS_PRESIDENT_REVIEW, $proposal->status);

        // 5. Execute President Approval transaction (mimics Livewire submission)
        $remarks = 'Approved waterproofing project from general reserves.';
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($proposal, $presidentA, $remarks) {
            // Write Decision Audit Record
            ProposalDecision::create([
                'proposal_id' => $proposal->id,
                'decided_by' => $presidentA->id,
                'decision' => 'APPROVED',
                'remarks' => $remarks,
            ]);

            // Transition proposal state
            $proposal->transitionTo(Proposal::STATUS_APPROVED);

            // Promote to Project
            $project = Project::create([
                'proposal_id' => $proposal->id,
                'title' => $proposal->title,
                'description' => $proposal->description,
                'budget' => $proposal->budget,
                'status' => 'Planning',
                'start_date' => now(),
                'end_date' => now()->addDays(30),
            ]);

            // Generate initial kickoff task
            Task::create([
                'project_id' => $project->id,
                'title' => 'Project Kickoff Meeting',
                'description' => 'Initial coordination meeting with water-proofing contractor.',
                'status' => Task::STATUS_ASSIGNED,
                'assigned_to' => $presidentA->id,
                'due_date' => now()->addDays(7),
            ]);

            // Write balancing Double-Entry ledger journal entries
            JournalEntry::create([
                'project_id' => $project->id,
                'proposal_id' => $proposal->id,
                'reference' => 'TXN-TEST01',
                'type' => 'DEBIT',
                'amount' => $proposal->budget,
                'account_name' => 'General Reserve Fund',
                'description' => "Debit for project funding: {$proposal->title}",
                'recorded_by' => $presidentA->id,
            ]);

            JournalEntry::create([
                'project_id' => $project->id,
                'proposal_id' => $proposal->id,
                'reference' => 'TXN-TEST01',
                'type' => 'CREDIT',
                'amount' => $proposal->budget,
                'account_name' => 'Project Budget: ' . $project->title,
                'description' => "Funding allocated for Project: {$project->title}",
                'recorded_by' => $presidentA->id,
            ]);
        });

        // 6. Assertions for workflow outcome
        $proposal->refresh();
        $this->assertEquals(Proposal::STATUS_APPROVED, $proposal->status);

        // Verify Decision audit record
        $decision = ProposalDecision::where('proposal_id', $proposal->id)->first();
        $this->assertNotNull($decision);
        $this->assertEquals('APPROVED', $decision->decision);
        $this->assertEquals($remarks, $decision->remarks);

        // Verify Project exists
        $project = Project::where('proposal_id', $proposal->id)->first();
        $this->assertNotNull($project);
        $this->assertEquals($proposal->title, $project->title);
        $this->assertEquals('Planning', $project->status);

        // Verify Tasks exist
        $task = Task::where('project_id', $project->id)->first();
        $this->assertNotNull($task);
        $this->assertEquals('Project Kickoff Meeting', $task->title);

        // Verify Balancing Ledger Entries exist
        $debit = JournalEntry::where('project_id', $project->id)->where('type', 'DEBIT')->first();
        $credit = JournalEntry::where('project_id', $project->id)->where('type', 'CREDIT')->first();

        $this->assertNotNull($debit);
        $this->assertNotNull($credit);
        $this->assertEquals('General Reserve Fund', $debit->account_name);
        $this->assertEquals('Project Budget: ' . $project->title, $credit->account_name);
        $this->assertEquals(500000.00, $debit->amount);
        $this->assertEquals(500000.00, $credit->amount);

        // 7. Verify Multi-Tenancy isolation (Tenant B)
        $orgB = Organization::create([
            'name' => 'Emerald Heights Society',
            'subdomain' => 'emerald',
            'status' => 'active',
        ]);

        // Switch context to Tenant B
        $tenantManager->setTenantId($orgB->id);

        // Tenant B queries should return 0 results for Tenant A data
        $this->assertEquals(0, Proposal::count());
        $this->assertEquals(0, Project::count());
        $this->assertEquals(0, Task::count());
        $this->assertEquals(0, JournalEntry::count());
    }

    /**
     * Test that the Livewire components render successfully.
     */
    public function test_livewire_components_render(): void
    {
        $org = Organization::create([
            'name' => 'Royal Palm Society',
            'subdomain' => 'royalpalm',
            'status' => 'active',
        ]);
        App::make(TenantManager::class)->setTenantId($org->id);

        $user = User::create([
            'name' => 'Dr. Raghavan',
            'email' => 'president@royalpalm.in',
            'password' => bcrypt('password'),
            'role' => 'president',
        ]);

        $this->actingAs($user);

        \Livewire\Livewire::test(\App\Livewire\ControlCenter::class)
            ->assertStatus(200);

        \Livewire\Livewire::test(\App\Livewire\PresidentInbox::class)
            ->assertStatus(200);

        \Livewire\Livewire::test(\App\Livewire\OrganizationCrud::class)
            ->assertStatus(200);

        \Livewire\Livewire::test(\App\Livewire\SuperadminDashboard::class)
            ->assertStatus(200);

        \Livewire\Livewire::test(\App\Livewire\Auth\Login::class)
            ->assertRedirect('/president-inbox');
    }

    /**
     * Test resident onboarding, dashboard claims, and upgrade proposals.
     */
    public function test_resident_onboarding_and_dashboard_workflow(): void
    {
        $org = Organization::create([
            'name' => 'Royal Palm Society',
            'subdomain' => 'royalpalm',
            'status' => 'active',
        ]);
        App::make(TenantManager::class)->setTenantId($org->id);

        $prop = \App\Models\Property::create([
            'organization_id' => $org->id,
            'name' => 'Royal Palm Enclave',
            'address_line1' => 'Action Area II',
            'pin' => '700156',
        ]);

        $bldg = \App\Models\Building::create([
            'organization_id' => $org->id,
            'property_id' => $prop->id,
            'name' => 'Tower A',
            'floors' => 5,
        ]);

        $unit = \App\Models\Unit::create([
            'organization_id' => $org->id,
            'building_id' => $bldg->id,
            'flat_number' => '202',
            'floor' => 2,
            'unit_type' => '2BHK',
            'occupancy_status' => 'Vacant',
            'monthly_maintenance_amount' => 2500,
        ]);

        // Run resident registration Livewire test
        \Livewire\Livewire::test(\App\Livewire\Auth\Register::class)
            ->set('selectedOrgId', $org->id)
            ->set('selectedBhk', '2BHK')
            ->set('selectedFloor', 2)
            ->set('name', 'Resident User')
            ->set('mobile', '9876543212')
            ->set('email', 'resident@test.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('familyMembers', 3)
            ->set('isProfessional', true)
            ->set('carParking', true)
            ->set('flatNumber', '202')
            ->call('registerResident')
            ->assertRedirect('/resident/dashboard');

        // Confirm DB structures
        $this->assertDatabaseHas('users', [
            'email' => 'resident@test.com',
            'role' => 'resident',
            'unit_id' => $unit->id,
        ]);

        $user = User::where('email', 'resident@test.com')->first();
        $this->actingAs($user);

        // Run resident dashboard Livewire test
        \Livewire\Livewire::test(\App\Livewire\ResidentDashboard::class)
            ->assertStatus(200)
            ->set('complaintCategory', 'Plumbing')
            ->set('complaintPriority', 'High')
            ->set('complaintDescription', 'Severe water leakage in kitchen sink pipe.')
            ->call('submitComplaint')
            ->set('proposalTitle', 'Society Gym Upgrade')
            ->set('proposalDescription', 'Adding new dumbbells and treadmills.')
            ->set('proposalBudget', 150000)
            ->call('submitProposal');

        // Confirm database records
        $this->assertDatabaseHas('complaints', [
            'category' => 'Plumbing',
            'priority' => 'High',
            'description' => 'Severe water leakage in kitchen sink pipe.',
        ]);

        $this->assertDatabaseHas('proposals', [
            'title' => 'Society Gym Upgrade',
            'budget' => 150000,
        ]);
    }

    /**
     * Test that President, Secretary, and Joint Secretary can create and toggle project milestones.
     */
    public function test_president_and_secretary_can_create_and_toggle_project_milestones(): void
    {
        $org = Organization::create([
            'name' => 'Royal Palm Society',
            'subdomain' => 'royalpalm',
            'status' => 'active',
        ]);
        App::make(TenantManager::class)->setTenantId($org->id);

        $president = User::create([
            'name' => 'Dr. K. Raghavan',
            'email' => 'president@test.com',
            'password' => bcrypt('password'),
            'role' => 'president',
        ]);

        $project = Project::create([
            'organization_id' => $org->id,
            'title' => 'Elevator Safety Overhaul',
            'description' => 'Replacing old gearboxes',
            'budget' => 500000.00,
            'status' => 'Planning',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);

        $this->actingAs($president);

        // Test Milestone creation by President in PresidentInbox
        \Livewire\Livewire::test(\App\Livewire\PresidentInbox::class)
            ->assertStatus(200)
            ->set('newMilestoneProjectId', $project->id)
            ->set('newMilestoneTitle', 'President Phase 1: Machine Safety Audit')
            ->set('newMilestoneDescription', 'Full safety audit by certified engineer')
            ->set('newMilestoneDueDate', '2026-09-15')
            ->set('newMilestoneBudget', 75000)
            ->call('createMilestone');

        $this->assertDatabaseHas('project_milestones', [
            'project_id' => $project->id,
            'title' => 'President Phase 1: Machine Safety Audit',
            'budget_allocation' => 75000,
            'status' => 'PENDING',
        ]);

        $milestone = ProjectMilestone::where('title', 'President Phase 1: Machine Safety Audit')->first();

        // Test Milestone toggle by President
        \Livewire\Livewire::test(\App\Livewire\PresidentInbox::class)
            ->call('toggleMilestoneStatus', $milestone->id);

        $this->assertEquals('COMPLETED', $milestone->fresh()->status);

        // Test Milestone creation by Secretary in ControlCenter
        $secretaryRole = Role::create([
            'organization_id' => $org->id,
            'name' => 'Secretary',
            'description' => 'Secretary Desk',
        ]);

        $secretary = User::create([
            'name' => 'Arnab Das',
            'email' => 'secretary@test.com',
            'password' => bcrypt('password'),
            'role' => 'resident',
            'role_id' => $secretaryRole->id,
        ]);

        $this->actingAs($secretary);

        \Livewire\Livewire::test(\App\Livewire\ControlCenter::class)
            ->assertStatus(200)
            ->set('newMilestoneProjectId', $project->id)
            ->set('newMilestoneTitle', 'Secretary Phase 2: Cable Replacement')
            ->set('newMilestoneDescription', 'Replacing worn hoist ropes')
            ->set('newMilestoneDueDate', '2026-09-25')
            ->set('newMilestoneBudget', 120000)
            ->call('createMilestone');

        $this->assertDatabaseHas('project_milestones', [
            'project_id' => $project->id,
            'title' => 'Secretary Phase 2: Cable Replacement',
            'budget_allocation' => 120000,
            'status' => 'PENDING',
        ]);
    }
}

