<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Organization Directory</h2>
            <p class="text-sm text-slate-500">Manage tenant accounts, edit subdomain configurations, and switch database contexts during local development.</p>
        </div>
    </div>

    <!-- Alert Message -->
    @if(session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-sm flex items-center gap-2 shadow-sm">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Organizations Directory Table -->
        <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-800">Organizations Directory</h3>
                @if(session()->has('selected_org_id'))
                    <button wire:click="resetTenantContext" class="px-2.5 py-1 text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition-colors">
                        Reset Context to Subdomain
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-600">
                    <thead class="bg-slate-100/80 text-slate-500 uppercase text-[8px] tracking-widest border-b border-slate-200">
                        <tr>
                            <th class="p-3">Organization Name</th>
                            <th class="p-3">Subdomain</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center">Active Context</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/60">
                        @foreach($organizations as $org)
                            <tr class="hover:bg-slate-50/40 transition-colors {{ $activeOrgId === $org->id ? 'bg-blue-50/20' : '' }}">
                                <td class="p-3 font-semibold text-slate-800">{{ $org->name }}</td>
                                <td class="p-3 font-mono text-[10px] text-slate-500">{{ $org->subdomain }}.bpdes.app</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $org->status === 'active' ? 'bg-emerald-500/10 text-emerald-700 border border-emerald-500/20' : 'bg-red-500/10 text-red-700 border border-red-500/20' }} uppercase">
                                        {{ $org->status }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    @if($activeOrgId === $org->id)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    @else
                                        <button wire:click="switchOrganization('{{ $org->id }}')" 
                                                class="px-2.5 py-1 text-[10px] font-bold bg-white text-slate-600 border border-slate-200 rounded hover:border-blue-500 hover:text-blue-600 transition-all shadow-sm">
                                            Switch to
                                        </button>
                                    @endif
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button wire:click="editOrganization('{{ $org->id }}')" class="text-blue-600 hover:text-blue-500 font-semibold">Edit</button>
                                    <button onclick="confirm('Are you sure you want to delete this organization?') || event.stopImmediatePropagation()" 
                                            wire:click="deleteOrganization('{{ $org->id }}')" class="text-red-600 hover:text-red-500 font-semibold">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Create/Edit Form -->
        <div class="lg:col-span-1">
            <div class="glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">{{ $isEditing ? 'Edit Organization' : 'Create Organization' }}</h3>
                <form wire:submit.prevent="{{ $isEditing ? 'updateOrganization' : 'createOrganization' }}" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Organization Name</label>
                        <input type="text" wire:model="name" placeholder="e.g. Royal Palm Society" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20" required>
                        @error('name') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Subdomain</label>
                        <div class="flex">
                            <input type="text" wire:model="subdomain" placeholder="e.g. royalpalm" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20" required>
                        </div>
                        <span class="text-[9px] text-slate-400 mt-1 block">Alpha characters only. Used for routing (e.g. <code>subdomain.bpdes.app</code>).</span>
                        @error('subdomain') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Account Status</label>
                        <select wire:model="status" 
                                class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        @error('status') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 flex gap-2">
                        @if($isEditing)
                            <button type="button" wire:click="resetFields" class="w-1/2 py-2.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition-colors">
                                Cancel
                            </button>
                        @endif
                        <button type="submit" class="py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all {{ $isEditing ? 'w-1/2' : 'w-full' }}">
                            {{ $isEditing ? 'Save Changes' : 'Create Organization' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
