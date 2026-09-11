<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\User;
use App\Models\Role;
use Livewire\Component;

class SuperadminDashboard extends Component
{
    // Role CRUD Details
    public ?string $selectedOrgId = null;
    public ?string $roleId = null;
    public string $roleName = '';
    public string $roleDescription = '';
    public array $selectedPermissions = [];


    // Org Details
    public string $orgName = '';
    public string $orgShortName = '';
    public string $subdomain = '';
    public string $registrationType = 'Apartment Owners Association';
    public string $registrationAct = 'West Bengal Apartment Ownership Act, 1972';
    public string $registrationNumber = '';
    public string $officialEmail = '';
    public string $officialMobile = '';

    // Admin Details
    public string $adminName = '';
    public string $adminEmail = '';
    public string $adminPassword = '';
    public string $adminRole = 'president'; // president, admin

    protected function rules(): array
    {
        return [
            'orgName' => 'required|string|min:3|max:100',
            'subdomain' => 'required|string|alpha|min:3|max:20|unique:organizations,subdomain',
            'registrationType' => 'required|string',
            'registrationAct' => 'required|string',
            'registrationNumber' => 'required|string',
            'officialEmail' => 'required|email|unique:organizations,official_email',
            'officialMobile' => 'required|string|min:10|max:15',
            
            'adminName' => 'required|string|min:3|max:50',
            'adminEmail' => 'required|email|unique:users,email',
            'adminPassword' => 'required|string|min:6',
            'adminRole' => 'required|in:president,admin',
        ];
    }

    public function registerOrganization(): void
    {
        $this->validate();

        // 1. Create Organization
        $org = Organization::create([
            'name' => $this->orgName,
            'short_name' => $this->orgShortName ?: null,
            'subdomain' => strtolower($this->subdomain),
            'registration_type' => $this->registrationType,
            'registration_number' => $this->registrationNumber,
            'registration_authority' => 'Government Registration Authority',
            'registration_act' => $this->registrationAct,
            'official_email' => $this->officialEmail,
            'official_mobile' => $this->officialMobile,
            'status' => 'active',
            'settings' => ['currency' => 'INR']
        ]);

        // 2. Create Organization Admin/President User Account
        User::create([
            'organization_id' => $org->id,
            'name' => $this->adminName,
            'email' => $this->adminEmail,
            'password' => bcrypt($this->adminPassword),
            'role' => $this->adminRole,
        ]);

        // 3. Pre-seed default settings and return success
        $this->resetFields();
        session()->flash('message', "Society '{$org->name}' registered successfully! Credentials shared for '{$this->adminEmail}'.");
    }

    protected function resetFields(): void
    {
        $this->orgName = '';
        $this->orgShortName = '';
        $this->subdomain = '';
        $this->registrationNumber = '';
        $this->officialEmail = '';
        $this->officialMobile = '';
        
        $this->adminName = '';
        $this->adminEmail = '';
        $this->adminPassword = '';
    }

    public function deleteOrganization(string $id): void
    {
        $org = Organization::find($id);
        if ($org) {
            $orgName = $org->name;
            $org->delete();
            session()->flash('message', "Organization '{$orgName}' and all resident/premises records purged.");
        }
    }

    public function selectOrgForRoles(string $orgId): void
    {
        $this->selectedOrgId = $orgId;
        $this->resetRoleForm();
    }

    public function editRole(string $id): void
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->roleDescription = $role->description ?? '';
        $this->selectedPermissions = $role->permissions()->pluck('permissions.id')->toArray();
    }

    public function saveRole(): void
    {
        if (!$this->selectedOrgId) {
            $this->selectedOrgId = Organization::value('id');
        }

        $this->validate([
            'selectedOrgId' => 'required|uuid',
            'roleName' => 'required|string|min:3|max:50',
            'roleDescription' => 'nullable|string|max:250',
            'selectedPermissions' => 'array',
        ]);

        $role = Role::updateOrCreate(
            ['id' => $this->roleId],
            [
                'organization_id' => $this->selectedOrgId,
                'name' => $this->roleName,
                'description' => $this->roleDescription,
            ]
        );

        $role->permissions()->sync($this->selectedPermissions);

        $this->resetRoleForm();
        session()->flash('message', 'Custom role saved and registered successfully.');
    }

    public function deleteRole(string $id): void
    {
        $role = Role::findOrFail($id);
        // Clean mapping links first
        User::where('role_id', $role->id)->update(['role_id' => null]);
        $role->delete();
        session()->flash('message', 'Role purged from system.');
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
        $organizations = Organization::orderBy('name', 'asc')->get();

        if (!$this->selectedOrgId && $organizations->isNotEmpty()) {
            $this->selectedOrgId = $organizations->first()->id;
        }

        $roles = $this->selectedOrgId 
            ? Role::where('organization_id', $this->selectedOrgId)->orderBy('name', 'asc')->get() 
            : collect();

        $allPermissions = \App\Models\Permission::orderBy('category', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.superadmin-dashboard', [
            'organizations' => $organizations,
            'roles' => $roles,
            'allPermissions' => $allPermissions,
        ])->layout('components.layouts.app');
    }
}
