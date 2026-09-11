<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Superadmin Control Center</h2>
            <p class="text-sm text-slate-500">Register new building complexes/societies and configure primary admin accounts globally.</p>
        </div>
        <div>
            <a href="/logout" class="px-3 py-1.5 text-xs font-semibold rounded bg-red-500/10 hover:bg-red-500/20 text-red-600 border border-red-500/20 transition-all">
                Sign Out
            </a>
        </div>
    </div>

    <!-- Session Alerts -->
    @if(session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-sm flex items-center gap-2 shadow-sm animate-fade-in">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Directory List (2 Columns) -->
        <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Registered Building Societies & Associations</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-600">
                    <thead class="bg-slate-100/80 text-slate-500 uppercase text-[8px] tracking-widest border-b border-slate-200">
                        <tr>
                            <th class="p-3">Legal Name</th>
                            <th class="p-3">Registration Info</th>
                            <th class="p-3">Routing Subdomain</th>
                            <th class="p-3">Official Contact</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60">
                        @forelse($organizations as $org)
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="p-3">
                                    <div class="font-semibold text-slate-800">{{ $org->name }}</div>
                                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $org->registration_type }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="text-slate-700 font-semibold">{{ $org->registration_number }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $org->registration_act }}</div>
                                </td>
                                <td class="p-3 font-mono text-[10px] text-slate-500">{{ $org->subdomain }}.bpdes.app</td>
                                <td class="p-3">
                                    <div class="text-slate-700">{{ $org->official_email }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $org->official_mobile }}</div>
                                </td>
                                <td class="p-3 text-right">
                                    <button onclick="confirm('Are you sure you want to delete this organization? This deletes all flats, ledger records, and documents!') || event.stopImmediatePropagation()" 
                                            wire:click="deleteOrganization('{{ $org->id }}')" 
                                            class="text-red-600 hover:text-red-500 font-semibold">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-400 italic">No organizations registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Registration Form (1 Column) -->
        <div class="lg:col-span-1 flex flex-col gap-6">
            <div class="glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Register New Society</h3>
                <form wire:submit.prevent="registerOrganization" class="space-y-4">
                    
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-1 mb-2">1. Organization Profile</h4>
                    
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Organization Name</label>
                        <input type="text" wire:model="orgName" placeholder="e.g. Royal Palm Co-Operative Society" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        @error('orgName') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Short Name</label>
                            <input type="text" wire:model="orgShortName" placeholder="e.g. Royal Palm" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Routing Subdomain</label>
                            <input type="text" wire:model="subdomain" placeholder="e.g. royalpalm" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            @error('subdomain') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Registration Type</label>
                        <select wire:model="registrationType" 
                                class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                            <option value="Apartment Owners Association">Apartment Owners Association</option>
                            <option value="Cooperative Housing Society">Cooperative Housing Society</option>
                            <option value="Registered Trust / Society">Registered Trust / Society</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Applicable Act</label>
                        <input type="text" wire:model="registrationAct" placeholder="e.g. West Bengal Apartment Ownership Act, 1972" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Registration/Form Number</label>
                        <input type="text" wire:model="registrationNumber" placeholder="e.g. WB/AOA/2026/0014" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        @error('registrationNumber') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Official Email</label>
                            <input type="email" wire:model="officialEmail" placeholder="admin@palm.com" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            @error('officialEmail') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Official Mobile</label>
                            <input type="text" wire:model="officialMobile" placeholder="9876543210" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            @error('officialMobile') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-1 mt-4 mb-2">2. Administrative User Credentials</h4>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Admin Member Full Name</label>
                        <input type="text" wire:model="adminName" placeholder="e.g. President Name" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        @error('adminName') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Admin User Email (Login)</label>
                        <input type="email" wire:model="adminEmail" placeholder="president@palm.in" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        @error('adminEmail') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Password</label>
                            <input type="password" wire:model="adminPassword" placeholder="••••••••" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            @error('adminPassword') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Office Role</label>
                            <select wire:model="adminRole" 
                                    class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                                <option value="president">President</option>
                                <option value="admin">Admin Bearer</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-4 py-3 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                        Register & Seed Tenant Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Role-Based Access Control (RBAC) Settings (Full Row) -->
    <div class="mt-8 border-t border-slate-200 pt-8">
        <h2 class="text-base font-extrabold text-slate-900 tracking-tight mb-2">Role-Based Access Control (RBAC) Settings</h2>
        <p class="text-xs text-slate-500 mb-6">Create, modify, and manage custom roles and permission maps for specific societies.</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            <!-- Form Panel -->
            <div class="lg:col-span-1 glass-panel rounded-xl shadow-sm h-[560px] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 shrink-0">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                        {{ $roleId ? 'Edit Custom Role' : 'Create Custom Role' }}
                    </h3>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Target Organization</label>
                        <select wire:model.live="selectedOrgId" 
                                class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedOrgId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Role Name</label>
                        <input type="text" wire:model="roleName" placeholder="e.g. Treasurer"
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        @error('roleName') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Description</label>
                        <textarea wire:model="roleDescription" placeholder="Description of capabilities..."
                                  class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500 h-16"></textarea>
                        @error('roleDescription') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-2">Permissions</label>
                        <div class="space-y-2 max-h-56 overflow-y-auto border border-slate-200 p-2.5 rounded bg-slate-50">
                            @php $currentCategory = ''; @endphp
                            @foreach($allPermissions as $permission)
                                @if($currentCategory !== $permission->category)
                                    @php $currentCategory = $permission->category; @endphp
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-2 first:mt-0 border-b border-slate-100 pb-0.5 mb-1">{{ $currentCategory }}</div>
                                @endif
                                <label class="flex items-center gap-2 text-[11px] text-slate-700 cursor-pointer pl-1">
                                    <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->id }}"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedPermissions') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex gap-2 border-t border-slate-100 bg-white px-6 py-4 shrink-0">
                    <button type="button" wire:click="saveRole"
                            class="flex-1 py-2.5 rounded bg-blue-600 text-white font-bold text-xs hover:bg-blue-500 shadow-sm transition-all">
                        {{ $roleId ? 'Update Role' : 'Create Role' }}
                    </button>
                    @if($roleId)
                        <button type="button" wire:click="resetRoleForm"
                                class="py-2.5 px-3 rounded bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300 transition-all">
                            Cancel
                        </button>
                    @endif
                </div>
            </div>

            <!-- List Panel -->
            <div class="lg:col-span-2 glass-panel rounded-xl shadow-sm h-[560px] flex flex-col overflow-hidden">
                <div class="flex justify-between items-center border-b border-slate-100 px-6 py-4 shrink-0">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Configured Custom Roles</h3>
                    <div>
                        <select wire:model.live="selectedOrgId" 
                                class="text-xs p-1.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none">
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }} (Roles)</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6">
                    @if($roles->isEmpty())
                        <p class="text-xs text-slate-500 italic py-6 text-center">No custom roles defined for this organization yet.</p>
                    @else
                        <div class="divide-y divide-slate-200/60">
                        @foreach($roles as $role)
                            <div class="py-4 flex justify-between items-start gap-4 hover:bg-slate-50/40 px-2 rounded-lg transition-colors">
                                <div class="space-y-1 flex-1 min-w-0 w-full">
                                    <h4 class="text-xs font-black text-slate-900">{{ $role->name }}</h4>
                                    <p class="text-[11px] text-slate-500">{{ $role->description ?: 'No description provided.' }}</p>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach($role->permissions as $permission)
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 text-[9px] font-semibold border border-blue-100/60">
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <button type="button" wire:click="editRole('{{ $role->id }}')"
                                            class="text-[10px] font-bold text-blue-600 hover:text-blue-500 hover:underline">
                                        Edit
                                    </button>
                                    <button type="button" wire:click="deleteRole('{{ $role->id }}')"
                                            class="text-[10px] font-bold text-red-600 hover:text-red-500 hover:underline"
                                            onclick="confirm('Are you sure you want to delete this role?') || event.stopImmediatePropagation()">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
