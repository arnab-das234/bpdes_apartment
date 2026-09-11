@props([
    'bills' => collect(),
    'title' => 'Sub-meter Electricity Bill Collection & WBSEDCL Calculator',
    'subtitle' => null,
    'height' => '420px',
    'showActions' => true,
])

@php
    $totalBilled = $bills->sum(fn ($bill) => (float) ($bill->amount ?? 0));
    $approvedTotal = $bills
        ->filter(fn ($bill) => $bill->status === 'approved')
        ->sum(fn ($bill) => (float) ($bill->amount ?? 0));
    $pendingTotal = max($totalBilled - $approvedTotal, 0);

    // Fetch all units registered with a sub-meter connection across the society
    $submeterFlats = \App\Models\Unit::with(['building', 'memberships.person', 'electricityBills'])
        ->where(function($q) {
            $q->where('electricity_connection_type', 'submeter')
              ->orWhereNotNull('submeter_number');
        })
        ->orderBy('flat_number', 'asc')
        ->get();

    $submeterResidentCount = $submeterFlats->count();

    // Pull Livewire state properties safely via $this
    $showSubmeterModal = $this->showSubmeterModal ?? false;
    $showSubmeterBreakdownModal = $this->showSubmeterBreakdownModal ?? false;
    $selectedSubmeterBillId = $this->selectedSubmeterBillId ?? null;
    $submeterUnitId = $this->submeterUnitId ?? null;
    $submeterBillingMonth = $this->submeterBillingMonth ?? date('F Y');

    // Real-time calculation parameters inside modal
    $calcTotalFlats = (int)($this->calcTotalFlats ?? 17);
    $calcCommonMeterUnits = (int)($this->calcCommonMeterUnits ?? 1136);
    $calcEnergyCharge = (float)($this->calcEnergyCharge ?? 8601.14);
    $calcElectricityDuty = (float)($this->calcElectricityDuty ?? 945.78);
    $calcFixedCharge = (float)($this->calcFixedCharge ?? 952.20);
    $calcMeterRent = (float)($this->calcMeterRent ?? 90.00);
    $calcLpscExclusion = (float)($this->calcLpscExclusion ?? 0.00);
    $calcBillingCycleMonths = (int)($this->calcBillingCycleMonths ?? 3);

    $submeterPrevReading = (int)($this->submeterPrevReading ?? 1200);
    $submeterCurrReading = (int)($this->submeterCurrReading ?? 1384);

    // WBSEDCL Real-time formulas
    $safeUnits = max($calcCommonMeterUnits, 1);
    $safeFlats = max($calcTotalFlats, 1);
    $grossBill = $calcEnergyCharge + $calcElectricityDuty + $calcFixedCharge + $calcMeterRent - $calcLpscExclusion;
    $energyRate = $calcEnergyCharge / $safeUnits;
    $dutyRate = $calcElectricityDuty / $safeUnits;
    $recRate = ($calcEnergyCharge + $calcElectricityDuty) / $safeUnits;

    $totalFixed = $calcFixedCharge + $calcMeterRent;
    $fixedShare3M = $totalFixed / $safeFlats;
    $fixedShare1M = $fixedShare3M / max($calcBillingCycleMonths, 1);

    $consumedUnits = max($submeterCurrReading - $submeterPrevReading, 0);
    $personalCharge = round($consumedUnits * $recRate, 2);
    $commonPool = max($grossBill - $personalCharge, 0);
    $commonSharePerFlat = round($commonPool / $safeFlats, 2);
    $submeterTotalPayable = round($personalCharge + $commonSharePerFlat, 2);

    $selectedBill = $selectedSubmeterBillId ? $bills->firstWhere('id', $selectedSubmeterBillId) : null;
@endphp

<div x-data="{ activeSubTab: 'flats' }" class="glass-panel rounded-xl shadow-sm overflow-hidden bg-white border border-slate-200">
    <!-- Header Section with Stat Cards -->
    <div class="flex flex-col gap-4 border-b border-slate-200 p-6 bg-slate-50/80">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded text-[9px] font-black uppercase tracking-widest bg-amber-100 text-amber-900 border border-amber-300">
                        WBSEDCL SUB-METER CALCULATOR
                    </span>
                    <h3 class="text-base font-black text-slate-900 tracking-tight">{{ $title }}</h3>
                </div>
                @if($subtitle)
                    <p class="text-xs text-slate-500 mt-1 font-medium">{{ $subtitle }}</p>
                @else
                    <p class="text-xs text-slate-500 mt-1 font-medium">Calculates personal submeter units, energy/duty rates, common expense pool, and total payable per flat.</p>
                @endif
            </div>

            <!-- Toggle View & Build Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap shrink-0">
                <div class="flex rounded-lg bg-slate-200/80 p-0.5 border border-slate-300">
                    <button type="button" @click="activeSubTab = 'flats'" 
                            :class="activeSubTab === 'flats' ? 'bg-white text-slate-900 font-black shadow-2xs' : 'text-slate-600 font-bold hover:text-slate-900'"
                            class="px-3 py-1 text-xs rounded-md transition-all">
                        ⚡ Sub-meter Holders ({{ $submeterResidentCount }})
                    </button>
                    <button type="button" @click="activeSubTab = 'ledger'" 
                            :class="activeSubTab === 'ledger' ? 'bg-white text-slate-900 font-black shadow-2xs' : 'text-slate-600 font-bold hover:text-slate-900'"
                            class="px-3 py-1 text-xs rounded-md transition-all">
                        📋 Collection Ledger ({{ $bills->count() }})
                    </button>
                </div>

                @if($showActions)
                    <button type="button" wire:click="openSubmeterModal(null)" 
                            class="px-3 py-1.5 rounded-lg text-xs font-black bg-amber-600 hover:bg-amber-700 text-white shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>⚡</span> + Build Sub-meter Bill Entry
                    </button>
                @endif
            </div>
        </div>

        <!-- 4-Grid Summary Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
            <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs">
                <span class="uppercase tracking-wider text-slate-500 font-extrabold text-[9px] block">Sub-meter Holders:</span>
                <strong class="text-slate-900 font-black text-base tracking-tight block mt-0.5">{{ $submeterResidentCount }} Flats</strong>
                <span class="text-[10px] text-slate-400 font-medium block mt-1">Registered sub-meters</span>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 shadow-2xs">
                <span class="uppercase tracking-wider text-slate-500 font-extrabold text-[9px] block">Total Billed Dues:</span>
                <strong class="text-slate-900 font-black text-base tracking-tight block mt-0.5">{{ format_indian_currency($totalBilled) }}</strong>
                <span class="text-[10px] text-slate-400 font-medium block mt-1">Sub-meter energy charges</span>
            </div>

            <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200 shadow-2xs">
                <span class="uppercase tracking-wider text-emerald-800 font-extrabold text-[9px] block">Approved / Collected:</span>
                <strong class="text-emerald-900 font-black text-base tracking-tight block mt-0.5">{{ format_indian_currency($approvedTotal) }}</strong>
                <span class="text-[10px] text-emerald-700 font-bold block mt-1">Verified & cleared bills</span>
            </div>

            <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 shadow-2xs">
                <span class="uppercase tracking-wider text-amber-800 font-extrabold text-[9px] block">Pending Collection:</span>
                <strong class="text-amber-900 font-black text-base tracking-tight block mt-0.5">{{ format_indian_currency($pendingTotal) }}</strong>
                <span class="text-[10px] text-amber-700 font-medium block mt-1">Unbilled / unverified dues</span>
            </div>
        </div>
    </div>

    <!-- SUB-TAB 1: REGISTERED SUB-METER FLATS DIRECTORY & CALCULATOR QUICK DESK -->
    <div x-show="activeSubTab === 'flats'" class="overflow-x-auto">
        <table class="w-full text-xs text-left text-slate-600">
            <thead class="sticky top-0 z-10 bg-slate-100 text-slate-500 uppercase text-[9px] tracking-widest border-b border-slate-200 font-extrabold">
                <tr>
                    <th class="p-3.5">Flat & Resident</th>
                    <th class="p-3.5">Sub-meter Details</th>
                    <th class="p-3.5 text-right">Latest Bill Amount</th>
                    <th class="p-3.5">Latest Bill Month</th>
                    <th class="p-3.5">Status</th>
                    <th class="p-3.5 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/70 bg-white">
                @forelse($submeterFlats as $unit)
                    @php
                        $resident = $unit->memberships->first()?->person?->name ?? 'Unassigned Resident';
                        $latestBill = $unit->electricityBills->sortByDesc('created_at')->first();
                        $status = $latestBill?->status ?? 'No Bill Entry';
                        $statusClass = $status === 'approved' 
                            ? 'bg-emerald-100 text-emerald-800 border-emerald-300' 
                            : ($status === 'pending' ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-slate-100 text-slate-600 border-slate-300');
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3.5">
                            <div class="font-black text-slate-900 text-xs">Flat {{ $unit->flat_number }}</div>
                            <div class="text-[11px] font-bold text-slate-800 mt-0.5">{{ $resident }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $unit->building?->name ?? 'Block' }} | Floor {{ $unit->floor ?? '-' }}</div>
                        </td>
                        <td class="p-3.5">
                            <span class="inline-flex rounded bg-amber-50 text-amber-900 border border-amber-300 px-2 py-0.5 text-[10px] font-mono font-bold">
                                ⚡ Sub: {{ $unit->submeter_number ?: 'Not Assigned' }}
                            </span>
                            @if($unit->meter_number)
                                <div class="text-[10px] text-slate-400 font-mono mt-1">Parent Meter: {{ $unit->meter_number }}</div>
                            @endif
                        </td>
                        <td class="p-3.5 text-right font-black text-slate-900">
                            {{ $latestBill ? format_indian_currency($latestBill->amount) : '₹ 0.00' }}
                        </td>
                        <td class="p-3.5 font-bold text-slate-800">
                            {{ $latestBill?->billing_month ?: '--' }}
                        </td>
                        <td class="p-3.5">
                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                @if($latestBill)
                                    <button type="button" wire:click="openSubmeterBreakdownModal('{{ $latestBill->id }}')" 
                                            class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-all flex items-center gap-1 cursor-pointer">
                                        <span>👁️</span> Breakdown
                                    </button>
                                @endif
                                @if($showActions)
                                    <button type="button" wire:click="openSubmeterModal('{{ $unit->id }}')" 
                                            class="px-3 py-1 rounded-lg text-xs font-black bg-amber-600 hover:bg-amber-700 text-white shadow-2xs transition-all cursor-pointer">
                                        + Record Bill Entry
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400 italic">No flats registered with a sub-meter electricity connection. Go to <strong>Residents & Owners Registry</strong> in Control Center to set flat connection type to Sub-meter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- SUB-TAB 2: SUB-METER BILL HISTORY LEDGER -->
    <div x-show="activeSubTab === 'ledger'" class="overflow-x-auto">
        <table class="w-full text-xs text-left text-slate-600">
            <thead class="sticky top-0 z-10 bg-slate-100 text-slate-500 uppercase text-[9px] tracking-widest border-b border-slate-200 font-extrabold">
                <tr>
                    <th class="p-3.5">Flat & Resident</th>
                    <th class="p-3.5">Billing Month</th>
                    <th class="p-3.5">Sub-meter Units</th>
                    <th class="p-3.5 text-right">Rec. Rate / Unit</th>
                    <th class="p-3.5 text-right">Personal Charge</th>
                    <th class="p-3.5 text-right">Common Share</th>
                    <th class="p-3.5 text-right">Total Payable</th>
                    <th class="p-3.5">Status</th>
                    <th class="p-3.5 text-center">Breakdown</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/70 bg-white">
                @forelse($bills as $bill)
                    @php
                        $unit = $bill->unit;
                        $resident = $unit?->memberships?->first()?->person?->name ?? 'Unassigned';
                        $statusClass = $bill->status === 'approved'
                            ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
                            : 'bg-amber-100 text-amber-900 border-amber-300';
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3.5">
                            <div class="font-black text-slate-900 text-xs">Flat {{ $unit?->flat_number ?? '--' }}</div>
                            <div class="text-[11px] font-bold text-slate-800 mt-0.5">{{ $resident }}</div>
                        </td>
                        <td class="p-3.5 font-bold text-slate-800">{{ $bill->billing_month }}</td>
                        <td class="p-3.5 font-bold text-slate-700">
                            {{ $bill->units_consumed ?: '--' }} kWh
                            <span class="text-[9px] text-slate-400 block font-mono">Sub: {{ $bill->submeter_number ?: '--' }}</span>
                        </td>
                        <td class="p-3.5 text-right font-mono font-bold text-amber-900">
                            {{ $bill->recommended_rate_per_unit ? '₹ ' . number_format($bill->recommended_rate_per_unit, 4) : '--' }}
                        </td>
                        <td class="p-3.5 text-right font-bold text-slate-800">
                            {{ $bill->personal_charge ? format_indian_currency($bill->personal_charge) : format_indian_currency($bill->amount) }}
                        </td>
                        <td class="p-3.5 text-right font-bold text-slate-700">
                            {{ $bill->common_share ? format_indian_currency($bill->common_share) : '--' }}
                        </td>
                        <td class="p-3.5 text-right font-black text-emerald-700">
                            {{ format_indian_currency($bill->amount) }}
                        </td>
                        <td class="p-3.5">
                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $statusClass }}">
                                {{ $bill->status }}
                            </span>
                        </td>
                        <td class="p-3.5 text-center">
                            <button type="button" wire:click="openSubmeterBreakdownModal('{{ $bill->id }}')" 
                                    class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-all flex items-center gap-1 mx-auto cursor-pointer">
                                <span>👁️</span> View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-400 italic">No sub-meter electricity bill entry recorded yet. Click <strong>"+ Build Sub-meter Bill Entry"</strong> above to record a sub-meter bill.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL 1: BUILD SUB-METER ELECTRICITY BILL ENTRY (WBSEDCL CALCULATOR) -->
    @if($showSubmeterModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs text-slate-800 overflow-hidden">
            <div style="max-height: calc(100vh - 4rem); height: auto;" 
                 class="w-full max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-2xl flex flex-col overflow-hidden relative mx-auto my-auto">
                <!-- Header (100% Fixed at top with crisp dark typography) -->
                <div style="flex-shrink: 0;" class="px-5 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-100/90 text-slate-900">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest bg-amber-600 text-white shadow-xs">
                            ⚡ WBSEDCL SUB-METER CALCULATOR
                        </span>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight">
                            Build Sub-meter Electricity Bill Entry
                        </h3>
                    </div>
                    <button type="button" wire:click="closeSubmeterModal" 
                            class="w-7 h-7 rounded-lg bg-slate-200/80 hover:bg-slate-300 text-slate-600 hover:text-slate-900 font-bold flex items-center justify-center transition-all text-lg leading-none cursor-pointer">
                        ×
                    </button>
                </div>

                <!-- Body / Form (Scrollable Container inside fixed box) -->
                <div style="flex: 1 1 auto; overflow-y: auto; min-height: 0;" class="p-4 sm:p-5 space-y-4 bg-white text-slate-800 text-xs">
                    <!-- SECTION 1: WBSEDCL COMMON METER MAIN BILL INPUTS -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <h4 class="text-xs font-black uppercase text-slate-800 flex items-center justify-between border-b border-slate-200 pb-2">
                            <span class="flex items-center gap-1.5">
                                <span>📄</span> INPUTS FROM WBSEDCL COMMON METER BILL
                            </span>
                            @php
                                $dbFlats = \App\Models\Unit::count();
                                $activeTotalFlats = ($calcTotalFlats ?? 0) > 0 ? $calcTotalFlats : ($dbFlats > 0 ? $dbFlats : 17);
                            @endphp
                            <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Auto-Fetched: {{ $activeTotalFlats }} Society Flats
                            </span>
                        </h4>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[10px] font-extrabold text-slate-700 uppercase">Total Flats / Members *</label>
                                    <span class="text-[8px] font-black text-emerald-600 uppercase tracking-wider bg-emerald-50 px-1 py-0.5 rounded border border-emerald-200">Auto-Fetched</span>
                                </div>
                                <input type="number" wire:model.live="calcTotalFlats" placeholder="17" 
                                       class="w-full text-xs p-2 rounded bg-emerald-50/40 border border-emerald-300 font-bold text-emerald-950 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                <span class="text-[8px] text-slate-400 block mt-0.5">Automatically synced with registered flats database</span>
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Common Total Units *</label>
                                <input type="number" wire:model.live="calcCommonMeterUnits" placeholder="1136" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Energy Charge (₹) *</label>
                                <input type="number" step="0.01" wire:model.live="calcEnergyCharge" placeholder="8601.14" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Electricity Duty (₹) *</label>
                                <input type="number" step="0.01" wire:model.live="calcElectricityDuty" placeholder="945.78" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Fixed / Demand Charge (₹)</label>
                                <input type="number" step="0.01" wire:model.live="calcFixedCharge" placeholder="952.20" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-semibold text-slate-900 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Meter Rent (₹)</label>
                                <input type="number" step="0.01" wire:model.live="calcMeterRent" placeholder="90.00" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-semibold text-slate-900 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">LPSC / Exclusion (₹)</label>
                                <input type="number" step="0.01" wire:model.live="calcLpscExclusion" placeholder="0.00" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-semibold text-slate-900 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Billing Cycle (Months)</label>
                                <select wire:model.live="calcBillingCycleMonths" class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-semibold text-slate-900 focus:border-blue-500">
                                    <option value="1">1 Month</option>
                                    <option value="2">2 Months</option>
                                    <option value="3">3 Months (Quarterly)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Real-time Computed Rates Banner -->
                        <div class="p-3 bg-amber-500/10 rounded-lg border border-amber-300 grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                            <div>
                                <span class="text-[9px] font-extrabold text-amber-900 uppercase block">Energy Rate / Unit</span>
                                <strong class="font-mono text-amber-950 font-black">₹ {{ number_format($energyRate, 4) }}</strong>
                            </div>
                            <div>
                                <span class="text-[9px] font-extrabold text-amber-900 uppercase block">Duty Rate / Unit</span>
                                <strong class="font-mono text-amber-950 font-black">₹ {{ number_format($dutyRate, 4) }}</strong>
                            </div>
                            <div>
                                <span class="text-[9px] font-extrabold text-amber-900 uppercase block">Rec. Sub-meter Rate</span>
                                <strong class="font-mono text-amber-950 font-black">₹ {{ number_format($recRate, 4) }} / unit</strong>
                            </div>
                            <div>
                                <span class="text-[9px] font-extrabold text-amber-900 uppercase block">Gross Bill Amount</span>
                                <strong class="font-mono text-slate-900 font-black">₹ {{ number_format($grossBill, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: SUB-METER HOLDER FLAT READINGS & CONSUMPTION -->
                    <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200 space-y-3">
                        <h4 class="text-xs font-black uppercase text-blue-950 flex items-center gap-1.5 border-b border-blue-200 pb-2">
                            <span>🏠</span> SUB-METER HOLDER READINGS & CONSUMPTION
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Select Sub-meter Flat & Resident *</label>
                                <select wire:model.live="submeterUnitId" class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                                    <option value="">-- Choose Sub-meter Resident Flat --</option>
                                    @foreach($submeterFlats as $u)
                                        @php $res = $u->memberships->first()?->person?->name ?? 'Unassigned'; @endphp
                                        <option value="{{ $u->id }}">Flat {{ $u->flat_number }} - {{ $res }} (Submeter: {{ $u->submeter_number ?: 'Assigned' }})</option>
                                    @endforeach
                                </select>
                                @error('submeterUnitId') <span class="text-rose-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Billing Month & Year *</label>
                                <input type="text" wire:model="submeterBillingMonth" placeholder="e.g. September 2026" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Approval & Collection Status</label>
                                <select wire:model="submeterStatus" class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                                    <option value="approved">Approved & Verified (Active)</option>
                                    <option value="pending">Pending Review / Unverified</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Previous Sub-meter Reading *</label>
                                <input type="number" wire:model.live="submeterPrevReading" placeholder="1200" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Current Sub-meter Reading *</label>
                                <input type="number" wire:model.live="submeterCurrReading" placeholder="1384" 
                                       class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Computed Consumption Units Box -->
                        <div class="p-3 bg-white rounded-lg border border-blue-200 flex justify-between items-center text-xs">
                            <span class="font-extrabold uppercase text-[10px] text-blue-900">Sub-meter Personal Consumption Units:</span>
                            <strong class="text-base font-black text-blue-700 font-mono">{{ $consumedUnits }} kWh / Units</strong>
                        </div>
                    </div>

                    <!-- SECTION 3: FINAL CALCULATED PAYABLE BREAKDOWN SUMMARY -->
                    <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-300 space-y-2.5">
                        <h4 class="text-xs font-black uppercase text-emerald-950 flex items-center justify-between border-b border-emerald-200 pb-2">
                            <span>📊 FINAL PAYABLE SUMMARY FOR SUB-METER RESIDENT</span>
                            <span class="text-xs font-mono font-black text-emerald-800">TOTAL: ₹ {{ number_format($submeterTotalPayable, 2) }}</span>
                        </h4>

                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-slate-700">Sub-meter Personal Electricity Charge ({{ $consumedUnits }} units × ₹{{ number_format($recRate, 4) }}):</span>
                                <strong class="font-mono text-slate-900 font-black">₹ {{ number_format($personalCharge, 2) }}</strong>
                            </div>

                            <div class="flex justify-between items-center text-amber-900">
                                <span class="font-semibold">Sub-meter Variable Cost Removed from Pool:</span>
                                <strong class="font-mono font-black">- ₹ {{ number_format($personalCharge, 2) }}</strong>
                            </div>

                            <div class="flex justify-between items-center text-slate-700">
                                <span class="font-semibold">Actual Common Expense Pool (Gross ₹{{ number_format($grossBill, 2) }} - ₹{{ number_format($personalCharge, 2) }}):</span>
                                <strong class="font-mono text-slate-900 font-bold">₹ {{ number_format($commonPool, 2) }}</strong>
                            </div>

                            <div class="flex justify-between items-center text-blue-900">
                                <span class="font-semibold">Common Share Per Flat (Pool ÷ {{ $safeFlats }} flats):</span>
                                <strong class="font-mono font-black">₹ {{ number_format($commonSharePerFlat, 2) }}</strong>
                            </div>

                            <div class="pt-2 border-t border-emerald-300 flex justify-between items-center text-sm">
                                <span class="font-black uppercase text-emerald-950">Sub-meter Resident Total Payable:</span>
                                <strong class="font-mono font-black text-emerald-800 text-base">₹ {{ number_format($submeterTotalPayable, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer (100% Fixed at bottom) -->
                <div style="flex-shrink: 0;" class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeSubmeterModal" class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveSubmeterBillEntry" class="px-5 py-2 rounded-lg text-xs font-black bg-amber-600 hover:bg-amber-700 text-white shadow-md transition-all cursor-pointer">
                        Save Sub-meter Bill Entry (₹ {{ number_format($submeterTotalPayable, 2) }})
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: VIEW COMPLETE WBSEDCL BREAKDOWN MODAL -->
    @if($showSubmeterBreakdownModal && $selectedBill)
        @php
            $bUnits = max((int)($selectedBill->common_meter_total_units ?: 1136), 1);
            $bFlats = max((int)($selectedBill->total_flats_count ?: 17), 1);
            $bEnergy = (float)($selectedBill->energy_charge ?: 8601.14);
            $bDuty = (float)($selectedBill->electricity_duty ?: 945.78);
            $bFixed = (float)($selectedBill->fixed_charge ?: 952.20);
            $bRent = (float)($selectedBill->meter_rent ?: 90.00);
            $bGross = (float)($selectedBill->gross_bill_amount ?: ($bEnergy + $bDuty + $bFixed + $bRent));
            $bRecRate = (float)($selectedBill->recommended_rate_per_unit ?: (($bEnergy + $bDuty) / $bUnits));
            $bPersonal = (float)($selectedBill->personal_charge ?: ($selectedBill->units_consumed * $bRecRate));
            $bCommonShare = (float)($selectedBill->common_share ?: (max($bGross - $bPersonal, 0) / $bFlats));
            $bTotalPayable = (float)($selectedBill->amount ?: ($bPersonal + $bCommonShare));
            $resName = $selectedBill->unit?->memberships?->first()?->person?->name ?? 'Sub-meter Resident';
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs text-slate-800 overflow-hidden">
            <div style="max-height: calc(100vh - 4rem); height: auto;" 
                 class="w-full max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-2xl flex flex-col overflow-hidden relative mx-auto my-auto">
                <!-- Header (100% Fixed at top) -->
                <div style="flex-shrink: 0;" class="px-5 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-100/90 text-slate-900">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[9px] font-black uppercase tracking-widest bg-amber-600 text-white shadow-xs">
                                📊 WBSEDCL BREAKDOWN LEDGER
                            </span>
                            <h3 class="text-sm font-black text-slate-900">
                                Flat {{ $selectedBill->unit?->flat_number ?? '--' }} - {{ $resName }}
                            </h3>
                        </div>
                        <p class="text-[11px] text-slate-600 mt-0.5 font-medium">
                            Billing Month: <strong>{{ $selectedBill->billing_month }}</strong> • Sub-meter: <strong>{{ $selectedBill->submeter_number }}</strong>
                        </p>
                    </div>
                    <button type="button" wire:click="closeSubmeterBreakdownModal" 
                            class="w-7 h-7 rounded-lg bg-slate-200/80 hover:bg-slate-300 text-slate-600 hover:text-slate-900 font-bold flex items-center justify-center transition-all text-lg leading-none cursor-pointer">
                        ×
                    </button>
                </div>

                <!-- Body (Scrollable Container) -->
                <div style="flex: 1 1 auto; overflow-y: auto; min-height: 0;" class="p-4 sm:p-5 space-y-4 bg-white text-slate-800 text-xs">
                    <!-- Main Calculation Table matching exact user spec -->
                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <div class="bg-slate-900 text-white p-3 font-extrabold text-xs uppercase tracking-wider">
                            WBSEDCL Sub-meter & Common Electricity Charge Calculator
                        </div>

                        <table class="w-full text-xs text-left">
                            <tbody class="divide-y divide-slate-200">
                                <tr class="bg-slate-100 font-black text-slate-800 text-[10px] uppercase">
                                    <td class="p-2.5" colspan="3">INPUTS FROM WBSEDCL COMMON METER BILL</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Total Flats / Common Share Members</td>
                                    <td class="p-2.5 font-mono font-black text-slate-900 text-right">{{ $bFlats }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">members</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Common Meter Total Units</td>
                                    <td class="p-2.5 font-mono font-black text-slate-900 text-right">{{ $bUnits }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">kWh / units</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Energy Charge</td>
                                    <td class="p-2.5 font-mono font-bold text-slate-900 text-right">₹ {{ number_format($bEnergy, 2) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Electricity Duty</td>
                                    <td class="p-2.5 font-mono font-bold text-slate-900 text-right">₹ {{ number_format($bDuty, 2) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Fixed / Demand Charge</td>
                                    <td class="p-2.5 font-mono font-bold text-slate-900 text-right">₹ {{ number_format($bFixed, 2) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Meter Rent</td>
                                    <td class="p-2.5 font-mono font-bold text-slate-900 text-right">₹ {{ number_format($bRent, 2) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹</td>
                                </tr>
                                <tr class="bg-slate-50 font-black">
                                    <td class="p-2.5 text-slate-900">Gross Bill Amount</td>
                                    <td class="p-2.5 font-mono text-slate-900 text-right">₹ {{ number_format($bGross, 2) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Sub-meter Units (Billing Period)</td>
                                    <td class="p-2.5 font-mono font-black text-amber-900 text-right">{{ $selectedBill->units_consumed ?: 184 }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">units</td>
                                </tr>

                                <tr class="bg-slate-100 font-black text-slate-800 text-[10px] uppercase">
                                    <td class="p-2.5" colspan="3">CALCULATED SUB-METER RATE</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Energy Rate / Unit</td>
                                    <td class="p-2.5 font-mono font-bold text-slate-900 text-right">₹ {{ number_format($bEnergy / $bUnits, 4) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹/unit</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Duty Rate / Unit</td>
                                    <td class="p-2.5 font-mono font-bold text-slate-900 text-right">₹ {{ number_format($bDuty / $bUnits, 4) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹/unit</td>
                                </tr>
                                <tr class="bg-amber-50 font-black text-amber-950">
                                    <td class="p-2.5">Recommended Sub-meter Rate / Unit</td>
                                    <td class="p-2.5 font-mono text-right">₹ {{ number_format($bRecRate, 4) }}</td>
                                    <td class="p-2.5 text-amber-800 text-[10px]">₹/unit</td>
                                </tr>
                                <tr class="bg-amber-100/70 font-black text-amber-950">
                                    <td class="p-2.5">Sub-meter Personal Electricity Charge</td>
                                    <td class="p-2.5 font-mono text-right text-sm">₹ {{ number_format($bPersonal, 2) }}</td>
                                    <td class="p-2.5 text-amber-900 text-[10px]">₹</td>
                                </tr>

                                <tr class="bg-slate-100 font-black text-slate-800 text-[10px] uppercase">
                                    <td class="p-2.5" colspan="3">COMMON EXPENSE CALCULATION</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Sub-meter Variable Cost Removed</td>
                                    <td class="p-2.5 font-mono font-bold text-slate-900 text-right">₹ {{ number_format($bPersonal, 2) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹</td>
                                </tr>
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-700">Actual Common Expense Pool</td>
                                    <td class="p-2.5 font-mono font-bold text-slate-900 text-right">₹ {{ number_format(max($bGross - $bPersonal, 0), 2) }}</td>
                                    <td class="p-2.5 text-slate-500 text-[10px]">₹ (Gross − Personal)</td>
                                </tr>
                                <tr class="bg-blue-50 font-black text-blue-950">
                                    <td class="p-2.5">Common Share Per Flat</td>
                                    <td class="p-2.5 font-mono text-right">₹ {{ number_format($bCommonShare, 2) }}</td>
                                    <td class="p-2.5 text-blue-900 text-[10px]">₹/flat</td>
                                </tr>

                                <tr class="bg-emerald-100 font-black text-emerald-950 text-sm">
                                    <td class="p-3 uppercase">FINAL TOTAL PAYABLE FOR RESIDENT</td>
                                    <td class="p-3 font-mono text-right text-base">₹ {{ number_format($bTotalPayable, 2) }}</td>
                                    <td class="p-3 text-emerald-900 text-[10px]">Personal + Common</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer (100% Fixed at bottom) -->
                <div style="flex-shrink: 0;" class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                    <button type="button" wire:click="closeSubmeterBreakdownModal" class="px-5 py-2 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white transition-all cursor-pointer">
                        Close Breakdown
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
