<?php

namespace App\Livewire\Auth;

use App\Models\Organization;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Person;
use App\Models\Membership;
use App\Models\User;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class Register extends Component
{
    // Credentials
    public string $name = '';
    public string $mobile = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Selector parameters
    public int $familyMembers = 1;
    public bool $isProfessional = true;
    public bool $carParking = false;
    
    // Flat details
    public string $flatNumber = '';
    public string $selectedBhk = '3BHK';
    public int $selectedFloor = 2;
    public ?string $selectedOrgId = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:100',
            'mobile' => 'required|string|min:10|max:15',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'familyMembers' => 'required|integer|min:1|max:20',
            'flatNumber' => 'required|string|min:1|max:10',
            'selectedOrgId' => 'required|uuid',
            'selectedBhk' => 'required|string',
            'selectedFloor' => 'required|integer|min:0',
        ];
    }

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectUser();
        }

        // Resolve active organization context
        $this->selectedOrgId = session('selected_org_id') ?? Organization::value('id');
    }

    public function selectBhk(string $bhk): void
    {
        $this->selectedBhk = $bhk;
    }

    public function selectFloor(int $floor): void
    {
        $this->selectedFloor = $floor;
    }

    public function registerResident(): mixed
    {
        $this->validate();

        // 1. Establish database connection context
        $tenantManager = App::make(TenantManager::class);
        $tenantManager->setTenantId($this->selectedOrgId);

        // 2. Resolve default Building (Tower A) or create one
        $bldg = Building::where('organization_id', $this->selectedOrgId)->first();
        if (!$bldg) {
            // Find first property or create a dummy one
            $prop = \App\Models\Property::where('organization_id', $this->selectedOrgId)->first();
            if (!$prop) {
                $org = Organization::find($this->selectedOrgId);
                $prop = \App\Models\Property::create([
                    'organization_id' => $this->selectedOrgId,
                    'name' => ($org->name ?? 'Society') . ' Enclave',
                    'address_line1' => 'Plot 10, Action Area I, New Town',
                    'locality' => 'New Town',
                    'municipality' => 'NKDA',
                    'state' => 'West Bengal',
                    'pin' => '700156',
                ]);
            }
            $bldg = Building::create([
                'organization_id' => $this->selectedOrgId,
                'property_id' => $prop->id,
                'name' => 'Tower A',
                'floors' => 10,
            ]);
        }

        // 3. Resolve flat Unit or create on-the-fly
        $unit = Unit::where('organization_id', $this->selectedOrgId)
            ->where('flat_number', $this->flatNumber)
            ->first();

        if (!$unit) {
            $unit = Unit::create([
                'organization_id' => $this->selectedOrgId,
                'building_id' => $bldg->id,
                'flat_number' => $this->flatNumber,
                'floor' => $this->selectedFloor,
                'unit_type' => $this->selectedBhk,
                'super_built_up_area' => $this->selectedBhk === '3BHK' ? 1500.00 : 1100.00,
                'carpet_area' => $this->selectedBhk === '3BHK' ? 1200.00 : 900.00,
                'ownership_type' => 'Owner',
                'occupancy_status' => 'Self Occupied',
                'monthly_maintenance_amount' => $this->selectedBhk === '3BHK' ? 3500.00 : 2600.00,
                'outstanding_amount' => 0.00,
            ]);
        } else {
            $unit->update([
                'ownership_type' => 'Owner',
                'occupancy_status' => 'Self Occupied',
                'unit_type' => $this->selectedBhk,
                'floor' => $this->selectedFloor,
            ]);
        }

        // 4. Create Person registry profile
        $person = Person::create([
            'organization_id' => $this->selectedOrgId,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'gender' => 'Other',
            'family_members' => $this->familyMembers,
            'is_professional' => $this->isProfessional,
            'car_parking' => $this->carParking,
            'address' => "Flat {$this->flatNumber}, Floor {$this->selectedFloor}, {$bldg->name}",
        ]);

        // 5. Link Person to flat Unit via Membership
        Membership::create([
            'organization_id' => $this->selectedOrgId,
            'unit_id' => $unit->id,
            'person_id' => $person->id,
            'membership_number' => 'MEM-' . rand(10000, 99999),
            'primary_owner' => true,
            'membership_status' => 'active',
        ]);

        // 6. Create User login credentials
        $user = User::create([
            'organization_id' => $this->selectedOrgId,
            'person_id' => $person->id,
            'unit_id' => $unit->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'role' => 'resident',
        ]);

        // 7. Authenticate and redirect
        Auth::login($user);
        session(['selected_org_id' => $this->selectedOrgId]);

        return redirect()->to('/resident/dashboard');
    }

    protected function redirectUser(): void
    {
        $user = Auth::user();
        redirect()->to($user->homeRoute());
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $organizations = Organization::orderBy('name', 'asc')->get();

        return view('livewire.auth.register', [
            'organizations' => $organizations,
        ])->layout('components.layouts.app', ['title' => 'Resident Registration']);
    }
}
