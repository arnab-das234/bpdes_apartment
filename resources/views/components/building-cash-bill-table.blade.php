@props([
    'bills' => collect(),
    'persons' => collect(),
    'units' => collect(),
    'proposals' => collect(),
    'projects' => collect(),
    'milestones' => collect(),
    'title' => 'Building Cash Release & Expense Bill Desk',
    'subtitle' => 'Cashier, Treasurer & President workflow for recording purchase bills, assigning responsible residents, and releasing cash from the main building collection fund.',
    'showModal' => false,
    'showPrintModal' => false,
    'selectedVoucherId' => null,
    'billFormStep' => 1,
])

@php
    $billFormStep = $this->billFormStep ?? $billFormStep ?? 1;
@endphp

<div class="space-y-6">
    <!-- Top Header & Summary Statistics Desk -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex justify-between items-center flex-wrap gap-3 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-900 border border-emerald-300">
                        🏛️ CASHIER / TREASURER & PRESIDENT WORKFLOW
                    </span>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">{{ $title }}</h2>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">{{ $subtitle }}</p>
            </div>
            
            <button type="button" wire:click="openCashBillModal" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                + Record Expense Bill & Release Cash
            </button>
        </div>

        <!-- Single Horizontal Line Summary Bar -->
        <div class="grid grid-cols-3 divide-x divide-slate-200/80 rounded-xl bg-slate-50 border border-slate-200/80 p-3">
            <div class="px-3 flex items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Released Cash</span>
                    <strong class="text-sm sm:text-base font-black text-rose-700 font-mono block leading-tight">
                        {{ format_indian_currency($bills->where('status', '!=', 'CANCELLED')->sum('amount')) }}
                    </strong>
                </div>
                <span class="text-[9px] text-slate-400 font-medium hidden md:inline">Disbursed Fund</span>
            </div>

            <div class="px-3 flex items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Recorded Vouchers</span>
                    <strong class="text-sm sm:text-base font-black text-blue-900 block leading-tight">
                        {{ $bills->count() }} Bills
                    </strong>
                </div>
                <span class="text-[9px] text-slate-400 font-medium hidden md:inline">Linked Residents</span>
            </div>

            <div class="px-3 flex items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Primary Cash Source</span>
                    <strong class="text-xs sm:text-sm font-black text-emerald-800 block leading-tight truncate">
                        Main Cash Collection Fund
                    </strong>
                </div>
                <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 hidden lg:inline-block">Active</span>
            </div>
        </div>
    </div>

    <!-- Expense Bills Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex justify-between items-center flex-wrap gap-2 pb-2 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                📋 Cash Release & Building Expense Vouchers
            </h3>
            <span class="text-xs font-bold text-slate-500">
                Showing {{ $bills->count() }} entries
            </span>
        </div>

        <div class="overflow-x-auto max-h-[520px] overflow-y-auto pr-1">
            <table class="w-full text-xs text-left text-slate-600">
                <thead class="bg-slate-100/90 text-slate-500 uppercase text-[9px] tracking-widest border-b border-slate-200 sticky top-0 z-10 backdrop-blur-xs">
                    <tr>
                        <th class="p-3">Voucher #</th>
                        <th class="p-3">Expense Title & Category</th>
                        <th class="p-3">Responsible Resident</th>
                        <th class="p-3">Bill Date</th>
                        <th class="p-3 text-right">Released Cash (₹)</th>
                        <th class="p-3">Source Fund</th>
                        <th class="p-3">Document Proof</th>
                        <th class="p-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3 font-mono text-[10px] font-bold text-slate-700">
                                {{ $bill->voucher_number }}
                                @if($bill->receipt_ref)
                                    <span class="block text-[9px] text-slate-400 font-normal">Ref: {{ $bill->receipt_ref }}</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <strong class="text-xs font-black text-slate-900 block leading-tight">{{ $bill->title }}</strong>
                                <div class="flex items-center gap-1 flex-wrap mt-1">
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase bg-slate-200/70 text-slate-700 border border-slate-300 inline-block">
                                        {{ $bill->category }}
                                    </span>
                                    @if($bill->proposal)
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            💡 {{ $bill->proposal->title }}
                                        </span>
                                    @endif
                                    @if($bill->project)
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-blue-100 text-blue-900 border border-blue-300">
                                            🏗️ {{ $bill->project->title }}
                                        </span>
                                    @endif
                                    @if($bill->milestone)
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-purple-100 text-purple-900 border border-purple-300">
                                            🎯 {{ $bill->milestone->title }}
                                        </span>
                                    @endif
                                </div>
                                @if($bill->vendor_name)
                                    <span class="text-[9px] text-slate-500 block mt-0.5">Vendor: {{ $bill->vendor_name }}</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-6 w-6 rounded-full bg-blue-100 text-blue-800 font-black text-[10px] flex items-center justify-center shrink-0">
                                        👤
                                    </span>
                                    <div>
                                        <strong class="text-xs font-bold text-slate-800 block">{{ $bill->responsible_person_name ?: 'Building Management' }}</strong>
                                        @if($bill->unit)
                                            <span class="text-[9px] text-slate-500 font-semibold block">Flat {{ $bill->unit->flat_number }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 font-semibold text-slate-700">
                                {{ $bill->bill_date ? $bill->bill_date->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="p-3 text-right font-mono font-extrabold text-sm text-rose-700">
                                {{ format_indian_currency($bill->amount) }}
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    {{ $bill->fund_source }}
                                </span>
                            </td>
                            <td class="p-3">
                                @if($bill->bill_document_path)
                                    <a href="{{ Storage::url($bill->bill_document_path) }}" target="_blank" 
                                       class="inline-flex items-center gap-1 text-[9px] font-black text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded border border-blue-200 transition-all">
                                        📄 View Bill File
                                    </a>
                                @else
                                    <span class="text-[9px] text-slate-400 italic">No File</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="openPrintVoucherModal('{{ $bill->id }}')" 
                                            class="px-2 py-1 rounded-md text-[10px] font-black bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-all cursor-pointer">
                                        🖨️ Print Voucher
                                    </button>
                                    <button type="button" wire:click="deleteCashBillEntry('{{ $bill->id }}')" 
                                            onclick="return confirm('Are you sure you want to delete this cash release bill record?')"
                                            class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1 cursor-pointer">
                                        🗑
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 italic text-xs">
                                No expense bills recorded or cash released yet. Click "+ Record Expense Bill & Release Cash" to add an entry.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Record Cash Release & Expense Bill Modal (3-Step Wizard Stepper) -->
    @if($showModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 overflow-y-auto">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col p-6 my-auto">
                
                <!-- Fixed Header with 3-Step Progress Stepper -->
                <div class="space-y-3 pb-3 border-b border-slate-100 shrink-0">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="px-2.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                                💸 CASH RELEASE & EXPENSE DESK
                            </span>
                            <h3 class="text-base font-black text-slate-900 mt-1">Record Expense Bill & Release Cash</h3>
                        </div>
                        <button type="button" wire:click="closeCashBillModal" class="text-slate-400 hover:text-slate-700 text-lg font-black cursor-pointer">
                            ✕
                        </button>
                    </div>

                    <!-- Stepper Header Tabs -->
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="setBillFormStep(1)"
                                class="flex items-center gap-2 p-2 rounded-xl text-left border transition-all cursor-pointer {{ $billFormStep === 1 ? 'bg-emerald-50 border-emerald-500 text-emerald-900 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100' }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0 {{ $billFormStep === 1 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">1</span>
                            <span class="text-[10px] font-bold truncate">Bill Particulars</span>
                        </button>

                        <button type="button" wire:click="setBillFormStep(2)"
                                class="flex items-center gap-2 p-2 rounded-xl text-left border transition-all cursor-pointer {{ $billFormStep === 2 ? 'bg-emerald-50 border-emerald-500 text-emerald-900 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100' }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0 {{ $billFormStep === 2 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">2</span>
                            <span class="text-[10px] font-bold truncate">Work & Resident</span>
                        </button>

                        <button type="button" wire:click="setBillFormStep(3)"
                                class="flex items-center gap-2 p-2 rounded-xl text-left border transition-all cursor-pointer {{ $billFormStep === 3 ? 'bg-emerald-50 border-emerald-500 text-emerald-900 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-500 hover:bg-slate-100' }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0 {{ $billFormStep === 3 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">3</span>
                            <span class="text-[10px] font-bold truncate">Treasury & Proof</span>
                        </button>
                    </div>
                </div>

                <!-- Form Stepper Body -->
                <form wire:submit.prevent="saveCashBillEntry" class="flex flex-col flex-1 min-h-0 mt-3 space-y-4">
                    
                    <div class="overflow-y-auto pr-1 flex-1 max-h-[55vh]">
                        @if($billFormStep === 1)
                            <!-- Step 1: Bill Particulars & Disbursed Amount -->
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-3">
                                <span class="text-[10px] font-black text-slate-700 uppercase tracking-wider block border-b border-slate-200 pb-1">
                                    📋 Step 1 of 3: Bill Particulars & Disbursed Amount
                                </span>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Voucher # *</label>
                                        <input type="text" wire:model="billVoucherNumber" required
                                               class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-mono font-bold focus:border-blue-500">
                                        @error('billVoucherNumber') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Expense Category *</label>
                                        <select wire:model="billCategory" required
                                                class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-semibold focus:border-blue-500">
                                            <option value="Maintenance & Repairs">Maintenance & Repairs</option>
                                            <option value="Utilities & Electricity">Utilities & Electricity</option>
                                            <option value="Security & Guard Allowance">Security & Guard Allowance</option>
                                            <option value="Water Pump & Sanitation">Water Pump & Sanitation</option>
                                            <option value="Equipment & Hardware">Equipment & Hardware</option>
                                            <option value="Miscellaneous">Miscellaneous</option>
                                        </select>
                                        @error('billCategory') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Bill / Purchase Title *</label>
                                        <input type="text" wire:model="billTitle" placeholder="e.g. Water Pump Motor Repair & Pipe Replacement" required
                                               class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-semibold focus:border-blue-500">
                                        @error('billTitle') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Disbursed Amount (₹) *</label>
                                        <input type="number" step="0.01" min="1" wire:model="billAmount" placeholder="0.00" required
                                               class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-rose-700 font-mono font-extrabold focus:border-blue-500">
                                        @error('billAmount') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Bill / Expense Date *</label>
                                        <input type="date" wire:model="billDate" required
                                               class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-medium focus:border-blue-500">
                                        @error('billDate') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                        @elseif($billFormStep === 2)
                            <!-- Step 2: Work Context & Resident Tagging -->
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-3">
                                <span class="text-[10px] font-black text-slate-700 uppercase tracking-wider block border-b border-slate-200 pb-1">
                                    🔗 Step 2 of 3: Work Context & Responsible Resident
                                </span>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-[9px] font-extrabold text-slate-600 uppercase mb-1">Linked Proposal</label>
                                        <select wire:model.live="billProposalId"
                                                class="w-full text-xs p-2 rounded-lg bg-white border border-slate-300 text-slate-900 font-medium focus:border-blue-500">
                                            <option value="">-- No Proposal --</option>
                                            @foreach($proposals as $prop)
                                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[9px] font-extrabold text-slate-600 uppercase mb-1">Linked Project</label>
                                        <select wire:model.live="billProjectId"
                                                class="w-full text-xs p-2 rounded-lg bg-white border border-slate-300 text-slate-900 font-medium focus:border-blue-500">
                                            <option value="">-- No Project --</option>
                                            @foreach($projects as $proj)
                                                <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[9px] font-extrabold text-slate-600 uppercase mb-1">Linked Milestone</label>
                                        <select wire:model.live="billMilestoneId"
                                                class="w-full text-xs p-2 rounded-lg bg-white border border-slate-300 text-slate-900 font-medium focus:border-blue-500">
                                            <option value="">-- No Milestone --</option>
                                            @foreach($milestones->when($this->billProjectId, fn($ms) => $ms->where('project_id', $this->billProjectId)) as $ms)
                                                <option value="{{ $ms->id }}">{{ $ms->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Responsible Resident *</label>
                                        <select wire:model="billResponsiblePersonId" 
                                                 class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-semibold focus:border-blue-500">
                                            <option value="">-- Select Resident --</option>
                                            @foreach($persons as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->mobile ?: 'Member' }})</option>
                                            @endforeach
                                        </select>
                                        <span class="text-[9px] text-slate-400 mt-0.5 block">Resident who made purchase & received cash release.</span>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Linked Flat / Unit (Optional)</label>
                                        <select wire:model="billUnitId" 
                                                class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-medium focus:border-blue-500">
                                            <option value="">-- Select Flat --</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">Flat {{ $u->flat_number }} ({{ $u->building->name ?? 'Main' }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                        @elseif($billFormStep === 3)
                            <!-- Step 3: Treasury, Vendor & Proof Attachment -->
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-3">
                                <span class="text-[10px] font-black text-slate-700 uppercase tracking-wider block border-b border-slate-200 pb-1">
                                    💳 Step 3 of 3: Treasury Account, Vendor & Receipt Attachment
                                </span>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Cash Source Account *</label>
                                        <select wire:model="billFundSource" required
                                                class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-bold focus:border-blue-500">
                                            <option value="Main Cash Collection Fund">Main Cash Collection Fund</option>
                                            <option value="General Reserve Fund">General Reserve Fund</option>
                                            <option value="Petty Cash Fund">Petty Cash Fund</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Vendor / Shop Name</label>
                                        <input type="text" wire:model="billVendorName" placeholder="e.g. Bengal Hardware" 
                                               class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-medium focus:border-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Vendor Bill / Ref No.</label>
                                        <input type="text" wire:model="billReceiptRef" placeholder="e.g. INV-98402" 
                                               class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-medium focus:border-blue-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Attach Bill / Receipt Proof (Image or PDF)</label>
                                    <input type="file" wire:model="billDocument" accept="image/*,application/pdf"
                                           class="w-full text-xs p-2 rounded-lg bg-white border border-slate-200 text-slate-700 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-extrabold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                    <div wire:loading wire:target="billDocument" class="text-[10px] font-bold text-blue-600 mt-1">
                                        ⏳ Uploading bill document...
                                    </div>
                                    @error('billDocument') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Scope / Remarks / Notes</label>
                                    <textarea wire:model="billRemarks" rows="2" placeholder="Detail purchase notes or scope..." 
                                              class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-medium focus:border-blue-500"></textarea>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="flex gap-2 justify-between items-center pt-3 border-t border-slate-100 shrink-0 mt-2">
                        @if($billFormStep > 1)
                            <button type="button" wire:click="prevBillFormStep" 
                                    class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs cursor-pointer flex items-center gap-1">
                                ⬅ Back
                            </button>
                        @else
                            <button type="button" wire:click="closeCashBillModal" 
                                    class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs cursor-pointer">
                                Cancel
                            </button>
                        @endif

                        @if($billFormStep < 3)
                            <button type="button" wire:click="nextBillFormStep" 
                                    class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-xs shadow-md transition-all flex items-center gap-1 cursor-pointer">
                                <span>Next: {{ $billFormStep === 1 ? 'Work & Resident' : 'Treasury & Proof' }} ➔</span>
                            </button>
                        @else
                            <button type="submit" 
                                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer">
                                <span>💸 Complete & Disburse Cash</span>
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Printable Signature Cash Disbursement Voucher Receipt Modal -->
    @if($showPrintModal && $selectedVoucherId)
        @php
            $vBill = $bills->firstWhere('id', $selectedVoucherId);
        @endphp
        @if($vBill)
            <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 z-50 overflow-y-auto">
                <div class="bg-white rounded-lg border border-slate-300 shadow-xl w-full max-w-2xl p-6 space-y-6 my-6 print:shadow-none print:border-none print:m-0 print:p-0 print:w-full print:max-w-none">
                    
                    <!-- Print-only style override to guarantee 1 single page print without UI clutter -->
                    <style>
                        @media print {
                            body * {
                                visibility: hidden !important;
                            }
                            #printableVoucherVessel, #printableVoucherVessel * {
                                visibility: visible !important;
                            }
                            #printableVoucherVessel {
                                position: absolute !important;
                                left: 0 !important;
                                top: 0 !important;
                                width: 100% !important;
                                padding: 0 !important;
                                margin: 0 !important;
                                background: white !important;
                                color: black !important;
                            }
                            @page {
                                size: A4 portrait;
                                margin: 12mm;
                            }
                        }
                    </style>

                    <div id="printableVoucherVessel" class="bg-white text-black font-sans space-y-4">
                        <!-- Simple Header -->
                        <div class="text-center border-b-2 border-black pb-3">
                            <h1 class="text-xl font-bold uppercase tracking-wide text-black m-0 p-0">NARMADA HOUSING COOPERATIVE SOCIETY</h1>
                            <h2 class="text-sm font-bold uppercase tracking-wider text-black mt-1 mb-0 p-0">CASH DISBURSEMENT & EXPENSE VOUCHER</h2>
                        </div>

                        <!-- Plain Simple HTML Table for Details -->
                        <table class="w-full text-xs text-left border-collapse border border-black my-2">
                            <tbody>
                                <tr class="border-b border-black">
                                    <td class="p-2 border-r border-black font-bold bg-white text-black w-1/4">Voucher No:</td>
                                    <td class="p-2 border-r border-black font-mono font-bold text-black w-1/4">{{ $vBill->voucher_number }}</td>
                                    <td class="p-2 border-r border-black font-bold bg-white text-black w-1/4">Voucher Date:</td>
                                    <td class="p-2 font-bold text-black w-1/4">{{ $vBill->bill_date ? $vBill->bill_date->format('d/m/Y') : now()->format('d/m/Y') }}</td>
                                </tr>
                                <tr class="border-b border-black">
                                    <td class="p-2 border-r border-black font-bold bg-white text-black">Source Fund:</td>
                                    <td class="p-2 border-r border-black text-black">{{ $vBill->fund_source }}</td>
                                    <td class="p-2 border-r border-black font-bold bg-white text-black">Expense Category:</td>
                                    <td class="p-2 text-black">{{ $vBill->category }}</td>
                                </tr>
                                <tr class="border-b border-black">
                                    <td class="p-2 border-r border-black font-bold bg-white text-black">Vendor / Supplier:</td>
                                    <td class="p-2 border-r border-black text-black" colspan="3">
                                        {{ $vBill->vendor_name ?: 'N/A' }} 
                                        @if($vBill->receipt_ref) (Bill Ref: {{ $vBill->receipt_ref }}) @endif
                                    </td>
                                </tr>
                                <tr class="border-b border-black">
                                    <td class="p-2 border-r border-black font-bold bg-white text-black">Responsible Resident:</td>
                                    <td class="p-2 border-r border-black text-black" colspan="3">
                                        <strong>{{ $vBill->responsible_person_name }}</strong>
                                        @if($vBill->unit)
                                            (Flat {{ $vBill->unit->flat_number }})
                                        @endif
                                    </td>
                                </tr>
                                @if($vBill->proposal || $vBill->project || $vBill->milestone)
                                    <tr class="border-b border-black">
                                        <td class="p-2 border-r border-black font-bold bg-white text-black">Linked Work Context:</td>
                                        <td class="p-2 text-black" colspan="3">
                                            @if($vBill->proposal)
                                                <span><strong>Proposal:</strong> {{ $vBill->proposal->title }}</span> &nbsp;
                                            @endif
                                            @if($vBill->project)
                                                <span><strong>Project:</strong> {{ $vBill->project->title }}</span> &nbsp;
                                            @endif
                                            @if($vBill->milestone)
                                                <span><strong>Milestone:</strong> {{ $vBill->milestone->title }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                <tr class="border-b border-black">
                                    <td class="p-2 border-r border-black font-bold bg-white text-black">Particulars / Details:</td>
                                    <td class="p-2 text-black" colspan="3">
                                        <strong>{{ $vBill->title }}</strong>
                                        @if($vBill->remarks)
                                            <br><span class="text-[11px]">Note: {{ $vBill->remarks }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="p-2 border-r border-black font-bold bg-white text-black text-sm" colspan="2">Total Released Cash Amount:</td>
                                    <td class="p-2 font-black text-black font-mono text-base text-right" colspan="2">₹ {{ number_format($vBill->amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Simple Signature Table -->
                        <div class="pt-10 mt-6">
                            <table class="w-full text-center text-xs border-none">
                                <tbody>
                                    <tr>
                                        <td class="w-1/3 align-bottom px-2">
                                            <div class="border-t border-black pt-1 font-bold text-black">
                                                Prepared By<br>
                                                <span class="font-normal text-[10px]">(Cashier / Treasurer)</span>
                                            </div>
                                        </td>
                                        <td class="w-1/3 align-bottom px-2">
                                            <div class="border-t border-black pt-1 font-bold text-black">
                                                Resident / Receiver Signature<br>
                                                <span class="font-normal text-[10px]">({{ $vBill->responsible_person_name }})</span>
                                            </div>
                                        </td>
                                        <td class="w-1/3 align-bottom px-2">
                                            <div class="border-t border-black pt-1 font-bold text-black">
                                                Authorized Signatory<br>
                                                <span class="font-normal text-[10px]">(President Approval)</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal Actions (Hidden during print) -->
                    <div class="flex gap-3 justify-end pt-4 border-t border-slate-200 print:hidden">
                        <button type="button" wire:click="closePrintVoucherModal" 
                                class="px-4 py-2 rounded bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs cursor-pointer">
                            Close
                        </button>
                        <button type="button" onclick="window.print()" 
                                class="px-5 py-2 rounded bg-slate-900 hover:bg-black text-white font-bold text-xs shadow transition-all flex items-center gap-2 cursor-pointer">
                            🖨️ Print Voucher
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
