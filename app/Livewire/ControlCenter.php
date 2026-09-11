<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\User;
use App\Models\Property;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Person;
use App\Models\Membership;
use App\Models\Nominee;
use App\Models\CommitteeAppointment;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Document;
use App\Services\TenantManager;
use App\Modules\Planning\Models\Proposal;
use App\Modules\Execution\Models\Project;
use App\Modules\Execution\Models\Task;
use App\Modules\Execution\Models\ProjectMilestone;
use App\Modules\Finance\Models\JournalEntry;
use App\Models\BuildingCashBill;
use App\Livewire\Traits\HandlesMaintenanceEntries;
use App\Livewire\Traits\HandlesCashReleaseBills;
use App\Livewire\Traits\HandlesInventoryDesk;
use App\Livewire\Traits\HandlesBalanceSheet;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ControlCenter extends Component
{
    use WithFileUploads, HandlesMaintenanceEntries, HandlesCashReleaseBills, HandlesInventoryDesk, HandlesBalanceSheet;
    // Active Tab state
    public string $activeTab = 'overview'; // overview, units, maintenance, residents, committee, documents, rbac, cash-release-bills, inventory-desk, balance-sheet, audit-logs

    // Audit Trail Tab State
    public string $auditSearch = '';
    public string $auditActionFilter = 'all';

    // Tab 7: RBAC & Committee Designations
    public ?string $roleId = null;
    public string $roleName = '';
    public string $roleDescription = '';
    public array $selectedPermissions = [];
    
    // Committee Appointment Fields
    public ?string $appointPersonId = null;
    public ?string $appointRoleId = null;
    public string $appointmentStartDate = '';

    // Selected Proposal Detail
    public ?string $selectedProposalId = null;
    
    // Proposal fields
    public string $newTitle = '';
    public string $newDescription = '';
    public string $newBudget = '';
    public string $newExecutionDate = '';
    public string $newDeadline = '';
    public ?string $newAssignedSecretaryId = null;
    public $newDocument = null;
    public string $newJustification = '';
    public array $newInvolvedMembers = [];

    // Project Milestone fields
    public string $newMilestoneProjectId = '';
    public string $newMilestoneTitle = '';
    public string $newMilestoneDescription = '';
    public string $newMilestoneDueDate = '';
    public string $newMilestoneBudget = '';

    // Tab 2: Property & Unit Setup Fields
    public string $propertyName = '';
    public string $propertyAddress = '';
    public string $propertyPin = '';
    public string $plotNumber = '';
    public string $dagNumber = '';
    
    public string $selectedPropertyId = '';
    public string $towerName = '';
    public int $towerFloors = 1;

    public string $selectedBuildingId = '';
    public string $flatNumber = '';
    public int $flatFloor = 0;
    public string $unitType = '3BHK'; // 1BHK, 2BHK, 3BHK, PENTHOUSE
    public float $superArea = 1200.00;
    public float $monthlyMaintenance = 2500.00;

    // Tab 3: Resident Registry Fields
    public string $residentName = '';
    public string $residentMobile = '';
    public string $residentEmail = '';
    public int $familyMembersCount = 4;
    public string $residentIdType = 'Aadhaar'; // Aadhaar, PAN, Voter ID, Passport
    public string $residentIdNumber = '';
    public string $residentFlatId = '';
    public string $residentType = 'Owner'; // Owner, Tenant
    public string $residentOccupation = 'Business';
    public string $guardianName = '';
    public string $electricityConnectionType = 'own_meter';
    public string $residentMeterNumber = '';
    public string $residentSubmeterNumber = '';

    // Tab 4: Committee Fields
    public string $selectedPersonId = '';
    public string $designation = 'Secretary'; // Secretary, Vice Secretary, Treasurer, Committee Member
    public string $appointmentMethod = 'Election';

    // Tab 5: Document Upload fields
    public string $docName = '';
    public string $docCategory = 'Form A'; // Form A, Form 1, Bye-Laws, Deed, approved plan
    public string $docDescription = '';

    public function mount(): void
    {
        $this->initMaintenanceDefaults();

        // 1. Seed database automatically if it's completely empty
        $this->ensureSeedDataExists();
        $this->ensureSampleInventorySeeded();

        $requestedTab = request()->query('tab');
        if (in_array($requestedTab, ['overview', 'units', 'maintenance', 'residents', 'committee', 'documents', 'rbac', 'cash-release-bills', 'inventory-desk', 'balance-sheet', 'audit-logs'], true)) {
            $this->activeTab = $requestedTab;
        }

        // 2. Select first proposal under verification by default
        $firstUnderVerification = Proposal::where('status', Proposal::STATUS_VERIFICATION)->first();
        if ($firstUnderVerification) {
            $this->selectedProposalId = $firstUnderVerification->id;
        } else {
            $first = Proposal::first();
            if ($first) {
                $this->selectedProposalId = $first->id;
            }
        }

        // Set default dropdown targets if options exist
        $firstProperty = Property::first();
        if ($firstProperty) {
            $this->selectedPropertyId = $firstProperty->id;
        }

        $firstBuilding = Building::first();
        if ($firstBuilding) {
            $this->selectedBuildingId = $firstBuilding->id;
        }
    }

    public function changeTab(string $tab): void
    {
        $allowedTabs = ['overview', 'units', 'maintenance', 'residents', 'committee', 'documents', 'rbac', 'cash-release-bills', 'inventory-desk', 'balance-sheet', 'audit-logs'];
        if (!in_array($tab, $allowedTabs, true)) {
            $this->activeTab = 'overview';
            return;
        }

        $this->activeTab = $tab;
    }

    /**
     * Ensure the default tenant organization and mock dataset exists.
     */
    protected function ensureSeedDataExists(): void
    {
        try {
            if (Organization::count() === 0) {
                // Ensure transaction
                DB::transaction(function () {
                    $org = Organization::create([
                        'name' => 'Royal Palm Co-Operative Society',
                        'short_name' => 'Royal Palm',
                        'subdomain' => 'royalpalm',
                        'registration_type' => 'Apartment Owners Association',
                        'registration_number' => 'WB/AOA/2026/1049',
                        'registration_authority' => 'Housing Department West Bengal',
                        'registration_act' => 'West Bengal Apartment Ownership Act, 1972',
                        'official_email' => 'admin@royalpalm.in',
                        'official_mobile' => '9876543210',
                        'status' => 'active',
                        'settings' => ['currency' => 'INR']
                    ]);

                    // Set tenant context for model creation
                    $tenantManager = App::make(TenantManager::class);
                    $tenantManager->setTenantId($org->id);

                    // Create users
                    $president = User::create([
                        'organization_id' => $org->id,
                        'name' => 'Dr. K. Raghavan',
                        'email' => 'president@royalpalm.in',
                        'password' => bcrypt('password'),
                        'role' => 'president',
                    ]);

                    $verifier = User::create([
                        'organization_id' => $org->id,
                        'name' => 'Amit Sharma',
                        'email' => 'auditor@royalpalm.in',
                        'password' => bcrypt('password'),
                        'role' => 'auditor',
                    ]);

                    // Create default Property (Premises)
                    $prop = Property::create([
                        'name' => 'Royal Palm Enclave',
                        'address_line1' => 'Plot 4, Action Area II, New Town',
                        'locality' => 'New Town',
                        'municipality' => 'NKDA',
                        'police_station' => 'New Town PS',
                        'district' => 'North 24 Parganas',
                        'state' => 'West Bengal',
                        'pin' => '700156',
                        'plot_number' => 'Dag No. 124',
                        'dag_number' => '124',
                        'khatian_number' => '512',
                        'mouza' => 'Reckjoani',
                        'total_land_area' => 3.5,
                        'common_areas' => ['Garden', 'Swimming Pool', 'Security Guard Post', 'Elevators']
                    ]);

                    // Create default Building
                    $bldg = Building::create([
                        'property_id' => $prop->id,
                        'name' => 'Tower A',
                        'floors' => 10,
                        'units_count' => 4,
                    ]);

                    // Create default Units
                    $u1 = Unit::create([
                        'building_id' => $bldg->id,
                        'flat_number' => '101',
                        'floor' => 1,
                        'unit_type' => '3BHK',
                        'super_built_up_area' => 1500.00,
                        'carpet_area' => 1200.00,
                        'undivided_land_share' => 450.00,
                        'ownership_type' => 'Owner',
                        'occupancy_status' => 'Self Occupied',
                        'monthly_maintenance_amount' => 3500.00,
                        'outstanding_amount' => 7000.00,
                    ]);

                    $u2 = Unit::create([
                        'building_id' => $bldg->id,
                        'flat_number' => '102',
                        'floor' => 1,
                        'unit_type' => '2BHK',
                        'super_built_up_area' => 1100.00,
                        'carpet_area' => 900.00,
                        'ownership_type' => 'Tenant',
                        'occupancy_status' => 'Rented',
                        'monthly_maintenance_amount' => 2600.00,
                        'outstanding_amount' => 0.00,
                    ]);

                    // Create Persons
                    $p1 = Person::create([
                        'name' => 'Dr. K. Raghavan',
                        'guardian_name' => 'S. Raghavan',
                        'dob' => '1965-08-15',
                        'gender' => 'Male',
                        'mobile' => '9830098300',
                        'email' => 'president@royalpalm.in',
                        'address' => 'Tower A, Flat 101',
                        'id_type' => 'Aadhaar',
                        'id_number' => '1111-2222-3333',
                    ]);

                    $p2 = Person::create([
                        'name' => 'Shyamal Sen',
                        'guardian_name' => 'N. Sen',
                        'dob' => '1978-11-23',
                        'gender' => 'Male',
                        'mobile' => '9830098301',
                        'email' => 'shyamal@society.in',
                        'address' => 'Tower A, Flat 102',
                        'id_type' => 'PAN',
                        'id_number' => 'ABCDE1234F',
                    ]);

                    // Create Memberships
                    Membership::create([
                        'unit_id' => $u1->id,
                        'person_id' => $p1->id,
                        'membership_number' => 'RP-MEM-001',
                        'primary_owner' => true,
                        'membership_status' => 'active',
                    ]);

                    Membership::create([
                        'unit_id' => $u2->id,
                        'person_id' => $p2->id,
                        'membership_number' => 'RP-MEM-002',
                        'primary_owner' => true,
                        'membership_status' => 'active',
                    ]);

                    // Create default Committee Appointment
                    CommitteeAppointment::create([
                        'person_id' => $p1->id,
                        'designation' => 'President',
                        'start_date' => now(),
                        'appointment_method' => 'Election',
                        'status' => 'active'
                    ]);

                    // Create default Documents
                    Document::create([
                        'name' => 'West Bengal Form A Declaration',
                        'category' => 'Form A',
                        'file_path' => 'docs/form_a_signed.pdf',
                        'description' => 'Form A submitted for Association registration.',
                    ]);

                    Document::create([
                        'name' => 'Registration Certificate (Form 1)',
                        'category' => 'Form 1',
                        'file_path' => 'docs/certificate.pdf',
                        'description' => 'Government issued Form 1 ownership association certificate.',
                    ]);

                    // Create Proposals
                    $prop1 = Proposal::create([
                        'title' => 'Terrace Waterproofing & Solar Panel Installation',
                        'description' => 'Fixing major leakage issues on building A & B terrace and installing a 50kW solar panel grid to reduce society common electricity bills.',
                        'budget' => 1850000.00,
                        'status' => Proposal::STATUS_VERIFICATION,
                        'checklist' => [
                            ['description' => 'Verify structural load capacity for solar panel weight', 'checked' => true],
                            ['description' => 'Check local municipal corporation permissions', 'checked' => false],
                            ['description' => 'Compare vendor quotations (minimum 3 bids)', 'checked' => false],
                        ],
                        'created_by' => $verifier->id,
                    ]);

                    $prop2 = Proposal::create([
                        'title' => 'Elevator Modernisation & Safety Upgrade',
                        'description' => 'Replacing old manual gate lifts with automatic sliding doors and ARD safety backup systems across blocks C, D, and E.',
                        'budget' => 4500000.00,
                        'status' => Proposal::STATUS_APPROVED,
                        'checklist' => [
                            ['description' => 'Verify OEM safety compliance certificate', 'checked' => true],
                            ['description' => 'Verify 5-year comprehensive maintenance contract (CMC)', 'checked' => true],
                            ['description' => 'Approval from state lift inspector registry', 'checked' => true],
                        ],
                        'created_by' => $verifier->id,
                    ]);

                    // Seed an active project from approved proposal
                    $proj = Project::create([
                        'organization_id' => $org->id,
                        'proposal_id' => $prop2->id,
                        'title' => 'Elevator Modernisation & Safety Upgrade',
                        'description' => 'Replacing old manual gate lifts with automatic sliding doors and ARD safety backup systems across blocks C, D, and E.',
                        'budget' => 4500000.00,
                        'status' => 'Active',
                        'start_date' => now()->subDays(15),
                        'end_date' => now()->addDays(15),
                    ]);

                    // Seed milestones for this project
                    ProjectMilestone::create([
                        'organization_id' => $org->id,
                        'project_id' => $proj->id,
                        'title' => 'Milestone 1: Safety Audit and Technical Lift Clearance',
                        'description' => 'Analyzing structural load, shaft alignments, and obtaining raw component safety clearances.',
                        'due_date' => now()->subDays(5),
                        'status' => 'COMPLETED',
                        'progress_percentage' => 100,
                        'budget_allocation' => 1500000.00,
                    ]);

                    ProjectMilestone::create([
                        'organization_id' => $org->id,
                        'project_id' => $proj->id,
                        'title' => 'Milestone 2: ARD Backup & Sliding Door Assembly',
                        'description' => 'Physical assembly of automated sliding gates and calibration of safety emergency recovery sensors.',
                        'due_date' => now()->addDays(5),
                        'status' => 'IN_PROGRESS',
                        'progress_percentage' => 60,
                        'budget_allocation' => 2000000.00,
                    ]);

                    ProjectMilestone::create([
                        'organization_id' => $org->id,
                        'project_id' => $proj->id,
                        'title' => 'Milestone 3: Inspector Certification & Handover',
                        'description' => 'Final audit by government lift inspector registry and full public rollout.',
                        'due_date' => now()->addDays(15),
                        'status' => 'PENDING',
                        'progress_percentage' => 0,
                        'budget_allocation' => 1000000.00,
                    ]);

                    // Seed a task
                    Task::create([
                        'project_id' => $proj->id,
                        'title' => 'Calibrate safety recovery sensors',
                        'description' => 'Test emergency brake recovery triggers on ARD activation.',
                        'status' => Task::STATUS_ASSIGNED,
                        'assigned_to' => $president->id,
                        'due_date' => now()->addDays(5),
                    ]);
                });
            }
        } catch (\Exception $e) {
            // Silence seeds error for safe migration re-run
        }
    }


    protected function isSecretaryWorkspace(): bool
    {
        $user = auth()->user();
        $roleName = strtolower($user?->roleRelation?->name ?? '');

        return $user?->role === 'secretary' || str_contains($roleName, 'secretary');
    }

    // TAB 2 ACTIONS: Create Property, Tower, Flat
    public function createProperty(): void
    {
        $this->validate([
            'propertyName' => 'required|string|min:3',
            'propertyAddress' => 'required|string',
            'propertyPin' => 'required|string|min:6',
        ]);

        $prop = Property::create([
            'name' => $this->propertyName,
            'address_line1' => $this->propertyAddress,
            'pin' => $this->propertyPin,
            'plot_number' => $this->plotNumber ?: null,
            'dag_number' => $this->dagNumber ?: null,
            'state' => 'West Bengal'
        ]);

        $this->selectedPropertyId = $prop->id;
        $this->propertyName = '';
        $this->propertyAddress = '';
        $this->propertyPin = '';

        session()->flash('message', "Complex '{$prop->name}' registered successfully!");
    }

    public function createTower(): void
    {
        $this->validate([
            'selectedPropertyId' => 'required|uuid',
            'towerName' => 'required|string',
            'towerFloors' => 'required|integer|min:1',
        ]);

        $bldg = Building::create([
            'property_id' => $this->selectedPropertyId,
            'name' => $this->towerName,
            'floors' => $this->towerFloors,
        ]);

        $this->selectedBuildingId = $bldg->id;
        $this->towerName = '';
        $this->towerFloors = 1;

        session()->flash('message', "Tower Block '{$bldg->name}' added successfully.");
    }

    public function createFlat(): void
    {
        $this->validate([
            'selectedBuildingId' => 'required|uuid',
            'flatNumber' => 'required|string',
            'flatFloor' => 'required|integer',
            'unitType' => 'required|string',
            'superArea' => 'required|numeric|min:10',
            'monthlyMaintenance' => 'required|numeric|min:0',
        ]);

        $unit = Unit::create([
            'building_id' => $this->selectedBuildingId,
            'flat_number' => $this->flatNumber,
            'floor' => $this->flatFloor,
            'unit_type' => $this->unitType,
            'super_built_up_area' => $this->superArea,
            'carpet_area' => $this->superArea * 0.8,
            'monthly_maintenance_amount' => $this->monthlyMaintenance,
            'occupancy_status' => 'Vacant',
            'ownership_type' => 'Owner',
        ]);

        // Increment unit count in building
        $bldg = Building::find($this->selectedBuildingId);
        if ($bldg) {
            $bldg->increment('units_count');
        }

        $this->flatNumber = '';
        $this->flatFloor = 0;

        session()->flash('message', "Flat '{$unit->flat_number}' registered inside {$bldg->name}.");
    }

    // TAB 3 ACTION: Register Resident and map to flat
    public function registerResident(): void
    {
        $rules = [
            'residentName' => 'required|string|min:3',
            'residentMobile' => 'required|string|min:10',
            'residentFlatId' => 'required|uuid',
            'residentType' => 'required|in:Owner,Tenant',
            'familyMembersCount' => 'required|integer|min:1|max:20',
            'residentIdType' => 'required|in:Aadhaar,PAN,Voter ID,Passport',
            'electricityConnectionType' => 'required|in:own_meter,submeter',
            'residentMeterNumber' => 'nullable|required_if:electricityConnectionType,own_meter|string|max:50',
            'residentSubmeterNumber' => 'nullable|required_if:electricityConnectionType,submeter|string|max:50',
        ];

        $idType = $this->residentIdType;
        if ($idType === 'Aadhaar') {
            $rules['residentIdNumber'] = ['required', 'string', 'regex:/^\d{4}-\d{4}-\d{4}$|^\d{12}$/'];
        } elseif ($idType === 'PAN') {
            $rules['residentIdNumber'] = ['required', 'string', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i'];
        } elseif ($idType === 'Voter ID') {
            $rules['residentIdNumber'] = ['required', 'string', 'regex:/^[A-Z]{3}[0-9]{7}$/i'];
        } elseif ($idType === 'Passport') {
            $rules['residentIdNumber'] = ['required', 'string', 'regex:/^[A-Z][0-9]{7}$/i'];
        } else {
            $rules['residentIdNumber'] = ['nullable', 'string', 'max:50'];
        }

        $messages = [
            'residentIdNumber.required' => "Please enter your {$idType} Number.",
            'residentIdNumber.regex' => match($idType) {
                'Aadhaar' => 'Aadhaar must be 12 digits (e.g. 1111-2222-3333 or 111122223333).',
                'PAN' => 'PAN must be 10 characters (e.g. ABCDE1234F).',
                'Voter ID' => 'Voter ID must be 10 characters (e.g. ABC1234567).',
                'Passport' => 'Passport must start with a letter followed by 7 digits (e.g. A1234567).',
                default => 'Invalid Verification ID number format.',
            },
            'residentMeterNumber.required_if' => 'Registered Meter Number is required when connection is Own Registered Meter.',
            'residentSubmeterNumber.required_if' => 'Sub-meter Number is required when connection is Sub-meter.',
        ];

        $this->validate($rules, $messages);

        // Resolve unit and building for address formatting
        $unit = Unit::with('building')->find($this->residentFlatId);
        $flatNo = $unit?->flat_number ?? 'N/A';
        $floorNo = $unit?->floor ?? 0;
        $bldgName = $unit?->building?->name ?? 'Block';
        $formattedAddress = "Flat {$flatNo}, Floor {$floorNo}, {$bldgName}";

        // 1. Create Person record
        $person = Person::create([
            'name' => $this->residentName,
            'mobile' => $this->residentMobile,
            'email' => $this->residentEmail ?: null,
            'family_members' => $this->familyMembersCount,
            'id_type' => $this->residentIdType,
            'id_number' => $this->residentIdNumber ? strtoupper(trim($this->residentIdNumber)) : null,
            'guardian_name' => $this->guardianName ?: null,
            'occupation' => $this->residentOccupation,
            'address' => $formattedAddress,
        ]);

        // 2. Link to Unit via Membership
        Membership::create([
            'unit_id' => $this->residentFlatId,
            'person_id' => $person->id,
            'membership_number' => 'MEM-' . rand(1000, 9999),
            'primary_owner' => ($this->residentType === 'Owner'),
            'membership_status' => 'active',
        ]);

        // Update unit occupancy status
        if ($unit) {
            $unit->update([
                'ownership_type' => $this->residentType,
                'occupancy_status' => ($this->residentType === 'Owner') ? 'Self Occupied' : 'Rented',
                'electricity_connection_type' => $this->electricityConnectionType,
                'meter_number' => $this->electricityConnectionType === 'own_meter' ? $this->residentMeterNumber : null,
                'submeter_number' => $this->electricityConnectionType === 'submeter' ? $this->residentSubmeterNumber : null,
            ]);
        }

        $this->residentName = '';
        $this->residentMobile = '';
        $this->residentEmail = '';
        $this->residentIdNumber = '';
        $this->familyMembersCount = 4;
        $this->electricityConnectionType = 'own_meter';
        $this->residentMeterNumber = '';
        $this->residentSubmeterNumber = '';

        session()->flash('message', "Resident registered and allocated to Flat '{$flatNo}' successfully.");
    }

    // TAB 4 ACTION: Assign Committee Position
    public function assignCommitteeRole(): void
    {
        $this->validate([
            'selectedPersonId' => 'required|uuid',
            'designation' => 'required|string',
        ]);

        // Deactivate old active positions for this role (e.g. only one Secretary at a time)
        CommitteeAppointment::where('designation', $this->designation)
            ->where('status', 'active')
            ->update(['status' => 'expired', 'end_date' => now()]);

        // Create new active appointment
        $appt = CommitteeAppointment::create([
            'person_id' => $this->selectedPersonId,
            'designation' => $this->designation,
            'start_date' => now(),
            'appointment_method' => $this->appointmentMethod,
            'status' => 'active'
        ]);

        session()->flash('message', "Resident assigned as '{$this->designation}' of the board committee.");
    }

    // TAB 5 ACTION: Document upload
    public function uploadDocument(): void
    {
        $this->validate([
            'docName' => 'required|string|min:3',
            'docCategory' => 'required|string',
        ]);

        $doc = Document::create([
            'name' => $this->docName,
            'category' => $this->docCategory,
            'description' => $this->docDescription ?: null,
            'file_path' => 'docs/' . strtolower(str_replace(' ', '_', $this->docName)) . '.pdf',
            'uploaded_by' => auth()->id()
        ]);

        $this->docName = '';
        $this->docDescription = '';

        session()->flash('message', "Document '{$doc->name}' archived in vault.");
    }

    // ORIGINAL ACTIONS (Proposals and checklist controls)
    public function selectProposal(string $id): void
    {
        $this->selectedProposalId = $id;
    }

    public function toggleChecklistItem(int $index): void
    {
        $proposal = Proposal::find($this->selectedProposalId);
        if ($proposal) {
            $checklist = $proposal->checklist;
            $checklist[$index]['checked'] = !$checklist[$index]['checked'];
            
            $proposal->checklist = $checklist;
            $proposal->save();
        }
    }

    public function createMilestone(): void
    {
        $this->validate([
            'newMilestoneProjectId' => 'required|uuid',
            'newMilestoneTitle' => 'required|string|min:3|max:100',
            'newMilestoneDescription' => 'nullable|string',
            'newMilestoneDueDate' => 'nullable|date',
            'newMilestoneBudget' => 'nullable|numeric|min:0',
        ]);

        $project = Project::findOrFail($this->newMilestoneProjectId);

        ProjectMilestone::create([
            'organization_id' => $project->organization_id,
            'project_id' => $this->newMilestoneProjectId,
            'title' => $this->newMilestoneTitle,
            'description' => $this->newMilestoneDescription,
            'due_date' => $this->newMilestoneDueDate ?: null,
            'budget_allocation' => (float)($this->newMilestoneBudget ?: 0.00),
            'progress_percentage' => 0,
            'status' => 'PENDING',
        ]);

        $this->newMilestoneTitle = '';
        $this->newMilestoneDescription = '';
        $this->newMilestoneDueDate = '';
        $this->newMilestoneBudget = '';
        $this->newMilestoneProjectId = '';

        session()->flash('message', 'New Project Milestone created successfully!');
    }

    public function toggleMilestoneStatus(string $milestoneId): void
    {
        $milestone = ProjectMilestone::find($milestoneId);
        if ($milestone) {
            if ($milestone->status === 'COMPLETED') {
                $milestone->update([
                    'status' => 'PENDING',
                    'progress_percentage' => 0
                ]);
            } else {
                $milestone->update([
                    'status' => 'COMPLETED',
                    'progress_percentage' => 100
                ]);
            }
            session()->flash('message', "Milestone '{$milestone->title}' status updated.");
        }
    }

    public function createProposal(): void
    {
        $rules = [
            'newTitle' => 'required|string|min:5|max:100',
            'newDescription' => 'required|string|min:10',
            'newBudget' => 'required|numeric|min:1000',
            'newExecutionDate' => 'required|date',
            'newDeadline' => 'required|date|after_or_equal:newExecutionDate',
            'newJustification' => 'required|string|min:10',
            'newAssignedSecretaryId' => 'required|uuid',
            'newInvolvedMembers' => 'array',
        ];

        if ($this->newDocument) {
            $rules['newDocument'] = 'file|mimes:pdf,png,jpg,jpeg,docx|max:10240';
        }

        $this->validate($rules);

        $documentPath = null;
        if ($this->newDocument) {
            $documentPath = $this->newDocument->store('proposals', 'public');
        }

        $p = Proposal::create([
            'title' => $this->newTitle,
            'description' => $this->newDescription,
            'budget' => (float)$this->newBudget,
            'status' => Proposal::STATUS_VERIFICATION,
            'checklist' => [
                ['description' => 'Verify structural load limits and safety constraints', 'checked' => false],
                ['description' => 'Confirm compliance with building board regulations', 'checked' => false],
                ['description' => 'Verify allocation matches the approved annual budget cap', 'checked' => false],
            ],
            'created_by' => auth()->id() ?: User::first()->id,
            'execution_date' => $this->newExecutionDate,
            'deadline' => $this->newDeadline,
            'assigned_secretary_id' => $this->newAssignedSecretaryId,
            'justification' => $this->newJustification,
            'involved_members' => $this->newInvolvedMembers,
            'document_path' => $documentPath,
        ]);

        $this->newTitle = '';
        $this->newDescription = '';
        $this->newBudget = '';
        $this->newExecutionDate = '';
        $this->newDeadline = '';
        $this->newAssignedSecretaryId = null;
        $this->newDocument = null;
        $this->newJustification = '';
        $this->newInvolvedMembers = [];
        $this->selectedProposalId = $p->id;

        session()->flash('message', 'New development proposal created and moved to Verification stage.');
    }

    public function verifyProposal(string $id): void
    {
        $proposal = Proposal::find($id);
        if ($proposal && $proposal->status === Proposal::STATUS_VERIFICATION) {
            $proposal->update(['status' => Proposal::STATUS_VERIFIED]);
            session()->flash('message', 'Proposal technical sign-off verified successfully.');
        }
    }

    public function forwardToCommittee(string $id): void
    {
        $proposal = Proposal::find($id);
        if ($proposal && $proposal->status === Proposal::STATUS_VERIFIED) {
            $proposal->update(['status' => Proposal::STATUS_COMMITTEE_REVIEW]);
            session()->flash('message', 'Proposal forwarded to board committee registry.');
        }
    }

    public function recommendProposal(string $id): void
    {
        $proposal = Proposal::find($id);
        if ($proposal && $proposal->status === Proposal::STATUS_COMMITTEE_REVIEW) {
            $proposal->update(['status' => Proposal::STATUS_RECOMMENDED]);
            session()->flash('message', 'Proposal recommended to President Review.');
        }
    }

    public function sendToPresident(string $id): void
    {
        $proposal = Proposal::find($id);
        if ($proposal && $proposal->status === Proposal::STATUS_RECOMMENDED) {
            $proposal->update(['status' => Proposal::STATUS_PRESIDENT_REVIEW]);
            session()->flash('message', 'Proposal sent to President Inbox.');
        }
    }

    public function saveCustomRole(): void
    {
        $orgId = auth()->user()->organization_id;

        $this->validate([
            'roleName' => 'required|string|min:3|max:50',
            'roleDescription' => 'nullable|string|max:250',
            'selectedPermissions' => 'array',
        ]);

        $role = Role::updateOrCreate(
            ['id' => $this->roleId],
            [
                'organization_id' => $orgId,
                'name' => $this->roleName,
                'description' => $this->roleDescription,
            ]
        );

        $role->permissions()->sync($this->selectedPermissions);

        $this->resetRoleForm();
        session()->flash('message', 'Custom role saved successfully.');
    }

    public function editCustomRole(string $id): void
    {
        $role = Role::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->roleDescription = $role->description ?? '';
        $this->selectedPermissions = $role->permissions()->pluck('permissions.id')->toArray();
    }

    public function deleteCustomRole(string $id): void
    {
        $role = Role::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        User::where('role_id', $role->id)->update(['role_id' => null]);
        $role->delete();
        session()->flash('message', 'Role deleted successfully.');
    }

    public function assignRoleToUser(string $userId, ?string $roleId): void
    {
        $user = User::where('organization_id', auth()->user()->organization_id)->findOrFail($userId);
        
        if ($user->role === 'president') {
            session()->flash('error', 'Cannot alter the supreme President role assignment.');
            return;
        }

        $user->update([
            'role_id' => $roleId ?: null
        ]);

        session()->flash('message', "Role assignment updated for {$user->name}.");
    }

    public function appointCommitteeOfficer(): void
    {
        $this->validate([
            'appointPersonId' => 'required|uuid',
            'appointRoleId' => 'required|uuid',
            'appointmentMethod' => 'required|string',
        ]);

        $orgId = auth()->user()->organization_id;
        $role = Role::where('organization_id', $orgId)->findOrFail($this->appointRoleId);
        $person = Person::where('organization_id', $orgId)->findOrFail($this->appointPersonId);

        // 1. Create the Committee Appointment
        CommitteeAppointment::create([
            'organization_id' => $orgId,
            'person_id' => $person->id,
            'role_id' => $role->id,
            'designation' => $role->name,
            'start_date' => $this->appointmentStartDate ?: now(),
            'appointment_method' => $this->appointmentMethod,
            'status' => 'active',
        ]);

        // 2. Assign the custom role to the User linked to this Person
        $user = User::where('person_id', $person->id)->first();
        if ($user && $user->role !== 'president') {
            $user->update([
                'role_id' => $role->id
            ]);
        }

        $this->appointPersonId = null;
        $this->appointRoleId = null;
        $this->appointmentStartDate = '';

        session()->flash('message', "{$person->name} has been successfully appointed as {$role->name} and assigned the corresponding system permissions.");
    }

    public function revokeCommitteeAppointment(string $appointmentId): void
    {
        $orgId = auth()->user()->organization_id;
        $appointment = CommitteeAppointment::where('organization_id', $orgId)->findOrFail($appointmentId);
        
        // Remove the custom role from the user linked to this person
        $user = User::where('person_id', $appointment->person_id)->first();
        if ($user && $user->role_id === $appointment->role_id) {
            $user->update(['role_id' => null]);
        }

        $appointment->update([
            'status' => 'inactive',
            'end_date' => now()
        ]);

        session()->flash('message', "Committee appointment revoked and user permissions cleared.");
    }

    public function resetRoleForm(): void
    {
        $this->roleId = null;
        $this->roleName = '';
        $this->roleDescription = '';
        $this->selectedPermissions = [];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $orgId = auth()->user()->organization_id;

        $proposals = Proposal::orderBy('created_at', 'desc')->get();
        $selectedProposal = $this->selectedProposalId ? Proposal::find($this->selectedProposalId) : null;
        
        // Ledger Audit mapping
        $ledger = JournalEntry::orderBy('created_at', 'desc')->get();

        // Expansions Directory values
        $properties = Property::orderBy('name', 'asc')->get();
        $buildings = Building::orderBy('name', 'asc')->get();
        $units = Unit::with(['building', 'memberships.person', 'maintenanceEntries.person', 'maintenanceEntries.recorder'])->orderBy('flat_number', 'asc')->get();
        $persons = Person::orderBy('name', 'asc')->get();
        
        // Committee Appointment mappings (tenant-scoped)
        $committeeAppointments = CommitteeAppointment::with(['person', 'role'])
            ->where('organization_id', $orgId)
            ->where('status', 'active')
            ->get();
            
        $documents = Document::orderBy('created_at', 'desc')->get();

        // RBAC listings
        $customRoles = Role::where('organization_id', $orgId)->orderBy('name', 'asc')->get();
        $orgUsers = User::where('organization_id', $orgId)
            ->where('role', '!=', 'superadmin')
            ->orderBy('name', 'asc')
            ->get();
        $allPermissions = Permission::orderBy('category', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Dynamic metrics
        $kpis = [
            'proposals' => Proposal::count(),
            'verification' => Proposal::where('status', Proposal::STATUS_VERIFICATION)->count(),
            'pending_approval' => Proposal::where('status', Proposal::STATUS_PRESIDENT_REVIEW)->count(),
            'active_projects' => Project::count(),
            
            // New premises metrics
            'total_flats' => Unit::count(),
            'occupancy_rate' => Unit::count() > 0 ? round((Unit::where('occupancy_status', '!=', 'Vacant')->count() / Unit::count()) * 100) : 0,
            'total_residents' => Person::count(),
            'outstanding_dues' => Unit::sum('outstanding_amount'),
        ];

        $buildingCashBills = BuildingCashBill::with(['responsiblePerson', 'unit', 'recorder', 'proposal', 'project', 'milestone'])
            ->orderBy('created_at', 'desc')
            ->get();
        $projects = \App\Modules\Execution\Models\Project::orderBy('title', 'asc')->get();
        $milestones = \App\Modules\Execution\Models\ProjectMilestone::orderBy('title', 'asc')->get();

        $orgId = app(\App\Services\TenantManager::class)->getTenantId() ?? Organization::first()?->id;
        $inventoryQuery = \App\Models\InventoryItem::where('organization_id', $orgId);
        if ($this->inventorySearch) {
            $s = '%' . $this->inventorySearch . '%';
            $inventoryQuery->where(function($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('sku', 'like', $s)
                  ->orWhere('storage_location', 'like', $s);
            });
        }
        if ($this->inventoryCategoryFilter !== 'all') {
            $inventoryQuery->where('category', $this->inventoryCategoryFilter);
        }
        $inventoryItems = $inventoryQuery->orderBy('name', 'asc')->get();

        $balanceSheetData = $this->getBalanceSheetData();

        $auditLogQuery = \App\Modules\TenantIdentity\Models\AuditLog::where('organization_id', $orgId);
        if ($this->auditSearch) {
            $s = '%' . $this->auditSearch . '%';
            $auditLogQuery->where(function($q) use ($s) {
                $q->where('auditable_type', 'like', $s)
                  ->orWhere('action', 'like', $s)
                  ->orWhere('reason', 'like', $s)
                  ->orWhere('ip_address', 'like', $s);
            });
        }
        if ($this->auditActionFilter !== 'all') {
            $auditLogQuery->where('action', $this->auditActionFilter);
        }
        $auditLogs = $auditLogQuery->orderBy('created_at', 'desc')->limit(50)->get();

        return view('livewire.control-center', [
            'proposals' => $proposals,
            'selectedProposal' => $selectedProposal,
            'projects' => $projects,
            'milestones' => $milestones,
            'ledger' => $ledger,
            'buildingCashBills' => $buildingCashBills,
            'inventoryItems' => $inventoryItems,
            'balanceSheetData' => $balanceSheetData,
            'auditLogs' => $auditLogs,
            
            // Premise setups
            'properties' => $properties,
            'buildings' => $buildings,
            'units' => $units,
            'persons' => $persons,
            'committeeAppointments' => $committeeAppointments,
            'documents' => $documents,
            
            // RBAC configurations
            'customRoles' => $customRoles,
            'orgUsers' => $orgUsers,
            'allPermissions' => $allPermissions,
            
            'kpis' => $kpis,
            'isSecretaryWorkspace' => $this->isSecretaryWorkspace(),
        ])->layout('components.layouts.app');
    }
}
