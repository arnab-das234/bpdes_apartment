<div>
    @php
        $proposalTone = [
            'DRAFT' => 'bg-slate-100 text-slate-700 border-slate-200',
            'VERIFICATION' => 'bg-slate-100 text-slate-700 border-slate-200',
            'VERIFIED' => 'bg-zinc-100 text-zinc-700 border-zinc-200',
            'COMMITTEE_REVIEW' => 'bg-stone-100 text-stone-700 border-stone-200',
            'RECOMMENDED' => 'bg-amber-100 text-amber-700 border-amber-200',
            'PRESIDENT_REVIEW' => 'bg-orange-100 text-orange-700 border-orange-200',
            'APPROVED' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'REJECTED' => 'bg-rose-100 text-rose-700 border-rose-200',
        ];
    @endphp

    <!-- Tabs Header Menu (Cashier & Treasurer Desk Navigation) -->
    <div class="flex border-b border-slate-200 mb-8 overflow-x-auto shrink-0 scrollbar-none gap-2">
        <button wire:click="changeTab('overview')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'overview' ? 'border-slate-700 text-slate-900 font-black' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Overview Dashboard
        </button>
        <button wire:click="changeTab('maintenance')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'maintenance' ? 'border-slate-700 text-slate-900 font-black' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Maintenance Contribution
        </button>
        <button wire:click="changeTab('documents')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'documents' ? 'border-slate-700 text-slate-900 font-black' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Document Vault (Form A / 1)
        </button>
        <button wire:click="changeTab('cash-release-bills')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'cash-release-bills' ? 'border-slate-700 text-slate-900 font-black' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            💸 Cash Release & Expense Desk
        </button>
        <button wire:click="changeTab('audit-logs')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'audit-logs' ? 'border-slate-700 text-slate-900 font-black' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            🔍 Audit Trail & Security Logs
        </button>
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

    <!-- TAB 1: OVERVIEW DASHBOARD -->
    @if($activeTab === 'overview')
        <!-- Top KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-card p-6 rounded-xl relative overflow-hidden">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Proposals</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpis['proposals'] }}</h3>
                <p class="text-xs text-slate-500 mt-1 font-semibold">All stage checkpoints</p>
            </div>
            <div class="glass-card p-6 rounded-xl relative overflow-hidden glow-amber">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Under Verification</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpis['verification'] }}</h3>
                <p class="text-xs text-amber-600 mt-1 font-semibold">Requires signoff</p>
            </div>
            <div class="glass-card p-6 rounded-xl relative overflow-hidden glow-amber">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">President's Review</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpis['pending_approval'] }}</h3>
                <p class="text-xs text-amber-600 mt-1 font-semibold">Awaiting sign-off</p>
            </div>
            <div class="glass-card p-6 rounded-xl relative overflow-hidden glow-emerald">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Projects</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpis['active_projects'] }}</h3>
                <p class="text-xs text-emerald-600 mt-1 font-semibold">Under execution</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Proposals List -->
            <div class="lg:col-span-1 flex flex-col gap-6">
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="relative overflow-hidden border-b border-sky-100 bg-gradient-to-r from-sky-50 via-blue-50 to-cyan-50 px-4 py-3">
                        <div class="absolute right-0 top-0 h-14 w-20 rounded-bl-full bg-sky-200/35"></div>
                        <div class="relative flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-sky-200 bg-white text-sky-600 shadow-sm">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 19h14M5 5h14M5 5v14M19 5v14" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-sky-500">Planning pipeline</p>
                                    <h3 class="mt-0.5 text-sm font-black leading-tight text-slate-900">Development Proposals</h3>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center rounded border border-sky-200 bg-white/80 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider text-sky-600">Verify</span>
                                        <span class="inline-flex items-center rounded border border-indigo-200 bg-indigo-50/80 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider text-indigo-600">Review</span>
                                        <span class="inline-flex items-center rounded border border-cyan-200 bg-cyan-50/80 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wider text-cyan-600">Approve</span>
                                    </div>
                                </div>
                            </div>
                            <div class="relative shrink-0 rounded-lg border border-sky-100 bg-white/90 px-3 py-2 text-center shadow-sm">
                                <span class="block text-lg font-black leading-none text-sky-700">{{ $kpis['proposals'] }}</span>
                                <span class="mt-0.5 block text-[8px] font-bold uppercase tracking-widest text-slate-400">Total</span>
                            </div>
                        </div>
                    </div>

                    <!-- Proposals Container (Compact Height with Scroll) -->
                    <div class="space-y-2 p-3 bg-white max-h-[280px] overflow-y-auto pr-1.5">
                        @forelse($proposals as $p)
                            <div wire:click="selectProposal('{{ $p->id }}')" 
                                 class="group p-2.5 rounded-lg cursor-pointer border transition-all {{ $selectedProposalId === $p->id ? 'bg-sky-50/70 border-sky-400 shadow-2xs' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-black text-slate-900 truncate leading-snug" title="{{ $p->title }}">{{ $p->title }}</h4>
                                        <p class="text-[10px] text-slate-500 mt-0.5 truncate leading-snug">{{ $p->description }}</p>
                                    </div>
                                    <span class="px-1.5 py-0.5 text-[8px] font-black rounded border uppercase shrink-0 {{ $proposalTone[$p->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">{{ str_replace('_', ' ', $p->status) }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-1.5 mt-1.5 text-[10px]">
                                    <div class="rounded bg-slate-50 border border-slate-200/80 px-2 py-0.5">
                                        <span class="block text-[7px] uppercase tracking-widest font-bold text-slate-400 leading-none">Budget</span>
                                        <strong class="block text-slate-900 text-[10px] font-black mt-0.5 leading-none">{{ format_indian_currency($p->budget) }}</strong>
                                    </div>
                                    <div class="rounded bg-slate-50 border border-slate-200/80 px-2 py-0.5 text-right">
                                        <span class="block text-[7px] uppercase tracking-widest font-bold text-slate-400 leading-none">Created</span>
                                        <strong class="block text-slate-900 text-[10px] font-bold mt-0.5 leading-none">{{ $p->created_at ? $p->created_at->format('M d') : 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-6">No proposals registered.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Create Proposal Form -->
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <div class="px-6 py-4 bg-sky-50 border-b border-sky-100">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-sky-500">New request</p>
                        <h3 class="text-sm font-extrabold mt-1 text-sky-900">Create New Proposal</h3>
                    </div>
                    <div class="p-6">
                    <form wire:submit.prevent="createProposal" class="space-y-4" enctype="multipart/form-data">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Proposal Title</label>
                            <input type="text" wire:model="newTitle" placeholder="e.g. Club House Painting & Renovation" 
                                   class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-slate-500" required>
                            @error('newTitle') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Description</label>
                            <textarea wire:model="newDescription" rows="3" placeholder="Provide full scope of development work..." 
                                      class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-slate-500" required></textarea>
                            @error('newDescription') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Budget (INR)</label>
                            <input type="number" wire:model="newBudget" placeholder="e.g. 500000" 
                                   class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-slate-500" required>
                            @error('newBudget') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Execution Date</label>
                                <input type="date" wire:model="newExecutionDate" 
                                       class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-slate-500" required>
                                @error('newExecutionDate') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Deadline Date</label>
                                <input type="date" wire:model="newDeadline" 
                                       class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-slate-500" required>
                                @error('newDeadline') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Assign to Secretary / Officer (For Simulation)</label>
                            <select wire:model="newAssignedSecretaryId" 
                                    class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-slate-500" required>
                                <option value="">-- Choose Officer --</option>
                                @foreach($orgUsers as $usr)
                                    <option value="{{ $usr->id }}">{{ $usr->name }} ({{ $usr->roleRelation?->name ?? ucfirst($usr->role) }})</option>
                                @endforeach
                            </select>
                            @error('newAssignedSecretaryId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Document Attachment (PDF, Image, or DOCX)</label>
                            <input type="file" wire:model="newDocument" 
                                   class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-slate-500">
                            <span class="text-[9px] text-slate-400 block mt-1">Supported formats: .pdf, .docx, image formats. Max 10MB.</span>
                            @error('newDocument') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Proper Justification</label>
                            <textarea wire:model="newJustification" rows="2" placeholder="Explain necessity, urgency, or benefits..." 
                                      class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-slate-500" required></textarea>
                            @error('newJustification') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-2">Involved Committee Members</label>
                            <div class="space-y-1.5 max-h-32 overflow-y-auto border border-slate-200 p-2.5 rounded bg-white">
                                @foreach($committeeAppointments as $appt)
                                    @if($appt->person)
                                        <label class="flex items-center gap-2 text-[11px] text-slate-700 cursor-pointer pl-1">
                                            <input type="checkbox" wire:model="newInvolvedMembers" value="{{ $appt->person->name }}"
                                                   class="rounded border-slate-300 text-slate-700 focus:ring-slate-500">
                                            <span>{{ $appt->person->name }} ({{ $appt->role?->name ?? $appt->designation }})</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                            @error('newInvolvedMembers') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-all">
                            Submit Proposal to Verification
                        </button>
                    </form>
                    </div>
                </div>
            </div>
 
            <!-- Right Column: Proposal details, Pipeline checklist & Projects -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                @if($selectedProposal)
                    <div class="bg-white rounded-2xl border border-slate-200 flex flex-col gap-5 shadow-sm overflow-hidden py-2">
                        <!-- Top Ultra-Compact Single Header Line -->
                        <div class="px-3.5 py-2 border-b border-slate-200">
                            <div class="flex items-center justify-between gap-2.5 min-w-0">
                                <!-- Left: Status Badge + Ref ID + Title + Metadata in Single Line -->
                                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300 shrink-0">
                                        {{ str_replace('_', ' ', $selectedProposal->status) }}
                                    </span>
                                    <span class="font-mono text-[10px] font-bold text-slate-400 shrink-0">#{{ substr($selectedProposal->id, 0, 8) }}</span>
                                    <h3 class="text-[13px] sm:text-xs font-bold text-slate-900 truncate leading-tight flex-1 min-w-0" title="{{ $selectedProposal->title }}">{{ $selectedProposal->title }}</h3>
                                    <span class="text-slate-300 shrink-0 hidden md:inline">•</span>
                                    <span class="text-[9px] text-slate-500 shrink-0 hidden md:inline">
                                        Submitted by: <strong class="text-slate-800 font-bold">{{ $selectedProposal->creator->name ?? 'Committee Auditor' }}</strong>
                                    </span>
                                    <span class="text-slate-300 shrink-0 hidden lg:inline">•</span>
                                    <span class="text-[9px] text-slate-400 shrink-0 hidden lg:inline">
                                        Created: {{ $selectedProposal->created_at ? $selectedProposal->created_at->format('d M Y, h:i A') : 'N/A' }}
                                    </span>
                                </div>
                                
                                <!-- Right: Single-Line Est. Budget Pill -->
                                <div class="flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 shrink-0">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800">Est. Budget</span>
                                    <span class="text-[13px] font-black text-emerald-700 font-mono">{{ format_indian_currency($selectedProposal->budget) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4-Grid Key Attributes Bar -->
                        <div class="px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Execution Date -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 shadow-xs flex items-center gap-3">
                                <div class="h-9 w-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs shrink-0 border border-blue-200">
                                    📅
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500 block">Execution Date</span>
                                    <strong class="text-xs font-black text-slate-900 truncate block">
                                        {{ $selectedProposal->execution_date ? $selectedProposal->execution_date->format('d M Y') : 'Not specified' }}
                                    </strong>
                                </div>
                            </div>

                            <!-- Target Deadline -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 shadow-xs flex items-center gap-3">
                                <div class="h-9 w-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-xs shrink-0 border border-emerald-200">
                                    ⏱️
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500 block">Deadline</span>
                                    <strong class="text-xs font-black text-slate-900 truncate block">
                                        {{ $selectedProposal->deadline ? $selectedProposal->deadline->format('d M Y') : 'Not specified' }}
                                    </strong>
                                </div>
                            </div>

                            <!-- Simulation Secretary -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 shadow-xs flex items-center gap-3">
                                <div class="h-9 w-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-black text-xs shrink-0 border border-purple-200">
                                    👤
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500 block">Assigned Secretary</span>
                                    <strong class="text-xs font-black text-purple-900 truncate block">
                                        {{ $selectedProposal->secretary->name ?? 'Unassigned' }}
                                    </strong>
                                </div>
                            </div>

                            <!-- Document Attachment -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 shadow-xs flex items-center gap-3">
                                <div class="h-9 w-9 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-black text-xs shrink-0 border border-amber-200">
                                    📑
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500 block">Document Attachment</span>
                                    @if($selectedProposal->document_path)
                                        <a href="{{ asset('storage/' . $selectedProposal->document_path) }}" target="_blank" 
                                           class="text-xs font-black text-blue-700 hover:underline truncate block">
                                            View Attached File ↗
                                        </a>
                                    @else
                                        <span class="text-xs font-bold text-slate-400 italic block">No document</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="px-2 space-y-4">
                            <!-- Scope & Description Section -->
                            <div class="p-4 rounded-xl bg-slate-50 border-l-4 border-blue-600 border-slate-200 shadow-2xs">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-blue-900 mb-1.5 flex items-center gap-1.5">
                                    🎯 Scope & Description
                                </h4>
                                <p class="text-xs text-slate-800 leading-relaxed font-semibold">{{ $selectedProposal->description }}</p>
                            </div>

                            <!-- Justification Section -->
                            <div class="p-4 rounded-xl bg-amber-50 border-l-4 border-amber-500 border-amber-200 shadow-2xs">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-amber-900 mb-1.5 flex items-center gap-1.5">
                                    💡 Strategic Justification
                                </h4>
                                <p class="text-xs text-slate-800 leading-relaxed font-semibold italic">
                                    {{ $selectedProposal->justification ?? 'No justification provided.' }}
                                </p>
                            </div>

                            <!-- Two Column Grid: Checklist & Involved Members -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Technical Checklist Sign-offs -->
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-600 mb-2.5 flex items-center gap-1.5">
                                        ✓ Technical Verification Checklist
                                    </h4>
                                    <div class="space-y-2">
                                        @forelse($selectedProposal->checklist ?? [] as $index => $item)
                                            <div class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200 shadow-2xs">
                                                @if($selectedProposal->status === 'VERIFICATION')
                                                    <input type="checkbox" wire:click="toggleChecklistItem({{ $index }})" {{ !empty($item['checked']) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                @else
                                                    <span class="h-4 w-4 rounded-full flex items-center justify-center text-[10px] font-black {{ !empty($item['checked']) ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                                        {{ !empty($item['checked']) ? '✓' : '○' }}
                                                    </span>
                                                @endif
                                                <span class="text-[11px] font-bold text-slate-800 leading-snug">{{ $item['description'] }}</span>
                                            </div>
                                        @empty
                                            <p class="text-[10px] text-slate-400 italic">No checklist sign-offs attached.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Involved Committee Members -->
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-600 mb-2.5 flex items-center gap-1.5">
                                        👥 Involved Committee Members
                                    </h4>
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($selectedProposal->involved_members ?? [] as $member)
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-white text-slate-800 border border-slate-300 shadow-2xs flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                                {{ $member }}
                                            </span>
                                        @empty
                                            <p class="text-[10px] text-slate-400 italic">No specific committee members assigned.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Workflow Pipeline Progress Stepper (Light High Contrast Card) -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 shadow-sm space-y-4">
                                <div class="flex justify-between items-center flex-wrap gap-2">
                                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-800 flex items-center gap-2">
                                        🚀 Workflow Pipeline Stage Progress
                                    </h4>
                                    <span class="text-[10px] font-black text-blue-900 px-3 py-1 rounded-md bg-blue-100 border border-blue-300">
                                        Active Stage: {{ str_replace('_', ' ', $selectedProposal->status) }}
                                    </span>
                                </div>

                                <div class="relative py-3">
                                    <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-2 bg-slate-200 rounded-full"></div>
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-2 bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-600 rounded-full transition-all duration-500" 
                                         style="width: {{ 
                                             $selectedProposal->status === 'DRAFT' ? '12.5%' : (
                                             $selectedProposal->status === 'VERIFICATION' ? '25%' : (
                                             $selectedProposal->status === 'VERIFIED' ? '37.5%' : (
                                             $selectedProposal->status === 'COMMITTEE_REVIEW' ? '50%' : (
                                             $selectedProposal->status === 'RECOMMENDED' ? '62.5%' : (
                                             $selectedProposal->status === 'PRESIDENT_REVIEW' ? '75%' : (
                                             $selectedProposal->status === 'APPROVED' ? '100%' : '50%' )))))) 
                                         }}">
                                    </div>

                                    <div class="relative flex justify-between">
                                        @php
                                            $stages = [
                                                'DRAFT' => ['label' => '1. Draft', 'rank' => 1],
                                                'VERIFICATION' => ['label' => '2. Verification', 'rank' => 2],
                                                'COMMITTEE_REVIEW' => ['label' => '3. Committee', 'rank' => 3],
                                                'PRESIDENT_REVIEW' => ['label' => '4. President', 'rank' => 4],
                                                'APPROVED' => ['label' => '5. Approved', 'rank' => 5],
                                            ];

                                            $statusRanks = [
                                                'DRAFT' => 1,
                                                'VERIFICATION' => 2,
                                                'VERIFIED' => 2,
                                                'COMMITTEE_REVIEW' => 3,
                                                'RECOMMENDED' => 3,
                                                'PRESIDENT_REVIEW' => 4,
                                                'APPROVED' => 5,
                                                'REJECTED' => 0,
                                                'DEFERRED' => 3,
                                            ];

                                            $currentRank = $statusRanks[$selectedProposal->status] ?? 1;
                                        @endphp
                                        @foreach($stages as $key => $info)
                                            @php
                                                $stageRank = $info['rank'];
                                                $isCompleted = ($currentRank === 5) || ($currentRank > 0 && $stageRank < $currentRank);
                                                $isCurrent = ($currentRank === $stageRank);
                                            @endphp
                                            <div class="flex flex-col items-center">
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-black border-2 transition-all shadow-sm
                                                    {{ $currentRank === 5 && $key === 'APPROVED' ? 'bg-emerald-600 text-white border-white ring-4 ring-emerald-500/30' : (
                                                       $isCurrent ? 'bg-blue-600 text-white border-white ring-4 ring-blue-500/30' : (
                                                       $isCompleted ? 'bg-emerald-600 text-white border-white' : 'bg-slate-200 text-slate-500 border-white'
                                                    )) }}">
                                                    @if($isCompleted || ($currentRank === 5 && $key === 'APPROVED'))
                                                        ✓
                                                    @else
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </div>
                                                <span class="text-[10px] mt-1.5 font-extrabold {{ 
                                                    $currentRank === 5 || $isCompleted ? 'text-emerald-700' : (
                                                    $isCurrent ? 'text-blue-700' : 'text-slate-500'
                                                ) }}">{{ $info['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Action Buttons per Stage -->
                                <div class="pt-3 border-t border-slate-200 flex justify-between items-center gap-4 flex-wrap">
                                    <span class="text-xs font-semibold text-slate-600">Governance Pipeline Action:</span>
                                    <div>
                                        @if($selectedProposal->status === 'VERIFICATION')
                                            @php
                                                $checkedCount = collect($selectedProposal->checklist ?? [])->where('checked', true)->count();
                                                $totalCount = count($selectedProposal->checklist ?? []);
                                            @endphp
                                            <button wire:click="verifyProposal('{{ $selectedProposal->id }}')" 
                                                    {{ $checkedCount < $totalCount ? 'disabled' : '' }}
                                                    class="px-5 py-2.5 rounded-xl text-xs font-black transition-all shadow-sm {{ $checkedCount === $totalCount ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                                                Verify Technical Sign-off ({{ $checkedCount }}/{{ $totalCount }})
                                            </button>
                                        @elseif($selectedProposal->status === 'VERIFIED')
                                            <button wire:click="forwardToCommittee('{{ $selectedProposal->id }}')" 
                                                    class="px-5 py-2.5 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-sm">
                                                Forward to Committee
                                            </button>
                                        @elseif($selectedProposal->status === 'COMMITTEE_REVIEW')
                                            <button wire:click="recommendProposal('{{ $selectedProposal->id }}')" 
                                                    class="px-5 py-2.5 rounded-xl text-xs font-black bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-sm">
                                                Recommend to President
                                            </button>
                                        @elseif($selectedProposal->status === 'RECOMMENDED')
                                            <button wire:click="sendToPresident('{{ $selectedProposal->id }}')" 
                                                    class="px-5 py-2.5 rounded-xl text-xs font-black bg-purple-600 hover:bg-purple-700 text-white shadow-sm">
                                                Submit to President Inbox
                                            </button>
                                        @elseif($selectedProposal->status === 'APPROVED')
                                            <span class="px-4 py-2 rounded-xl text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                ✓ Approved & Promoted to Active Project
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Double entry ledger -->
                <div class="glass-panel p-6 rounded-xl shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 mb-2">Transactional Audit Ledger</h3>
                    <p class="text-[10px] text-slate-400 mb-4">Double-entry record ledger tracing fund allocations back to authorized proposals.</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-[10px] text-left text-slate-600">
                            <thead class="bg-slate-100/80 text-slate-500 uppercase text-[8px] tracking-widest border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Ref</th>
                                    <th class="p-3">Account</th>
                                    <th class="p-3">Description</th>
                                    <th class="p-3 text-right">Debit</th>
                                    <th class="p-3 text-right">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ledger as $entry)
                                    <tr class="border-b border-slate-100">
                                        <td class="p-3 font-mono text-[9px] text-slate-500">{{ $entry->reference }}</td>
                                        <td class="p-3 font-semibold text-slate-700">{{ $entry->account_name }}</td>
                                        <td class="p-3 truncate max-w-[120px]">{{ $entry->description }}</td>
                                        <td class="p-3 text-right font-bold text-red-600">{{ $entry->type === 'DEBIT' ? format_indian_currency($entry->amount) : '--' }}</td>
                                        <td class="p-3 text-right font-bold text-emerald-600">{{ $entry->type === 'CREDIT' ? format_indian_currency($entry->amount) : '--' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 2: PREMISES & FLATS SETUP -->
    @if($activeTab === 'units' && ! $isSecretaryWorkspace)
        <!-- Top Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-card p-6 rounded-xl glow-blue">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Units / Flats</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpis['total_flats'] }}</h3>
            </div>
            <div class="glass-card p-6 rounded-xl glow-emerald">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Occupancy Rate</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpis['occupancy_rate'] }}%</h3>
            </div>
            <div class="glass-card p-6 rounded-xl glow-blue">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Registered Citizens</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $kpis['total_residents'] }}</h3>
            </div>
            <div class="glass-card p-6 rounded-xl glow-amber">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Outstanding Maintenance</p>
                <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ format_indian_currency($kpis['outstanding_dues']) }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Setup Forms -->
            <div class="lg:col-span-1 flex flex-col gap-6">
                <!-- 1. Register Property -->
                <div class="glass-panel p-6 rounded-xl shadow-sm">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b pb-1">1. Register Complex Premises</h3>
                    <form wire:submit.prevent="createProperty" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Property Name</label>
                            <input type="text" wire:model="propertyName" placeholder="e.g. Royal Palm Enclave" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Address Details</label>
                            <input type="text" wire:model="propertyAddress" placeholder="e.g. AA-II New Town" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">PIN Code</label>
                                <input type="text" wire:model="propertyPin" placeholder="700156" 
                                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Plot / Dag No.</label>
                                <input type="text" wire:model="plotNumber" placeholder="Dag No. 124" 
                                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                            Register Property
                        </button>
                    </form>
                </div>

                <!-- 2. Add Building Block -->
                <div class="glass-panel p-6 rounded-xl shadow-sm">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b pb-1">2. Add Tower or Block</h3>
                    <form wire:submit.prevent="createTower" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Target Property</label>
                            <select wire:model="selectedPropertyId" 
                                    class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                                @foreach($properties as $prop)
                                    <option value="{{ $prop->id }}">{{ $prop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Tower/Block Name</label>
                                <input type="text" wire:model="towerName" placeholder="e.g. Tower B" 
                                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Total Floors</label>
                                <input type="number" wire:model="towerFloors" placeholder="10" 
                                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                            Add Tower Block
                        </button>
                    </form>
                </div>

                <!-- 3. Register Flat Unit -->
                <div class="glass-panel p-6 rounded-xl shadow-sm">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b pb-1">3. Register Flat / Apartment Unit</h3>
                    <form wire:submit.prevent="createFlat" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Target Tower / Block</label>
                            <select wire:model="selectedBuildingId" 
                                    class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                                @foreach($buildings as $bldg)
                                    <option value="{{ $bldg->id }}">{{ $bldg->name }} ({{ $bldg->property->name ?? 'Complex' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Flat No.</label>
                                <input type="text" wire:model="flatNumber" placeholder="e.g. 101" 
                                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Floor</label>
                                <input type="number" wire:model="flatFloor" placeholder="1" 
                                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Type</label>
                                <select wire:model="unitType" 
                                        class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                                    <option value="1BHK">1BHK</option>
                                    <option value="2BHK">2BHK</option>
                                    <option value="3BHK">3BHK</option>
                                    <option value="PENTHOUSE">Penthouse</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Super Area (Sq.Ft.)</label>
                                <input type="number" wire:model="superArea" placeholder="1200" 
                                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Monthly Maint. (INR)</label>
                                <input type="number" wire:model="monthlyMaintenance" placeholder="2500" 
                                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                            Register Flat Unit
                        </button>
                    </form>
                </div>
            </div>

            <!-- Flat Directory (2 Columns) -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Apartment & Flat Registry Directory</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-100/80 text-slate-500 uppercase text-[8px] tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="p-3">Flat No.</th>
                                <th class="p-3">Tower Block</th>
                                <th class="p-3">Super Area</th>
                                <th class="p-3">Occupancy Status</th>
                                <th class="p-3 text-right">Maintenance Fee</th>
                                <th class="p-3 text-right">Outstanding Dues</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60">
                            @forelse($units as $u)
                                <tr class="hover:bg-slate-50/40 transition-colors">
                                    <td class="p-3 font-bold text-slate-800">{{ $u->flat_number }}</td>
                                    <td class="p-3 text-slate-500 font-semibold">{{ $u->building->name ?? 'Tower Block' }}</td>
                                    <td class="p-3">{{ $u->super_built_up_area }} Sq.Ft. <span class="text-[9px] text-slate-400 block">({{ $u->unit_type }})</span></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $u->occupancy_status === 'Vacant' ? 'bg-slate-100 text-slate-500 border' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                                            {{ $u->occupancy_status }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right font-semibold text-slate-700">{{ format_indian_currency($u->monthly_maintenance_amount) }}/mo</td>
                                    <td class="p-3 text-right font-bold {{ $u->outstanding_amount > 0 ? 'text-red-600' : 'text-slate-500' }}">
                                        {{ $u->outstanding_amount > 0 ? format_indian_currency($u->outstanding_amount) : 'Nil' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-slate-400 italic">No flats registered in database.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 3: MAINTENANCE CONTRIBUTION -->
    @if($activeTab === 'maintenance')
        <x-maintenance-contribution-table
            :units="$units"
            title="Maintenance Contribution"
            subtitle="Common collection view for all registered flats in this workspace."
            height="520px"
        />
    @endif

    <!-- TAB 3: RESIDENTS & MEMBERS REGISTRY -->
    @if($activeTab === 'residents')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Resident Registration Form -->
            <div class="lg:col-span-1 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4 border-b pb-1">Register Flat Resident</h3>
                <form wire:submit.prevent="registerResident" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Full Name</label>
                        <input type="text" wire:model="residentName" placeholder="e.g. Shyamal Sen" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        @error('residentName') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Mobile</label>
                            <input type="text" wire:model="residentMobile" placeholder="9830098301" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            @error('residentMobile') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Email</label>
                            <input type="email" wire:model="residentEmail" placeholder="shyamal@sen.com" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Allocate Flat / Unit</label>
                        <select wire:model="residentFlatId" 
                                class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            <option value="">-- Choose Flat Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">Flat {{ $u->flat_number }} ({{ $u->building->name ?? 'Block' }})</option>
                            @endforeach
                        </select>
                        @error('residentFlatId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-700 mb-1">Resident Type *</label>
                            <select wire:model="residentType" 
                                    class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                                <option value="Owner">Primary Owner</option>
                                <option value="Tenant">Tenant / Occupant</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-700 mb-1">Occupation</label>
                            <input type="text" wire:model="residentOccupation" placeholder="e.g. Lawyer" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Modern Selection for Number of Members in Family -->
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase text-slate-700 mb-1.5">Number of Members in Family *</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            @foreach([1, 2, 3, 4, 5, 6, 7, 8] as $num)
                                @php $label = $num === 8 ? '8+' : (string)$num; @endphp
                                <button type="button" 
                                        wire:click="$set('familyMembersCount', {{ $num }})"
                                        class="py-1.5 px-2 rounded-lg text-xs font-black transition-all border text-center cursor-pointer {{ $familyMembersCount === $num ? 'bg-blue-600 text-white border-blue-600 shadow-xs scale-102' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-300' }}">
                                    {{ $label }} {{ $num === 1 ? 'Person' : 'Pers' }}
                                </button>
                            @endforeach
                        </div>
                        @error('familyMembersCount') <span class="text-rose-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Verification ID & ID Number with dynamic text validation format hint -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-700 mb-1">Verification ID *</label>
                            <select wire:model.live="residentIdType" 
                                    class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                                <option value="Aadhaar">Aadhaar</option>
                                <option value="PAN">PAN</option>
                                <option value="Voter ID">Voter ID</option>
                                <option value="Passport">Passport</option>
                            </select>
                            @error('residentIdType') <span class="text-rose-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-700 mb-1">ID Number *</label>
                            @php
                                $placeholder = match($residentIdType) {
                                    'Aadhaar' => 'XXXX-XXXX-XXXX',
                                    'PAN' => 'ABCDE1234F',
                                    'Voter ID' => 'ABC1234567',
                                    'Passport' => 'A1234567',
                                    default => 'Enter ID Number'
                                };
                                $hint = match($residentIdType) {
                                    'Aadhaar' => 'Format: 12 digits (e.g. 1111-2222-3333)',
                                    'PAN' => 'Format: 10 chars (e.g. ABCDE1234F)',
                                    'Voter ID' => 'Format: 10 chars (e.g. ABC1234567)',
                                    'Passport' => 'Format: Passport ID (e.g. A1234567)',
                                    default => ''
                                };
                            @endphp
                            <input type="text" wire:model="residentIdNumber" placeholder="{{ $placeholder }}" 
                                   class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:border-blue-500 uppercase">
                            <span class="text-[9px] text-slate-400 font-medium block mt-0.5">{{ $hint }}</span>
                            @error('residentIdNumber') <span class="text-rose-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Father / Spouse Name</label>
                        <input type="text" wire:model="guardianName" placeholder="e.g. S. Sen" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Electricity Connection: Sub-meter hides Registered Meter; Own Meter hides Sub-meter -->
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-3">
                        <div>
                            <label class="block text-[10px] font-extrabold uppercase text-slate-700 mb-1">Electricity Connection Type *</label>
                            <select wire:model.live="electricityConnectionType"
                                    class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 font-bold text-slate-800 focus:outline-none focus:border-blue-500">
                                <option value="own_meter">Own Registered Meter</option>
                                <option value="submeter">Sub-meter Connection</option>
                            </select>
                            @error('electricityConnectionType') <span class="text-rose-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        @if($electricityConnectionType === 'own_meter')
                            <div>
                                <label class="block text-[10px] font-extrabold uppercase text-slate-700 mb-1">Registered Meter Number *</label>
                                <input type="text" wire:model="residentMeterNumber" placeholder="e.g. WBSEDCL-458712"
                                       class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                                @error('residentMeterNumber') <span class="text-rose-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($electricityConnectionType === 'submeter')
                            <div>
                                <label class="block text-[10px] font-extrabold uppercase text-slate-700 mb-1">Sub-meter Number *</label>
                                <input type="text" wire:model="residentSubmeterNumber" placeholder="e.g. SUB-A-101"
                                       class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                                @error('residentSubmeterNumber') <span class="text-rose-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all cursor-pointer">
                        Register Resident Profile
                    </button>
                </form>
            </div>

            <!-- Residents Directory (2 Columns) -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Apartment Resident & Owner Directory</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-100/80 text-slate-500 uppercase text-[8px] tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="p-3">Citizen Name</th>
                                <th class="p-3">Allocated Flat</th>
                                <th class="p-3">Contact Mobile</th>
                                <th class="p-3">Membership Type</th>
                                <th class="p-3">ID Proof Details</th>
                                <th class="p-3">Electricity Meter</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60">
                            @forelse($persons as $p)
                                @php
                                    $membership = $p->memberships->first();
                                @endphp
                                <tr class="hover:bg-slate-50/40 transition-colors">
                                    <td class="p-3">
                                        <div class="font-bold text-slate-800">{{ $p->name }}</div>
                                        <div class="text-[9px] text-slate-500 mt-0.5">{{ $p->occupation ?: 'Resident' }} • 👨‍👩‍👧‍👦 {{ $p->family_members ?: 1 }} {{ ($p->family_members ?? 1) == 1 ? 'Member' : 'Members' }}</div>
                                    </td>
                                    <td class="p-3">
                                        <div class="text-slate-700 font-semibold">Flat {{ $membership->unit->flat_number ?? 'N/A' }}</div>
                                        <div class="text-[9px] text-slate-400 mt-0.5">{{ $membership->unit->building->name ?? 'Block' }}</div>
                                    </td>
                                    <td class="p-3 font-semibold text-slate-700">
                                        {{ $p->mobile }}
                                        <span class="text-[9px] text-slate-400 block">{{ $p->email ?: '--' }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $membership && $membership->primary_owner ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                                            {{ $membership && $membership->primary_owner ? 'Primary Owner' : 'Co-Owner/Tenant' }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <span class="text-slate-700 font-medium">{{ $p->id_type ?? 'N/A' }}:</span>
                                        <span class="font-mono text-slate-500 text-[10px]">{{ $p->id_number ?: '--' }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $membership?->unit?->electricity_connection_type === 'submeter' ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                                            {{ $membership?->unit?->electricity_connection_type === 'submeter' ? 'Sub-meter' : 'Own Meter' }}
                                        </span>
                                        @if($membership?->unit?->electricity_connection_type === 'submeter')
                                            <span class="text-[9px] text-slate-500 block font-mono mt-1 font-bold">Sub: {{ $membership?->unit?->submeter_number ?: '--' }}</span>
                                        @else
                                            <span class="text-[9px] text-slate-500 block font-mono mt-1 font-bold">Meter: {{ $membership?->unit?->meter_number ?: '--' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-slate-400 italic">No residents registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 5: DOCUMENT VAULT (FORM A & FORM 1 ARCHIVES) -->
    @if($activeTab === 'documents')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Upload Doc Form -->
            <div class="lg:col-span-1 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4 border-b pb-1">Archive Legal Document</h3>
                <form wire:submit.prevent="uploadDocument" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Document Display Title</label>
                        <input type="text" wire:model="docName" placeholder="e.g. Bye-Laws of Association 2026" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        @error('docName') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Legal Category</label>
                        <select wire:model="docCategory" 
                                class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                            <option value="Form A">Form A (Apartment Ownership Declaration)</option>
                            <option value="Form 1">Form 1 (Registration Certificate)</option>
                            <option value="Bye-Laws">Bye-Laws & Regulations</option>
                            <option value="AGM Minutes">General Body Meeting Minutes (AGM/SGM)</option>
                            <option value="Property Deed">Approved Building Plan & Deed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Scope Description / Notes</label>
                        <textarea wire:model="docDescription" rows="3" placeholder="Enter notes or registration certificate numbers..." 
                                  class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                        Archive File
                    </button>
                </form>
            </div>

            <!-- Documents Table (2 Columns) -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Archived Organization Legal Vault</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-100/80 text-slate-500 uppercase text-[8px] tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="p-3">File Title</th>
                                <th class="p-3">Category Tag</th>
                                <th class="p-3">Description Note</th>
                                <th class="p-3">Archived Date</th>
                                <th class="p-3 text-right">View Link</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60">
                            @forelse($documents as $doc)
                                <tr class="hover:bg-slate-50/40 transition-colors">
                                    <td class="p-3 font-bold text-slate-800">
                                        {{ $doc->name }}
                                        <span class="text-[9px] text-slate-400 block font-mono">{{ $doc->file_path }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[8px] font-bold bg-blue-50 text-blue-600 border border-blue-200 uppercase">
                                            {{ $doc->category }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-slate-500 leading-snug">{{ $doc->description ?: 'No notes attached.' }}</td>
                                    <td class="p-3">{{ $doc->created_at->format('M d, Y') }}</td>
                                    <td class="p-3 text-right">
                                        <a href="#" class="text-blue-600 hover:underline font-semibold" onclick="alert('Demo File Preview: Accessing mock storage at {{ $doc->file_path }}')">
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-slate-400 italic">No documents archived in vault.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 6: ROLES & PERMISSIONS (RBAC) -->
    @if($activeTab === 'rbac')
        <!-- Roles Config Panel -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Roles Creation Form (1 Column) -->
            <div class="lg:col-span-1 glass-panel rounded-xl shadow-sm h-[520px] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 shrink-0">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                        {{ $roleId ? 'Edit Custom Role' : 'Create Custom Role' }}
                    </h3>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4">
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
                        <label class="block text-[10px] font-semibold text-slate-700 mb-2">Assign Permissions</label>
                        <div class="space-y-2 max-h-60 overflow-y-auto border border-slate-200 p-2.5 rounded bg-slate-50">
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
                    <button type="button" wire:click="saveCustomRole"
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

            <!-- Custom Roles & User Assignment Directory (2 Columns) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Roles List -->
                <div class="glass-panel rounded-xl shadow-sm h-[520px] flex flex-col overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 shrink-0">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Custom Roles</h3>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6">
                        @if($customRoles->isEmpty())
                            <p class="text-xs text-slate-500 italic py-6 text-center">No custom roles defined. Create one on the left.</p>
                        @else
                            <div class="divide-y divide-slate-200/60">
                                @foreach($customRoles as $role)
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
                                            <button type="button" wire:click="editCustomRole('{{ $role->id }}')" class="text-[10px] font-bold text-blue-600 hover:text-blue-500 hover:underline">Edit</button>
                                            <button type="button" wire:click="deleteCustomRole('{{ $role->id }}')" class="text-[10px] font-bold text-red-600 hover:text-red-500 hover:underline" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">Delete</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- User Assignment Directory -->
                <div class="glass-panel p-6 rounded-xl shadow-sm space-y-4">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Assign Custom Roles to Members</h3>
                    <div class="divide-y divide-slate-200/60">
                        @foreach($orgUsers as $user)
                            <div class="py-3 flex justify-between items-center gap-4 hover:bg-slate-50/40 px-2 rounded-lg transition-colors">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">{{ $user->name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $user->email }} | System Role: <span class="font-semibold">{{ ucfirst($user->role) }}</span></div>
                                </div>
                                <div>
                                    @if($user->role === 'president')
                                        <span class="text-[10px] bg-slate-100 text-slate-500 font-bold px-2.5 py-1 rounded">Supreme Admin Access</span>
                                    @else
                                        <select wire:change="assignRoleToUser('{{ $user->id }}', $event.target.value)"
                                                class="text-xs p-1.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none">
                                            <option value="">No Custom Role</option>
                                            @foreach($customRoles as $role)
                                                <option value="{{ $role->id }}" {{ $user->role_id === $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <!-- Assign Committee Designation Form & List -->
        <div class="mt-8 border-t border-slate-200 pt-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Appointment Form (1 Column) -->
            <div class="lg:col-span-1 glass-panel p-6 rounded-xl shadow-sm space-y-4 h-fit">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Appoint Committee Officer</h3>
                <p class="text-[11px] text-slate-500">Appoint a resident to a committee role. This automatically matches their system permissions to the designated role.</p>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Select Member / Resident</label>
                    <select wire:model="appointPersonId" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none">
                        <option value="">-- Choose Member --</option>
                        @foreach($persons as $person)
                            <option value="{{ $person->id }}">{{ $person->name }} (Flat {{ $person->memberships->first()?->unit?->flat_number ?? 'N/A' }}, Floor {{ $person->memberships->first()?->unit?->floor ?? 0 }}, Type {{ $person->memberships->first()?->unit?->unit_type ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                    @error('appointPersonId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Office Designation (Custom Role)</label>
                    <select wire:model="appointRoleId" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none">
                        <option value="">-- Choose Designation --</option>
                        @foreach($customRoles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('appointRoleId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Appointment Method</label>
                    <select wire:model="appointmentMethod" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none">
                        <option value="Election">Election</option>
                        <option value="Nomination">Nomination</option>
                        <option value="Co-Option">Co-Option</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Start Date</label>
                    <input type="date" wire:model="appointmentStartDate" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none">
                </div>

                <button type="button" wire:click="appointCommitteeOfficer" class="w-full py-2.5 rounded bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-500 shadow-sm">
                    Confirm Appointment
                </button>
            </div>

            <!-- Active Officers List (2 Columns) -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Active Office Bearers / Committee Members</h3>
                
                @if($committeeAppointments->isEmpty())
                    <p class="text-xs text-slate-500 italic py-6 text-center">No active committee members appointed. Set appointments on the left.</p>
                @else
                    <div class="divide-y divide-slate-200/60">
                        @foreach($committeeAppointments as $appt)
                            <div class="py-3.5 flex justify-between items-center gap-4 hover:bg-slate-50/40 px-2 rounded-lg transition-colors">
                                <div>
                                    <div class="text-xs font-bold text-slate-900">{{ $appt->person?->name }}</div>
                                    <div class="text-[10px] text-slate-500">
                                        Appointed as: <span class="font-extrabold text-blue-700">{{ $appt->designation }}</span> | 
                                        Method: {{ $appt->appointment_method }} | 
                                        Since: {{ \Carbon\Carbon::parse($appt->start_date)->format('d M Y') }}
                                    </div>
                                </div>
                                <div>
                                    <button type="button" wire:click="revokeCommitteeAppointment('{{ $appt->id }}')" 
                                            class="text-[10px] px-2.5 py-1.5 rounded bg-red-50 text-red-600 hover:bg-red-100 font-bold border border-red-200/50"
                                            onclick="confirm('Are you sure you want to revoke this officer appointment?') || event.stopImmediatePropagation()">
                                        Revoke
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($activeTab === 'cash-release-bills')
        <x-building-cash-bill-table
            :bills="$buildingCashBills"
            :persons="$persons"
            :units="$units"
            :proposals="$proposals"
            :projects="$projects"
            :milestones="$milestones"
            :showModal="$showCashBillModal"
            :showPrintModal="$showPrintVoucherModal"
            :selectedVoucherId="$selectedVoucherBillId"
            :billFormStep="$billFormStep"
            title="Building Cash Release & Expense Bill Desk (Cashier / Treasurer Desk)"
            subtitle="Cashier & Treasurer Desk to record purchase bills, tag responsible residents, and release cash from the main building cash collection fund."
        />
    @endif

    @if($activeTab === 'inventory-desk')
        <x-inventory-management-desk
            :items="$inventoryItems"
            :proposals="$proposals"
            :projects="$projects"
            :milestones="$milestones"
            :showInventoryModal="$showInventoryModal"
            :inventoryModalMode="$inventoryModalMode"
            :editingItemId="$editingItemId"
            :invName="$invName"
            :invSku="$invSku"
            :invCategory="$invCategory"
            :invUnit="$invUnit"
            :invMinStock="$invMinStock"
            :invUnitCost="$invUnitCost"
            :invAllocatedBudget="$invAllocatedBudget"
            :invStorageLocation="$invStorageLocation"
            :invRemarks="$invRemarks"
            :transType="$transType"
            :transQuantity="$transQuantity"
            :transUnitPrice="$transUnitPrice"
            :transProposalId="$transProposalId"
            :transProjectId="$transProjectId"
            :transMilestoneId="$transMilestoneId"
            :transRemarks="$transRemarks"
        />
    @endif

    @if($activeTab === 'balance-sheet')
        <x-balance-sheet-report
            :data="$balanceSheetData"
        />
    @endif

    @if($activeTab === 'audit-logs')
        <div class="space-y-6">
            <div class="glass-card p-6 rounded-xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 flex items-center gap-2">
                            <span>🔍 System Audit Trail & Security Log Inspector</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Real-time immutable audit history tracking user actions, IP addresses, proposal state transitions, and outbox events.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <input type="text" wire:model.live="auditSearch" placeholder="Search action, IP, or entity..." class="px-3 py-2 text-xs border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-400 w-64">
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="p-3 border-b border-slate-200">Timestamp</th>
                                <th class="p-3 border-b border-slate-200">Entity Type</th>
                                <th class="p-3 border-b border-slate-200">Action / Event</th>
                                <th class="p-3 border-b border-slate-200">User / Actor</th>
                                <th class="p-3 border-b border-slate-200">IP Address</th>
                                <th class="p-3 border-b border-slate-200">Reason / Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-800">
                            @forelse($auditLogs as $log)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        {{ $log->created_at ? $log->created_at->format('M d, Y H:i:s') : 'N/A' }}
                                    </td>
                                    <td class="p-3 font-bold text-slate-900">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 text-[10px]">
                                            {{ $log->auditable_type }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <span class="font-black text-slate-900">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-semibold text-slate-700">
                                        {{ $log->user_id ? 'User #' . substr($log->user_id, 0, 8) : 'System Worker' }}
                                    </td>
                                    <td class="p-3 font-mono text-[11px] text-slate-500">
                                        {{ $log->ip_address ?: '127.0.0.1' }}
                                    </td>
                                    <td class="p-3 text-slate-600">
                                        {{ $log->reason ?: 'State modification logged.' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400 font-medium">
                                        No security audit logs found matching current search filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
