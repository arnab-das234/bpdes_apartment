<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantManager;
use Livewire\Component;
use Illuminate\Support\Str;

class OrganizationCrud extends Component
{
    public $name = '';
    public $subdomain = '';
    public $status = 'active';
    public ?string $editingOrgId = null;
    public bool $isEditing = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:100',
            'subdomain' => 'required|string|alpha|min:3|max:20|unique:organizations,subdomain,' . ($this->editingOrgId ?? 'NULL') . ',id',
            'status' => 'required|in:active,suspended',
        ];
    }

    public function createOrganization(): void
    {
        $this->validate();

        // Create the organization (this model does not have tenant scopes, so it works globally)
        $org = Organization::create([
            'name' => $this->name,
            'subdomain' => strtolower($this->subdomain),
            'status' => $this->status,
            'settings' => ['currency' => 'INR']
        ]);

        // Create a default administrator user for this new organization
        User::create([
            'organization_id' => $org->id,
            'name' => 'Admin ' . $org->name,
            'email' => 'admin@' . $org->subdomain . '.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->resetFields();
        session()->flash('message', "Organization '{$org->name}' created successfully with a default administrator account.");
    }

    public function editOrganization(string $id): void
    {
        $org = Organization::find($id);
        if ($org) {
            $this->editingOrgId = $org->id;
            $this->name = $org->name;
            $this->subdomain = $org->subdomain;
            $this->status = $org->status;
            $this->isEditing = true;
        }
    }

    public function updateOrganization(): void
    {
        $this->validate();

        $org = Organization::find($this->editingOrgId);
        if ($org) {
            $org->update([
                'name' => $this->name,
                'subdomain' => strtolower($this->subdomain),
                'status' => $this->status,
            ]);

            $this->resetFields();
            session()->flash('message', 'Organization updated successfully.');
        }
    }

    public function deleteOrganization(string $id): void
    {
        $org = Organization::find($id);
        if ($org) {
            $orgName = $org->name;
            $org->delete();

            // Clear session if active
            if (session('selected_org_id') === $id) {
                session()->forget('selected_org_id');
            }

            session()->flash('message', "Organization '{$orgName}' and all related records deleted successfully.");
        }
    }

    /**
     * Switch current tenant database context in the session.
     */
    public function switchOrganization(string $id): mixed
    {
        $org = Organization::find($id);
        if ($org) {
            session(['selected_org_id' => $org->id]);
            session()->flash('message', "Context switched to '{$org->name}' successfully.");
            return redirect()->to('/control-center');
        }
        return null;
    }

    /**
     * Reset session switcher to default resolution.
     */
    public function resetTenantContext(): mixed
    {
        session()->forget('selected_org_id');
        session()->flash('message', 'Context switched back to default subdomain resolution.');
        return redirect()->to('/control-center');
    }

    public function resetFields(): void
    {
        $this->name = '';
        $this->subdomain = '';
        $this->status = 'active';
        $this->editingOrgId = null;
        $this->isEditing = false;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $organizations = Organization::orderBy('name', 'asc')->get();
        $activeOrgId = session('selected_org_id') ?? Organization::value('id');

        return view('livewire.organization-crud', [
            'organizations' => $organizations,
            'activeOrgId' => $activeOrgId,
        ])->layout('components.layouts.app');
    }
}
