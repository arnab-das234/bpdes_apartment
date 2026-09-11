<div>
    <!-- Tabs Header Menu -->
    <div class="flex border-b border-slate-200 mb-8 overflow-x-auto shrink-0 scrollbar-none gap-2">
        <button wire:click="changeTab('overview')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'overview' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            My Flat Profile
        </button>
        <button wire:click="changeTab('maintenance')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'maintenance' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Maintenance Contribution
        </button>
        <button wire:click="changeTab('complaints')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'complaints' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Grievance Desk
        </button>
        <button wire:click="changeTab('proposals')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'proposals' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Submit Proposal
        </button>
        <button wire:click="changeTab('billing')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'billing' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Submeters & Bills
        </button>
        <button wire:click="changeTab('experiences')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'experiences' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Experiences Board
        </button>
        <button wire:click="changeTab('feedback')" class="px-5 py-3 text-xs font-bold transition-all border-b-2 whitespace-nowrap {{ $activeTab === 'feedback' ? 'border-b-2 border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Meeting Feedback
        </button>
    </div>

    <!-- Alert System -->
    @if(session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-sm flex items-center gap-2 shadow-sm animate-fade-in">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- TAB 1: MY FLAT PROFILE OVERVIEW -->
    @if($activeTab === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Side: Profile Specs & Maintenance -->
            <div class="lg:col-span-1 flex flex-col gap-6">
                <!-- Flat Card -->
                <div class="glass-panel p-6 rounded-xl shadow-sm relative overflow-hidden">
                    <span class="text-[9px] font-bold uppercase tracking-wider text-blue-600">Unit Occupant</span>
                    <h3 class="text-xl font-extrabold text-slate-900 mt-1 leading-snug">Flat {{ $unit->flat_number ?? '101' }}</h3>
                    <p class="text-xs text-slate-500 mt-1 font-semibold">{{ $unit->building->name ?? 'Tower A' }} • Floor {{ $unit->floor ?? 0 }}</p>

                    <div class="border-t border-slate-200 mt-4 pt-4 space-y-2 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span>Resident Name:</span>
                            <strong class="text-slate-800">{{ $person->name ?? 'Occupant' }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>BHK Type:</span>
                            <strong class="text-slate-800">{{ $unit->unit_type ?? '3BHK' }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Family Size:</span>
                            <strong class="text-slate-800">{{ $person->family_members ?? 1 }} members</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Car Parking Allocation:</span>
                            <strong class="text-slate-800">{{ $person->car_parking ? 'Yes (1 Slot)' : 'No' }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Professional Status:</span>
                            <strong class="text-slate-800">{{ $person->is_professional ? 'Working Professional' : 'Self-Employed/Other' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Financial Card -->
                <div class="glass-panel p-6 rounded-xl shadow-sm glow-amber">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Unpaid Outstanding Dues</p>
                    <h3 class="text-2xl font-black mt-2 {{ ($unit->outstanding_amount ?? 0) > 0 ? 'text-red-600' : 'text-slate-900' }}">
                        {{ format_indian_currency($unit->outstanding_amount ?? 0) }}
                    </h3>
                    <p class="text-[10px] text-slate-400 mt-2">Monthly Maintenance Charge: <strong>{{ format_indian_currency($unit->monthly_maintenance_amount ?? 0) }}/mo</strong></p>
                    @if($unit)
                        <div class="mt-4 pt-3 border-t border-slate-200/80 flex items-center gap-2">
                            <a href="{{ route('maintenance.statement', $unit->id) }}" target="_blank" 
                               class="px-3 py-1.5 rounded-lg text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                <span>📜</span> Print Dues Statement
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Side: Committee Office Bearers & Document Vault -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- Committee List -->
                <div class="glass-panel p-6 rounded-xl shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Board Committee Bearers</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse($committee as $appt)
                            <div class="p-3.5 rounded-lg border border-slate-200 bg-white/40 flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white uppercase text-xs">
                                    {{ substr($appt->person->name ?? 'C', 0, 2) }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 leading-tight">{{ $appt->person->name ?? 'Officer' }}</h4>
                                    <p class="text-[9px] text-blue-600 font-bold uppercase tracking-wider mt-0.5">{{ $appt->designation }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">No board committee members active.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Personal Document Vault -->
                <div class="glass-panel p-6 rounded-xl shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-bold text-slate-800">My Document Vault</h3>
                        <a href="#upload-doc" wire:click="changeTab('billing')" class="text-xs text-blue-600 font-semibold hover:underline">+ Upload File</a>
                    </div>
                    <div class="space-y-2">
                        @forelse($documents as $doc)
                            <div class="flex justify-between items-center p-3 rounded bg-slate-50 border border-slate-200 text-xs shadow-sm">
                                <div>
                                    <strong class="text-slate-800">{{ $doc->name }}</strong>
                                    <span class="text-[9px] text-slate-400 block font-mono">{{ $doc->file_path }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[8px] font-bold bg-blue-50 text-blue-600 border border-blue-200 uppercase">{{ $doc->category }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic text-center py-4">No personal files archived in vault.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 2: SELF MAINTENANCE CONTRIBUTION -->
    @if($activeTab === 'maintenance')
        <x-maintenance-contribution-table
            :units="$unit ? collect([$unit]) : collect()"
            title="Maintenance Contribution"
            subtitle="Your flat maintenance contribution, paid amount, and outstanding dues."
            height="320px"
        />
    @endif

    <!-- TAB 2: GRIEVANCE DESK -->
    @if($activeTab === 'complaints')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Lodge Form -->
            <div class="lg:col-span-1 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4 border-b pb-1">Report a Maintenance Issue</h3>
                <form wire:submit.prevent="submitComplaint" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Issue Category</label>
                        <select wire:model="complaintCategory" 
                                class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                            <option value="Plumbing">Plumbing (Leakage, Water lines)</option>
                            <option value="Electrical">Electrical (Common lights, Submeters)</option>
                            <option value="Elevator">Elevator / Lift issues</option>
                            <option value="Carpentry">Carpentry & Structural</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Grievance Priority</label>
                        <select wire:model="complaintPriority" 
                                class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                            <option value="Low">Low (No rush)</option>
                            <option value="Medium">Medium (Fix in 24h)</option>
                            <option value="High">High (Needs immediate attention)</option>
                            <option value="Critical">Critical (Power loss, flooding)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Detailed Description</label>
                        <textarea wire:model="complaintDescription" rows="4" placeholder="Detail the issue, location, and severity..." 
                                  class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                        Lodge Grievance
                    </button>
                </form>
            </div>

            <!-- Grievance Directory -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">My Submitted Grievances & Status Logs</h3>
                <div class="space-y-4">
                    @forelse($complaints as $ticket)
                        <div class="p-4 rounded-lg bg-white border border-slate-200 shadow-sm">
                            <div class="flex justify-between items-start gap-4 mb-3">
                                <div>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider {{ 
                                        $ticket->priority === 'Critical' ? 'bg-red-500/10 text-red-600 border border-red-500/20' : (
                                        $ticket->priority === 'High' ? 'bg-amber-500/10 text-amber-600 border border-amber-500/20' : 'bg-slate-100 text-slate-500 border') 
                                    }}">{{ $ticket->priority }} Priority</span>
                                    <h4 class="text-xs font-bold text-slate-900 mt-2">{{ $ticket->category }} Issue</h4>
                                    <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">{{ $ticket->description }}</p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-200 text-[8px] font-bold uppercase tracking-wider">{{ $ticket->status }}</span>
                            </div>

                            <!-- Live pipeline tracking indicator -->
                            <div class="border-t border-slate-100 pt-3 mt-3">
                                <h5 class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-2">Claim Resolution Progress</h5>
                                <div class="flex items-center gap-1.5 text-[9px] font-semibold">
                                    <span class="px-1.5 py-0.5 rounded bg-blue-600 text-white">Reported</span>
                                    <span class="text-slate-300">➔</span>
                                    <span class="px-1.5 py-0.5 rounded {{ in_array($ticket->status, ['assigned', 'resolved', 'closed']) ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-400' }}">Assigned</span>
                                    <span class="text-slate-300">➔</span>
                                    <span class="px-1.5 py-0.5 rounded {{ in_array($ticket->status, ['resolved', 'closed']) ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }}">Resolved</span>
                                    <span class="text-slate-300">➔</span>
                                    <span class="px-1.5 py-0.5 rounded {{ $ticket->status === 'closed' ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-400' }}">Closed</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-6">No grievances logged from your unit.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 3: DEMOCRATIC SOCIETY PROPOSALS -->
    @if($activeTab === 'proposals')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Proposal Form -->
            <div class="lg:col-span-1 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4 border-b pb-1">Submit Upgrade Proposal</h3>
                <form wire:submit.prevent="submitProposal" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Proposal Title</label>
                        <input type="text" wire:model="proposalTitle" placeholder="e.g. Electric Vehicle Charging Station" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Detailed Description</label>
                        <textarea wire:model="proposalDescription" rows="4" placeholder="Detail the justification, benefits for society, and installation scope..." 
                                  class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Estimated Cost (INR)</label>
                        <input type="number" wire:model="proposalBudget" placeholder="e.g. 150000" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                        Submit Proposal to Board
                    </button>
                </form>
            </div>

            <!-- Proposal List -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">My Submitted Society Proposals</h3>
                <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1.5">
                    @forelse(\App\Modules\Planning\Models\Proposal::where('created_by', auth()->id())->orderBy('created_at', 'desc')->get() as $p)
                        <div class="p-3.5 rounded-lg bg-white border border-slate-200 shadow-sm">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 leading-snug">{{ $p->title }}</h4>
                                    <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">{{ $p->description }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-200 text-[8px] font-bold uppercase tracking-wider shrink-0">{{ str_replace('_', ' ', $p->status) }}</span>
                            </div>
                            <div class="flex justify-between items-center mt-3 text-[10px] text-slate-500">
                                <span>Requested Budget: <strong class="text-slate-700 font-semibold">{{ format_indian_currency($p->budget) }}</strong></span>
                                <span>{{ $p->created_at ? $p->created_at->format('M d, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-6">You haven't submitted any upgrades yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 4: SUBMETERS & BILLS -->
    @if($activeTab === 'billing')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Submission Form -->
            <div class="lg:col-span-1 flex flex-col gap-6">
                <!-- Submit bill form -->
                <div class="glass-panel p-6 rounded-xl shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 border-b pb-1">Register Submeter Bill</h3>
                    <form wire:submit.prevent="submitElectricityBill" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Meter Number (Registered)</label>
                            <input type="text" wire:model="meterNumber" class="w-full text-xs p-2.5 rounded bg-slate-100 border border-slate-200 text-slate-500 focus:outline-none" readonly>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Submeter Number</label>
                            <input type="text" wire:model="submeterNumber" class="w-full text-xs p-2.5 rounded bg-slate-100 border border-slate-200 text-slate-500 focus:outline-none" readonly>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Billing Month</label>
                                <select wire:model="billingMonth" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                                    <option value="August 2026">August 2026</option>
                                    <option value="July 2026">July 2026</option>
                                    <option value="June 2026">June 2026</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Amount (INR)</label>
                                <input type="number" wire:model="billAmount" placeholder="e.g. 1850" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                                @error('billAmount') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                            Submit Reading Slip
                        </button>
                    </form>
                </div>

                <!-- Personal document upload vault -->
                <div id="upload-doc" class="glass-panel p-6 rounded-xl shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 border-b pb-1">Vault File Archive</h3>
                    <form wire:submit.prevent="uploadDocument" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Document Name</label>
                            <input type="text" wire:model="docName" placeholder="e.g. Flat Sale Deed copy" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Category</label>
                            <select wire:model="docCategory" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                                <option value="Property Deed">Property Sale Deed</option>
                                <option value="Mutation Copy">Mutation Copy</option>
                                <option value="Property Tax Slips">Property Tax Slip</option>
                                <option value="Meter NOC">NOC Certificate</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-1">Description / Remarks</label>
                            <textarea wire:model="docDescription" rows="2" placeholder="e.g. Registered in New Town registry" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none" required></textarea>
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                            Archive Document
                        </button>
                    </form>
                </div>
            </div>

            <!-- Bills History -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Electricity Reading & Payment History</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-600">
                        <thead class="bg-slate-100/80 text-slate-500 uppercase text-[8px] tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="p-3">Billing Month</th>
                                <th class="p-3">Meter Details</th>
                                <th class="p-3 text-right">Amount</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Receipt Slip</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/60">
                            @forelse($bills as $b)
                                <tr class="hover:bg-slate-50/40 transition-colors">
                                    <td class="p-3 font-bold text-slate-800">{{ $b->billing_month }}</td>
                                    <td class="p-3 text-slate-500 font-semibold">
                                        Meter: {{ $b->meter_number }}
                                        <span class="text-[9px] text-slate-400 block font-mono">Submeter: {{ $b->submeter_number ?: '--' }}</span>
                                    </td>
                                    <td class="p-3 text-right font-extrabold text-slate-700">{{ format_indian_currency($b->amount) }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase {{ $b->status === 'pending' ? 'bg-amber-500/10 text-amber-600 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' }}">
                                            {{ $b->status }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right text-blue-600 font-semibold hover:underline">
                                        <a href="#" onclick="alert('Viewing bill payment slip at: {{ $b->receipt_path }}')">View Slip</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-4 text-center text-slate-400 italic">No bill payments recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 5: EXPERIENCES BOARD (MICRO SOCIAL FEED) -->
    @if($activeTab === 'experiences')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Share Form -->
            <div class="lg:col-span-1 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4 border-b pb-1">Share Your Experience</h3>
                <form wire:submit.prevent="shareExperience" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Experience Note</label>
                        <textarea wire:model="postContent" rows="5" placeholder="Share notices, reviews, festive decorations praises, or feedback..." class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required></textarea>
                        @error('postContent') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                        Post Experience Message
                    </button>
                </form>
            </div>

            <!-- Experience Feed -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm flex flex-col gap-4">
                <h3 class="text-sm font-bold text-slate-800 mb-2">Society Community Feed</h3>
                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-1">
                    @forelse($communityPosts as $post)
                        <div class="p-4 rounded-xl border border-slate-200 bg-white/40 flex items-start gap-4">
                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center font-bold text-white uppercase text-xs shrink-0">
                                {{ substr($post->person->name ?? 'R', 0, 2) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-slate-900">{{ $post->person->name ?? 'Resident' }}</h4>
                                    <span class="text-[8px] text-slate-400">• {{ $post->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">{{ $post->content }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-6">No experiences shared yet. Be the first!</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 6: MEETING FEEDBACK REGISTRATION -->
    @if($activeTab === 'feedback')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Feedback Form -->
            <div class="lg:col-span-1 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4 border-b pb-1">Register Meeting Feedback</h3>
                <form wire:submit.prevent="submitMeetingFeedback" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Select General Body Meeting</label>
                        <select wire:model="selectedMeetingId" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                            <option value="">-- Select Meeting --</option>
                            @foreach($meetings as $mtg)
                                <option value="{{ $mtg->id }}">{{ $mtg->meeting_type }} ({{ $mtg->date->format('M d, Y') }})</option>
                            @endforeach
                        </select>
                        @error('selectedMeetingId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Your Feedback / Remarks</label>
                        <textarea wire:model="feedbackText" rows="5" placeholder="Share your suggestions or notes regarding resolutions passed..." class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none" required></textarea>
                        @error('feedbackText') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                        Register Remarks
                    </button>
                </form>
            </div>

            <!-- Meetings details log -->
            <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Recent Governance Sittings & AGM Board Agenda</h3>
                <div class="space-y-4">
                    @forelse($meetings as $mtg)
                        <div class="p-4 rounded-lg bg-white border border-slate-200 shadow-sm">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-200 text-[8px] font-bold uppercase tracking-wider">{{ $mtg->meeting_type }}</span>
                                    <h4 class="text-xs font-bold text-slate-900 mt-2">Venue: {{ $mtg->value ?? $mtg->venue }}</h4>
                                    <p class="text-[10px] text-slate-500 mt-1 leading-relaxed"><strong>Agenda:</strong> {{ $mtg->agenda }}</p>
                                </div>
                                <span class="text-[10px] font-bold text-slate-500 shrink-0">{{ $mtg->date->format('M d, Y') }} at {{ $mtg->time }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center py-6">No governance meetings scheduled.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
