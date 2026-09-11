<div>
    <!-- Header -->
    {{-- <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">President Inbox</h2>
        <p class="text-sm text-slate-500">Review proposals, check your own maintenance dues, and monitor total society collection.</p>
    </div> --}}

    <div class="flex border-b border-slate-200 mb-8 overflow-x-auto shrink-0 scrollbar-none gap-2">
        <button wire:click="changeTab('inbox')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'inbox' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Decision Inbox
        </button>
        <button wire:click="changeTab('self-maintenance')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'self-maintenance' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Self Maintenance Contribution
        </button>
        <button wire:click="changeTab('maintenance-collection')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'maintenance-collection' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Total Maintenance Collection
        </button>
        <button wire:click="changeTab('submeter-electricity')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'submeter-electricity' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Sub-meter Electricity Collection
        </button>
        <button wire:click="changeTab('cash-release-bills')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'cash-release-bills' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            💸 Cash Release & Expense Desk
        </button>
        <button wire:click="changeTab('inventory-desk')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'inventory-desk' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            📦 Inventory & Stock Reserve Desk
        </button>
        <button wire:click="changeTab('balance-sheet')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'balance-sheet' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            📊 Financial Balance Sheet Report
        </button>
    </div>

    <!-- Alerts -->
    @if(session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-sm flex items-center gap-2 shadow-sm">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    @if($activeTab === 'inbox')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Panel: Proposals Queue -->
        <div class="lg:col-span-1 glass-panel p-6 rounded-xl shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-800">Proposals Queue</h3>
                <div class="flex items-center gap-1">
                    <button wire:click="$set('proposalFilter', 'pending')" 
                            class="px-2.5 py-1 rounded-md text-[9px] font-extrabold transition-all cursor-pointer {{ $proposalFilter === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Pending ({{ \App\Modules\Planning\Models\Proposal::where('status', 'PRESIDENT_REVIEW')->count() }})
                    </button>
                    <button wire:click="$set('proposalFilter', 'all')" 
                            class="px-2.5 py-1 rounded-md text-[9px] font-extrabold transition-all cursor-pointer {{ $proposalFilter === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        All ({{ \App\Modules\Planning\Models\Proposal::count() }})
                    </button>
                </div>
            </div>
            <div class="space-y-2 max-h-[320px] overflow-y-auto pr-1.5">
                @forelse($pendingProposals as $p)
                    <div wire:click="selectProposal('{{ $p->id }}')" 
                         class="p-3 rounded-lg cursor-pointer border transition-all {{ $selectedProposalId === $p->id ? 'bg-white border-blue-500 shadow-sm glow-blue font-semibold' : 'bg-white/40 border-slate-200/60 hover:border-slate-300' }}">
                        <h4 class="text-xs font-bold text-slate-800 truncate leading-snug" title="{{ $p->title }}">{{ $p->title }}</h4>
                        <div class="flex justify-between items-center mt-3 text-[10px] text-slate-500">
                            <span>Budget: <strong class="text-slate-700 font-semibold">{{ format_indian_currency($p->budget) }}</strong></span>
                            <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase {{ 
                                $p->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (
                                $p->status === 'REJECTED' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-500/10 text-amber-600 border border-amber-500/20')
                            }}">
                                {{ str_replace('_', ' ', $p->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400">
                        <svg class="h-10 w-10 mx-auto opacity-30 mb-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-xs">No proposals matching filter criteria.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Panel: Proposal Detail View & President Actions -->
        <div class="lg:col-span-2">
            @if($selectedProposal)
                <div class="bg-white rounded-xl border border-slate-200/90 flex flex-col gap-3 shadow-md overflow-hidden">
                    <!-- Top Ultra-Compact Single Header Line -->
                    <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-200">
                        <div class="flex items-center justify-between gap-2.5 min-w-0">
                            <!-- Left: Status Badge + Ref ID + Title + Metadata in Single Line -->
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider {{ 
                                    $selectedProposal->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-900 border border-amber-300' 
                                }} shrink-0">
                                    {{ str_replace('_', ' ', $selectedProposal->status) }}
                                </span>
                                <span class="font-mono text-[9px] font-bold text-slate-400 shrink-0">#{{ substr($selectedProposal->id, 0, 8) }}</span>
                                <h3 class="text-[13px] sm:text-xs font-bold text-slate-900 truncate leading-tight flex-1 min-w-0" title="{{ $selectedProposal->title }}">{{ $selectedProposal->title }}</h3>
                                <span class="text-slate-300 shrink-0 hidden md:inline">•</span>
                                <span class="text-[9px] text-slate-500 shrink-0 hidden md:inline">
                                    Submitted by: <strong class="text-slate-800 font-bold">{{ $selectedProposal->creator->name ?? 'Committee Auditor' }}</strong>
                                </span>
                                <span class="text-slate-300 shrink-0 hidden lg:inline">•</span>
                                <span class="text-[12px] text-slate-400 shrink-0 hidden lg:inline">
                                    Created: {{ $selectedProposal->created_at ? $selectedProposal->created_at->format('d M Y, h:i A') : 'N/A' }}
                                </span>
                            </div>
                            
                            <!-- Right: Single-Line Est. Budget Pill -->
                            <div class="flex items-center gap-1 px-2 py-1 rounded bg-emerald-50 border border-emerald-200 shrink-0">
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800">Est. Budget</span>
                                <span class="text-[14px] font-black text-emerald-700 font-mono">{{ format_indian_currency($selectedProposal->budget) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4-Grid Key Attributes Bar (Compact) -->
                    <div class="px-4 grid grid-cols-2 lg:grid-cols-4 gap-2">
                        <!-- Execution Date -->
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/80 shadow-2xs flex items-center gap-2">
                            <div class="h-7 w-7 rounded-md bg-blue-500/10 text-blue-600 flex items-center justify-center font-bold text-[10px] shrink-0 border border-blue-500/20">
                                📅
                            </div>
                            <div class="min-w-0">
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block leading-none">Execution Date</span>
                                <strong class="text-[11px] font-extrabold text-slate-800 truncate block mt-0.5 leading-none">
                                    {{ $selectedProposal->execution_date ? \Carbon\Carbon::parse($selectedProposal->execution_date)->format('d M Y') : 'Not specified' }}
                                </strong>
                            </div>
                        </div>

                        <!-- Target Deadline -->
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/80 shadow-2xs flex items-center gap-2">
                            <div class="h-7 w-7 rounded-md bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-[10px] shrink-0 border border-emerald-500/20">
                                ⏱️
                            </div>
                            <div class="min-w-0">
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block leading-none">Target Deadline</span>
                                <strong class="text-[11px] font-extrabold text-slate-800 truncate block mt-0.5 leading-none">
                                    {{ $selectedProposal->deadline ? \Carbon\Carbon::parse($selectedProposal->deadline)->format('d M Y') : 'Not specified' }}
                                </strong>
                            </div>
                        </div>

                        <!-- Simulation Secretary -->
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/80 shadow-2xs flex items-center gap-2">
                            <div class="h-7 w-7 rounded-md bg-purple-500/10 text-purple-600 flex items-center justify-center font-bold text-[10px] shrink-0 border border-purple-500/20">
                                👤
                            </div>
                            <div class="min-w-0">
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block leading-none">Assigned Secretary</span>
                                <strong class="text-[11px] font-extrabold text-purple-700 truncate block mt-0.5 leading-none">
                                    {{ $selectedProposal->secretary->name ?? 'Unassigned' }}
                                </strong>
                            </div>
                        </div>

                        <!-- Document Attachment -->
                        <div class="p-2 rounded-lg bg-slate-50 border border-slate-200/80 shadow-2xs flex items-center gap-2">
                            <div class="h-7 w-7 rounded-md bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-[10px] shrink-0 border border-amber-500/20">
                                📑
                            </div>
                            <div class="min-w-0">
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-400 block leading-none">Attachment Document</span>
                                @if($selectedProposal->document_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($selectedProposal->document_path) }}" target="_blank" 
                                       class="text-[11px] font-extrabold text-blue-600 hover:underline truncate block mt-0.5 leading-none">
                                        View Attachment ↗
                                    </a>
                                @else
                                    <span class="text-[11px] font-bold text-slate-400 italic block mt-0.5 leading-none">No document</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="px-4 space-y-2.5">
                        <!-- Scope & Objectives Section -->
                        <div class="p-3 rounded-lg bg-slate-50/80 border border-slate-200/70 mb-2">
                            <h4 class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Proposal Scope & Objectives
                            </h4>
                            <p class="text-xs text-slate-700 leading-snug font-medium">{{ $selectedProposal->description }}</p>
                        </div>

                        <!-- Justification / ROI Rationale Section -->
                        @if($selectedProposal->justification)
                            <div class="p-3 rounded-lg bg-amber-50/50 border border-amber-200/70 mb-2">
                                <h4 class="text-[9px] font-black uppercase tracking-widest text-amber-800 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Strategic Justification & Rationale
                                </h4>
                                <p class="text-xs text-slate-700 leading-snug font-medium">{{ $selectedProposal->justification }}</p>
                            </div>
                        @endif

                        <!-- Two-Column Grid: Checklist Sign-offs & Involved Committee Members -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            <!-- Technical Checklist Sign-offs -->
                            <div class="p-3 rounded-lg bg-slate-50/80 border border-slate-200/70 mb-2">
                                <h4 class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Technical Verification Checklist
                                </h4>
                                <div class="space-y-1.5">
                                    @forelse($selectedProposal->checklist ?? [] as $item)
                                        <div class="flex items-center gap-2 p-1.5 rounded-md bg-white border border-slate-200/70 shadow-2xs">
                                            <span class="h-3.5 w-3.5 rounded-full flex items-center justify-center text-[9px] font-extrabold {{ !empty($item['checked']) ? 'bg-emerald-100 text-emerald-700 border border-emerald-300' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                                {{ !empty($item['checked']) ? '✓' : '○' }}
                                            </span>
                                            <span class="text-[10px] font-medium text-slate-700 leading-tight">{{ $item['description'] }}</span>
                                        </div>
                                    @empty
                                        <p class="text-[10px] text-slate-400 italic">No checklist sign-offs attached.</p>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Involved Committee Members -->
                            <div class="p-3 rounded-lg bg-slate-50/80 border border-slate-200/70 mb-2">
                                <h4 class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Involved Committee Members
                                </h4>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($selectedProposal->involved_members ?? [] as $memberName)
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-white text-slate-700 border border-slate-200/80 shadow-2xs flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            {{ $memberName }}
                                        </span>
                                    @empty
                                        <p class="text-[10px] text-slate-400 italic">No specific committee members assigned.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Workflow Pipeline Progress Stepper Card -->
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/90 shadow-2xs space-y-3">
                            <div class="flex justify-between items-center flex-wrap gap-2">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-800 flex items-center gap-1.5">
                                    🚀 Workflow Pipeline Stage Progress
                                </h4>
                                <span class="text-[9px] font-black text-blue-900 px-2.5 py-0.5 rounded-md bg-blue-100 border border-blue-300">
                                    Active Stage: {{ str_replace('_', ' ', $selectedProposal->status) }}
                                </span>
                            </div>

                            <div class="relative py-2">
                                <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1.5 bg-slate-200 rounded-full"></div>
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-600 rounded-full transition-all duration-500" 
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
                                            <div class="h-7 w-7 rounded-full flex items-center justify-center text-[10px] font-black border-2 transition-all shadow-xs
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
                                            <span class="text-[9px] mt-1 font-extrabold {{ 
                                                $currentRank === 5 || $isCompleted ? 'text-emerald-700' : (
                                                $isCurrent ? 'text-blue-700' : 'text-slate-500'
                                            ) }}">{{ $info['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Executive Action Buttons / Decision Desk -->
                    <div class="border-t border-slate-200/80 bg-slate-900 px-4 py-3 flex flex-col sm:flex-row gap-3 justify-between items-center text-white">
                        <div>
                            <span class="text-xs font-bold text-slate-200 block leading-tight">Executive Decision Desk</span>
                            <span class="text-[9px] text-slate-400 block mt-0.5">
                                {{ $selectedProposal->status === 'APPROVED' ? 'Formal executive approval logged. Project activated under execution desk.' : 'Select an action to log decision record & execute workflow.' }}
                            </span>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            @if($selectedProposal->status === 'APPROVED')
                                <span class="px-4 py-2 rounded-xl text-xs font-black bg-emerald-600 text-white shadow-md shadow-emerald-600/30 flex items-center gap-1.5 border border-emerald-400">
                                    <svg class="w-4 h-4 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    ✓ Approved & Promoted to Active Project
                                </span>
                            @elseif($selectedProposal->status === 'REJECTED')
                                <span class="px-4 py-2 rounded-xl text-xs font-black bg-rose-600 text-white shadow-md shadow-rose-600/30 flex items-center gap-1.5 border border-rose-400">
                                    ✕ Proposal Rejected
                                </span>
                            @elseif($selectedProposal->status === 'DEFERRED')
                                <span class="px-4 py-2 rounded-xl text-xs font-black bg-amber-500 text-slate-950 shadow-md shadow-amber-500/30 flex items-center gap-1.5 border border-amber-300">
                                    ⏳ Proposal Deferred
                                </span>
                            @else
                                <!-- Defer Button -->
                                <button wire:click="openDecisionModal('DEFERRED')" 
                                        class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 border border-amber-500/30 font-bold text-xs transition-all shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Defer
                                </button>
                                <!-- Reject Button -->
                                <button wire:click="openDecisionModal('REJECTED')" 
                                        class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-rose-300 border border-rose-500/30 font-bold text-xs transition-all shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Reject
                                </button>
                                <!-- Approve Button -->
                                <button wire:click="openDecisionModal('APPROVED')" 
                                        class="px-5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Approve & Promote
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Decision Remarks Modal -->
                @if($showModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-all">
                        <div class="w-full max-w-md bg-white border border-slate-200 rounded-xl overflow-hidden shadow-2xl relative">
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                                    Confirm Decision: 
                                    <span class="{{ 
                                        $decisionType === 'APPROVED' ? 'text-emerald-600' : (
                                        $decisionType === 'REJECTED' ? 'text-red-600' : 'text-amber-600') 
                                    }} font-extrabold">{{ $decisionType }}</span>
                                </h3>
                                <button wire:click="closeModal" class="text-slate-400 hover:text-slate-800 text-xl font-bold">×</button>
                            </div>

                            <!-- Form -->
                            <div class="p-6 space-y-4 bg-white">
                                <div class="p-3 bg-slate-50 rounded border border-slate-200 text-[11px] text-slate-500 leading-relaxed">
                                    @if($decisionType === 'APPROVED')
                                        <strong>Auto Promotion Protocol:</strong> Approving this proposal will instantly create an immutable decision record, launch the project, create kickoff tasks, and log double-entry ledger fund allocations.
                                    @elseif($decisionType === 'REJECTED')
                                        <strong>Rejection Protocol:</strong> Rejection stops all progress. The proposal status will transition to REJECTED.
                                    @else
                                        <strong>Deferral Protocol:</strong> Deferral loops the proposal to DEFERRED state for further information.
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Decision Remarks / Executive Notes</label>
                                    <textarea wire:model="remarks" rows="4" placeholder="Enter formal comments, budget authorization conditions, or reasons for deferral..." 
                                              class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20" required></textarea>
                                    @error('remarks') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="px-6 py-4 bg-slate-50/60 border-t border-slate-200 flex justify-end gap-3">
                                <button wire:click="closeModal" class="px-4 py-2 rounded text-xs font-semibold bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors">
                                    Cancel
                                </button>
                                <button wire:click="submitDecision" class="px-4 py-2 rounded text-xs font-bold text-white transition-colors {{
                                    $decisionType === 'APPROVED' ? 'bg-emerald-600 hover:bg-emerald-500' : (
                                    $decisionType === 'REJECTED' ? 'bg-red-600 hover:bg-red-500' : 'bg-amber-600 hover:bg-amber-500')
                                }}">
                                    Submit Authority Decision
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="glass-panel p-6 rounded-xl text-center py-20 text-slate-400 shadow-sm">
                    <p class="text-xs">Select a proposal from the queue to execute decision reviews.</p>
                </div>
            @endif
        </div>
        </div>

        <!-- Active Projects & Milestones Registry Card -->
        <div class="mt-8 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center flex-wrap gap-2 pb-3 border-b border-slate-200">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-800 border border-emerald-300">
                            PROJECT EXECUTION DESK
                        </span>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Active Projects & Milestones</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Approved projects, milestone tracking, progress updates & milestone creation desk.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.250ms="projectSearch" placeholder="Filter active projects..." 
                               class="text-xs py-1.5 pl-8 pr-7 rounded-lg bg-slate-50 border border-slate-200 text-slate-800 font-medium placeholder-slate-400 focus:outline-none focus:border-blue-500 w-44 sm:w-60 transition-all shadow-2xs">
                        
                        @if(!empty($projectSearch))
                            <button type="button" wire:click="$set('projectSearch', '')" class="absolute right-2 top-1.5 text-slate-400 hover:text-slate-700 text-xs font-bold px-0.5 cursor-pointer">
                                ✕
                            </button>
                        @endif
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700 border border-blue-200 shrink-0">
                        {{ $projects->count() }} Active Projects
                    </span>
                </div>
            </div>

            <!-- Scrollable Projects Container -->
            <div class="max-h-[600px] min-h-[380px] overflow-y-auto space-y-4 pr-2 scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-slate-100">
                {{-- max-h-[640px] min-h-[380px] overflow-y-auto space-y-4 pr-2 scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-slate-100 --}}
                @forelse($projects as $proj)
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-200 shadow-2xs space-y-4">
                        <div class="flex justify-between items-start gap-4 flex-wrap">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-black text-slate-900">{{ $proj->title }}</h4>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        {{ $proj->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-snug font-medium">{{ $proj->description }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Allocated Budget</span>
                                <strong class="text-base font-black text-emerald-700">{{ format_indian_currency($proj->budget) }}</strong>
                            </div>
                        </div>

                        <!-- Milestone Header & Add Button -->
                        <div class="pt-3 border-t border-slate-200 flex justify-between items-center flex-wrap gap-2">
                            <h5 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                🚩 Milestones ({{ $proj->milestones->where('status', 'COMPLETED')->count() }}/{{ $proj->milestones->count() }} Completed)
                            </h5>

                            <!-- President, Secretary & Joint Secretary can create Milestone -->
                            <button type="button" wire:click="$set('newMilestoneProjectId', '{{ $proj->id }}')" 
                                    class="px-3 py-1.5 rounded-lg text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition-all flex items-center gap-1">
                                + Add Project Milestone
                            </button>
                        </div>

                        <!-- Inline Add Milestone Form -->
                        @if($newMilestoneProjectId === $proj->id)
                            <div class="p-4 rounded-xl bg-white border border-blue-200 shadow-sm space-y-3 mt-2">
                                <div class="text-xs font-black text-blue-900 uppercase tracking-wider">
                                    ➕ Create New Milestone for: <span class="text-slate-800">{{ $proj->title }}</span>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Milestone Title</label>
                                        <input type="text" wire:model="newMilestoneTitle" placeholder="e.g. Phase 2 Testing & Handover" 
                                               class="w-full text-xs p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:outline-none focus:border-blue-500" required>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Description</label>
                                        <textarea wire:model="newMilestoneDescription" rows="2" placeholder="Detail the work scope for this milestone..." 
                                                  class="w-full text-xs p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:outline-none focus:border-blue-500"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Target Due Date</label>
                                        <input type="date" wire:model="newMilestoneDueDate" 
                                               class="w-full text-xs p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Budget Share (₹)</label>
                                        <input type="number" wire:model="newMilestoneBudget" placeholder="e.g. 50000" 
                                               class="w-full text-xs p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:outline-none focus:border-blue-500">
                                    </div>
                                </div>

                                <div class="flex gap-2 justify-end pt-2">
                                    <button type="button" wire:click="createMilestone" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-black text-xs shadow-2xs">
                                        Save Milestone
                                    </button>
                                    <button type="button" wire:click="$set('newMilestoneProjectId', '')" class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Milestones List -->
                        <div class="space-y-2">
                            @forelse($proj->milestones as $ms)
                                @if($editingMilestoneId === $ms->id)
                                    <!-- Milestone Edit Form (Inline) -->
                                    <div class="p-4 rounded-xl bg-white border border-blue-300 shadow-md space-y-3">
                                        <div class="flex justify-between items-center border-b border-blue-100 pb-2">
                                            <span class="text-xs font-black text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                                                ✏️ Edit Milestone: <span class="text-slate-800 font-bold">{{ $ms->title }}</span>
                                            </span>
                                            <button type="button" wire:click="cancelEditingMilestone" class="text-slate-400 hover:text-slate-700 text-sm font-black cursor-pointer">
                                                ✕
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                            <div class="sm:col-span-2">
                                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Milestone Title *</label>
                                                <input type="text" wire:model="editMilestoneTitle" placeholder="Milestone Title" 
                                                       class="w-full text-xs p-2 rounded bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:border-blue-500" required>
                                                @error('editMilestoneTitle') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Status</label>
                                                <select wire:model="editMilestoneStatus" 
                                                        class="w-full text-xs p-2 rounded bg-slate-50 border border-slate-200 text-slate-900 font-bold focus:border-blue-500">
                                                    <option value="PENDING">PENDING</option>
                                                    <option value="IN_PROGRESS">IN PROGRESS</option>
                                                    <option value="COMPLETED">COMPLETED</option>
                                                    <option value="CANCELLED">CANCELLED</option>
                                                </select>
                                            </div>

                                            <div class="sm:col-span-3">
                                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Description / Work Scope</label>
                                                <textarea wire:model="editMilestoneDescription" rows="2" placeholder="Milestone scope and progress notes..." 
                                                          class="w-full text-xs p-2 rounded bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:border-blue-500"></textarea>
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Target Due Date</label>
                                                <input type="date" wire:model="editMilestoneDueDate" 
                                                       class="w-full text-xs p-2 rounded bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:border-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Budget Share (₹)</label>
                                                <input type="number" wire:model="editMilestoneBudget" placeholder="0.00" 
                                                       class="w-full text-xs p-2 rounded bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:border-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Progress Percentage (%) (Auto-Calculated)</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="number" wire:model="editMilestoneProgress" readonly 
                                                           class="w-full text-xs p-2 rounded bg-emerald-50 border border-emerald-300 font-black text-emerald-900 focus:outline-none">
                                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-1 rounded border border-emerald-300 shrink-0">
                                                        {{ $editMilestoneProgress }}% Done
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Weighted Checklist Tasks Desk -->
                                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 space-y-2 mt-2">
                                            <div class="flex justify-between items-center flex-wrap gap-2 pb-1 border-b border-slate-200">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-800 flex items-center gap-1">
                                                    📋 Fixed Weightage Checklist & Task Breakdown
                                                </span>
                                                <div class="flex items-center gap-1.5">
                                                    <button type="button" wire:click="equalizeChecklistWeightages" 
                                                            class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-slate-200 hover:bg-slate-300 text-slate-700 cursor-pointer">
                                                        Equalize Weightages
                                                    </button>
                                                    <button type="button" wire:click="addChecklistItem" 
                                                            class="px-2 py-0.5 rounded text-[8px] font-black uppercase bg-blue-600 hover:bg-blue-700 text-white cursor-pointer">
                                                        + Add Task
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="space-y-1.5">
                                                @foreach($editMilestoneChecklist as $index => $item)
                                                    <div class="flex items-center gap-2 p-2 rounded bg-white border border-slate-200 shadow-2xs">
                                                        <input type="checkbox" wire:click="toggleChecklistItem({{ $index }})" {{ !empty($item['completed']) ? 'checked' : '' }}
                                                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer">
                                                        <input type="text" wire:model.live="editMilestoneChecklist.{{ $index }}.task" placeholder="Task name..." 
                                                               class="flex-1 text-xs p-1 rounded bg-slate-50 border border-slate-200 text-slate-800 font-medium focus:border-blue-500">
                                                        <div class="flex items-center gap-1 shrink-0">
                                                            <span class="text-[9px] font-bold text-slate-500 uppercase">Weight:</span>
                                                            <input type="number" wire:model.live="editMilestoneChecklist.{{ $index }}.weightage" min="1" max="100" placeholder="25" 
                                                                   class="w-14 text-xs p-1 rounded bg-slate-50 border border-slate-200 text-center font-bold text-blue-900 focus:border-blue-500">
                                                            <span class="text-[9px] font-bold text-slate-500">%</span>
                                                        </div>
                                                        <button type="button" wire:click="removeChecklistItem({{ $index }})" 
                                                                class="text-rose-500 hover:text-rose-700 text-xs font-black px-1 cursor-pointer">
                                                            ✕
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @php
                                                $totalWeight = collect($editMilestoneChecklist)->sum(fn ($item) => (int)($item['weightage'] ?? 0));
                                            @endphp
                                            <div class="flex justify-between items-center text-[9px] font-bold text-slate-500 pt-1">
                                                <span>Total Allocated Weightage: <strong class="{{ $totalWeight === 100 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $totalWeight }}%</strong></span>
                                                <span class="text-blue-700 font-extrabold">Calculated Progress: {{ $editMilestoneProgress }}%</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                            <button type="button" wire:click="deleteMilestone('{{ $ms->id }}')" 
                                                    onclick="return confirm('Are you sure you want to delete this milestone?')"
                                                    class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs flex items-center gap-1 transition-all cursor-pointer">
                                                🗑 Delete
                                            </button>
                                            <div class="flex gap-2">
                                                <button type="button" wire:click="cancelEditingMilestone" class="px-3.5 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs cursor-pointer">
                                                    Cancel
                                                </button>
                                                <button type="button" wire:click="updateMilestone" class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-black text-xs shadow-xs cursor-pointer">
                                                    Save Changes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs space-y-2">
                                        <div class="flex flex-wrap justify-between items-center gap-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-xs font-black text-slate-900">{{ $ms->title }}</span>
                                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase {{ 
                                                        $ms->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (
                                                        $ms->status === 'IN_PROGRESS' ? 'bg-blue-100 text-blue-800 border border-blue-300' : (
                                                        $ms->status === 'CANCELLED' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-amber-100 text-amber-900 border border-amber-300'
                                                    )) }}">
                                                        {{ $ms->status }}
                                                    </span>
                                                    <span class="text-[9px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">
                                                        {{ $ms->progress_percentage }}% Progress
                                                    </span>
                                                </div>
                                                @if($ms->description)
                                                    <p class="text-[11px] text-slate-600 mt-1 leading-snug font-medium">{{ $ms->description }}</p>
                                                @endif
                                                <div class="flex gap-4 text-[10px] text-slate-500 mt-1 font-semibold">
                                                    <span>Due: <strong class="text-slate-800">{{ $ms->due_date ? $ms->due_date->format('d M Y') : 'N/A' }}</strong></span>
                                                    <span>Budget Share: <strong class="text-emerald-700">{{ format_indian_currency($ms->budget_allocation) }}</strong></span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button wire:click="startEditingMilestone('{{ $ms->id }}')" 
                                                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-all flex items-center gap-1 cursor-pointer">
                                                    ✏️ Edit
                                                </button>
                                                <button wire:click="toggleMilestoneStatus('{{ $ms->id }}')" 
                                                        class="px-3 py-1.5 rounded-lg text-xs font-black transition-all shadow-2xs cursor-pointer {{ $ms->status === 'COMPLETED' ? 'bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                                    {{ $ms->status === 'COMPLETED' ? '↺ Reopen' : '✓ Mark Complete' }}
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Progress Bar Only on Card Front (Solid Color) -->
                                        <div class="pt-2 border-t border-slate-100">
                                            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                                <div class="{{ $ms->status === 'COMPLETED' ? 'bg-emerald-600' : 'bg-blue-600' }} h-2 rounded-full transition-all duration-500" 
                                                     style="width: {{ $ms->progress_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <p class="text-xs text-slate-400 italic p-3 bg-white rounded-lg border border-slate-200">No milestones registered for this project yet.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400">
                        <p class="text-xs font-semibold">No active projects currently under execution.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Transactional Audit Ledger Card (Fixed Height with Scroll) -->
        <div class="mt-8 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex justify-between items-center flex-wrap gap-2 pb-3 border-b border-slate-200">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Transactional Audit Ledger
                    </h3>
                    <p class="text-[10px] text-slate-500 mt-0.5">Double-entry record ledger tracing fund allocations back to authorized proposals.</p>
                </div>
                <span class="px-2.5 py-1 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
                    {{ $ledger->count() }} Audit Records
                </span>
            </div>

            <div class="max-h-[600px] min-h-[380px] overflow-y-auto space-y-4 pr-2 scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-slate-100">
                <table class="w-full text-xs text-left text-slate-600">
                    <thead class="bg-slate-100/90 text-slate-500 uppercase text-[9px] tracking-widest border-b border-slate-200 sticky top-0 z-10 backdrop-blur-xs">
                        <tr>
                            <th class="p-3">Ref ID</th>
                            <th class="p-3">Account Name</th>
                            <th class="p-3">Audit Description</th>
                            <th class="p-3 text-right">Debit (-)</th>
                            <th class="p-3 text-right">Credit (+)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ledger as $entry)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3 font-mono text-[10px] font-bold text-slate-700">{{ $entry->reference }}</td>
                                <td class="p-3 font-bold text-slate-800 text-xs">{{ $entry->account_name }}</td>
                                <td class="p-3 text-[11px] text-slate-600 truncate max-w-[280px]" title="{{ $entry->description }}">{{ $entry->description }}</td>
                                <td class="p-3 text-right font-extrabold text-xs text-rose-600 font-mono">
                                    {{ $entry->type === 'DEBIT' ? format_indian_currency($entry->amount) : '—' }}
                                </td>
                                <td class="p-3 text-right font-extrabold text-xs text-emerald-600 font-mono">
                                    {{ $entry->type === 'CREDIT' ? format_indian_currency($entry->amount) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-400 italic text-xs">No audit ledger records logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($activeTab === 'self-maintenance')
        <x-maintenance-contribution-table
            :units="$selfMaintenanceUnits"
            title="Self Maintenance Contribution"
            subtitle="Your linked flat maintenance status, month-wise entries, and outstanding dues."
            height="320px"
        />
    @endif

    @if($activeTab === 'maintenance-collection')
        <x-maintenance-contribution-table
            :units="$maintenanceUnits"
            title="Total Maintenance Collection & Resident Backlog Desk"
            subtitle="All flats, month-wise billing history, backlog arrears, collected amount, and society breakdown."
            height="520px"
        />
    @endif

    @if($activeTab === 'submeter-electricity')
        <x-submeter-electricity-bill-table
            :bills="$submeterElectricityBills"
            title="Sub-meter Electricity Bill Collection"
            subtitle="Collection register for residents who have submitted sub-meter electricity bills."
            height="520px"
        />
    @endif

    @if($activeTab === 'cash-release-bills')
        <x-building-cash-bill-table
            :bills="$buildingCashBills"
            :persons="$persons"
            :units="$maintenanceUnits"
            :proposals="$allProposals"
            :projects="$projects"
            :milestones="$milestones"
            :showModal="$showCashBillModal"
            :showPrintModal="$showPrintVoucherModal"
            :selectedVoucherId="$selectedVoucherBillId"
            :billFormStep="$billFormStep"
            title="Building Cash Release & Expense Bill Desk"
            subtitle="President & Treasurer Desk to record purchase bills, tag responsible residents, and release cash from the building main fund."
        />
    @endif

    @if($activeTab === 'inventory-desk')
        <x-inventory-management-desk
            :items="$inventoryItems"
            :proposals="$allProposals"
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
</div>
