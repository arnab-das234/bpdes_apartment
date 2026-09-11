@props([
    'units' => collect(),
    'monthlyBreakdown' => collect(),
    'title' => 'Total Maintenance Collection & Resident Backlog Desk',
    'subtitle' => null,
    'height' => '420px',
    'showActions' => true,
])

@php
    // Summary calculations across all resident flats
    $totalCalculatedTarget = $units->sum('calculated_total_maintenance');
    $totalPaid = $units->sum('calculated_total_paid');
    $totalOutstanding = $units->sum('calculated_outstanding');
    $totalBacklogs = $units->sum('calculated_backlog_dues');
    $collectionPercentage = $totalCalculatedTarget > 0 ? round(($totalPaid / $totalCalculatedTarget) * 100, 1) : 0;

    // Pull Livewire state properties safely via $this
    $showEntryModal = $this->showEntryModal ?? false;
    $showBreakdownModal = $this->showBreakdownModal ?? false;
    $breakdownUnitId = $this->breakdownUnitId ?? null;

    $selectedBreakdownUnit = ($showBreakdownModal && $breakdownUnitId)
        ? $units->firstWhere('id', $breakdownUnitId)
        : null;
@endphp

<div x-data="{ activeSubTab: 'flats', displayMode: 'table' }" class="glass-panel rounded-2xl shadow-sm overflow-hidden bg-white border border-slate-200">
    <!-- Header Section with Stat Cards -->
    <div class="flex flex-col gap-5 border-b border-slate-200 p-5 sm:p-6 bg-slate-100/90 text-slate-900">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="px-3 py-1 rounded-md text-[9px] font-black uppercase tracking-widest bg-blue-600 text-white shadow-xs">
                        ⚡ MONTH-WISE LEDGER & BACKLOGS
                    </span>
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">{{ $title }}</h3>
                </div>
                @if($subtitle)
                    <p class="text-xs text-slate-600 font-bold mt-1 flex items-center gap-1.5">
                        <span>ℹ️</span> {{ $subtitle }}
                    </p>
                @else
                    <p class="text-xs text-slate-600 font-bold mt-1 flex items-center gap-1.5">
                        <span>ℹ️</span> Single row per flat. Click <strong>"👁️ Breakdown"</strong> against any flat to view its full month-wise billing history.
                    </p>
                @endif
            </div>

            <!-- View Mode Toggle & Executive Quick Actions -->
            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                <!-- Display Mode Switcher (Tabular vs Card View) -->
                <div x-show="activeSubTab === 'flats'" class="flex rounded-xl bg-slate-200/90 p-1 border border-slate-300 shadow-inner">
                    <button type="button" @click="displayMode = 'table'" 
                            :class="displayMode === 'table' ? 'bg-white text-slate-950 font-black shadow-xs border border-slate-300' : 'text-slate-700 font-bold hover:text-slate-950 hover:bg-slate-200/60'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>📋</span> Table View
                    </button>
                    <button type="button" @click="displayMode = 'card'" 
                            :class="displayMode === 'card' ? 'bg-white text-slate-950 font-black shadow-xs border border-slate-300' : 'text-slate-700 font-bold hover:text-slate-950 hover:bg-slate-200/60'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>🎴 Card View
                    </button>
                </div>

                <!-- Sub-tab Navigation Pill Group -->
                <div class="flex rounded-xl bg-slate-200/90 p-1 border border-slate-300 shadow-inner">
                    <button type="button" @click="activeSubTab = 'flats'" 
                            :class="activeSubTab === 'flats' ? 'bg-white text-slate-950 font-black shadow-xs border border-slate-300' : 'text-slate-700 font-bold hover:text-slate-950 hover:bg-slate-200/60'"
                            class="px-3.5 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                        🏢 Resident Flats ({{ $units->count() }})
                    </button>
                    <button type="button" @click="activeSubTab = 'monthly'" 
                            :class="activeSubTab === 'monthly' ? 'bg-white text-slate-950 font-black shadow-xs border border-slate-300' : 'text-slate-700 font-bold hover:text-slate-950 hover:bg-slate-200/60'"
                            class="px-3.5 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                        📊 Society Monthly Breakdown
                    </button>
                </div>

                @if($showActions)
                    <!-- Build Maintenance Entry Button -->
                    <button type="button" wire:click="openEntryModal(null, false)" 
                            class="px-3.5 py-2 rounded-xl text-xs font-black bg-blue-100 hover:bg-blue-200 text-blue-950 shadow-xs border-2 border-blue-500 transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <svg class="w-3.5 h-3.5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        <span class="text-blue-950 font-black">+ Build Maintenance Entry</span>
                    </button>

                    <!-- Add Backlog Entry Button -->
                    <button type="button" wire:click="openEntryModal(null, true)" 
                            class="px-3.5 py-2 rounded-xl text-xs font-black bg-amber-100 hover:bg-amber-200 text-amber-950 shadow-xs border-2 border-amber-500 transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <svg class="w-3.5 h-3.5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-amber-950 font-black">+ Add Backlog Entry</span>
                    </button>

                    <!-- Batch Generate Monthly Bills -->
                    <button type="button" wire:click="generateMonthlyEntriesForAllFlats" 
                            class="px-3.5 py-2 rounded-xl text-xs font-black bg-emerald-100 hover:bg-emerald-200 text-emerald-950 shadow-xs border-2 border-emerald-500 transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <span class="bg-emerald-700 text-white px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider shadow-2xs">⚡ AUTO</span>
                        <span class="text-emerald-950 font-black tracking-tight">Generate Monthly Bills</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- 4-Grid Calculated Maintenance Stat Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 pt-1">
            <!-- Target Calculated Maintenance -->
            <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs">
                <span class="uppercase tracking-wider text-slate-600 font-black text-[9px] block">Total Target Calculated:</span>
                <strong class="text-slate-900 font-black text-lg tracking-tight block mt-0.5">{{ format_indian_currency($totalCalculatedTarget) }}</strong>
                <span class="text-[10px] text-slate-500 font-bold block mt-1">Monthly base + backlogs</span>
            </div>

            <!-- Total Paid -->
            <div class="p-4 rounded-xl bg-emerald-50/90 border border-emerald-300 shadow-2xs">
                <span class="uppercase tracking-wider text-emerald-900 font-black text-[9px] block">Total Maintenance Paid:</span>
                <strong class="text-emerald-950 font-black text-lg tracking-tight block mt-0.5">{{ format_indian_currency($totalPaid) }}</strong>
                <div class="w-full bg-emerald-200 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min($collectionPercentage, 100) }}%"></div>
                </div>
            </div>

            <!-- Total Backlogs & Outstanding Dues -->
            <div class="p-4 rounded-xl bg-rose-50/90 border border-rose-300 shadow-2xs">
                <span class="uppercase tracking-wider text-rose-900 font-black text-[9px] block">Total Outstanding Balance:</span>
                <strong class="text-rose-950 font-black text-lg tracking-tight block mt-0.5">{{ format_indian_currency($totalOutstanding) }}</strong>
                <span class="text-[10px] text-rose-800 font-bold block mt-1">Due across {{ $units->where('calculated_outstanding', '>', 0)->count() }} flats</span>
            </div>

            <!-- Backlog Arrears Component -->
            <div class="p-4 rounded-xl bg-amber-50/90 border border-amber-300 shadow-2xs">
                <span class="uppercase tracking-wider text-amber-900 font-black text-[9px] block">Historical Backlog Arrears:</span>
                <strong class="text-amber-950 font-black text-lg tracking-tight block mt-0.5">{{ format_indian_currency($totalBacklogs) }}</strong>
                <span class="text-[10px] text-amber-800 font-bold block mt-1">Prior period unpaid dues</span>
            </div>
        </div>
    </div>

    <!-- SUB-TAB 1 (MODE A): MODERN COMPACT TABULAR VIEW -->
    <div x-show="activeSubTab === 'flats' && displayMode === 'table'" class="overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse">
            <thead class="sticky top-0 z-10 bg-slate-100/90 backdrop-blur-xs text-slate-500 uppercase text-[9px] tracking-wider border-b border-slate-200 font-black">
                <tr>
                    <th class="py-3 px-4">Flat & Resident</th>
                    <th class="py-3 px-3 text-right">Monthly Rate</th>
                    <th class="py-3 px-3 text-right">Total Target (Calc)</th>
                    <th class="py-3 px-3 text-right">Total Paid</th>
                    <th class="py-3 px-3 text-right">Outstanding Due</th>
                    <th class="py-3 px-3">Status & Backlog</th>
                    <th class="py-3 px-4 text-center">Month-Wise Breakdown</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/70 bg-white">
                @forelse($units as $unit)
                    @php
                        $monthly = (float) ($unit->monthly_maintenance_amount ?? 0);
                        $totalBilled = $unit->calculated_total_maintenance;
                        $paid = $unit->calculated_total_paid;
                        $outstanding = $unit->calculated_outstanding;
                        $backlogDues = $unit->calculated_backlog_dues;
                        $entriesCount = $unit->maintenanceEntries ? $unit->maintenanceEntries->count() : 0;

                        $status = $outstanding <= 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Due');
                        $statusClass = $status === 'Paid'
                            ? 'bg-emerald-100 text-emerald-900 border-emerald-300'
                            : ($status === 'Partial' ? 'bg-amber-100 text-amber-950 border-amber-300' : 'bg-rose-100 text-rose-950 border-rose-300');
                        $resident = $unit->memberships->first()?->person?->name ?? 'Unassigned Resident';
                    @endphp
                    <tr class="hover:bg-slate-50/90 transition-colors">
                        <!-- Flat & Resident -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-slate-900 text-white font-black flex items-center justify-center text-xs shadow-2xs shrink-0">
                                    {{ $unit->flat_number }}
                                </div>
                                <div>
                                    <div class="font-black text-slate-900 text-xs">{{ $resident }}</div>
                                    <div class="text-[10px] text-slate-500 font-semibold mt-0.5">
                                        {{ $unit->building?->name ?? 'Building' }} • Floor {{ $unit->floor ?? '-' }} ({{ $entriesCount }} entries)
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Monthly Rate -->
                        <td class="py-3 px-3 text-right">
                            <span class="inline-block px-2 py-1 rounded-md bg-slate-100 text-slate-800 font-bold text-[11px] border border-slate-200 font-mono">
                                ₹ {{ number_format($monthly, 0) }}/mo
                            </span>
                        </td>

                        <!-- Total Target (Calc) -->
                        <td class="py-3 px-3 text-right font-black text-slate-900 font-mono">
                            {{ format_indian_currency($totalBilled) }}
                        </td>

                        <!-- Total Paid -->
                        <td class="py-3 px-3 text-right font-black text-emerald-700 font-mono">
                            {{ format_indian_currency($paid) }}
                        </td>

                        <!-- Outstanding Due -->
                        <td class="py-3 px-3 text-right">
                            <span class="inline-block px-2 py-0.5 rounded-md font-mono font-black text-xs {{ $outstanding > 0 ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-slate-50 text-slate-400' }}">
                                {{ format_indian_currency($outstanding) }}
                            </span>
                        </td>

                        <!-- Status & Backlog -->
                        <td class="py-3 px-3">
                            <div class="flex flex-col items-start gap-1">
                                <span class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                                @if($backlogDues > 0)
                                    <span class="inline-flex items-center gap-1 rounded bg-amber-50 text-amber-900 border border-amber-300 px-1.5 py-0.5 text-[9px] font-extrabold">
                                        ⚠️ Backlog: {{ format_indian_currency($backlogDues) }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Month-Wise Breakdown & Actions (3 Dots Dropdown Menu) -->
                        <td class="py-3 px-4 text-center">
                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                <button type="button" @click="open = !open" @click.outside="open = false" 
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-black text-xs transition-all cursor-pointer border border-slate-300 shadow-2xs flex items-center gap-1">
                                    <span class="font-extrabold tracking-widest">•••</span>
                                </button>
                                
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-1 w-52 rounded-xl bg-white border border-slate-200 shadow-xl z-30 py-1.5 text-xs text-slate-800 divide-y divide-slate-100">
                                    
                                    <div class="py-1">
                                        <!-- 👁️ View Breakdown Link -->
                                        <button type="button" wire:click="openMonthlyBreakdownModal('{{ $unit->id }}')" @click="open = false"
                                                class="w-full px-3.5 py-2 text-left font-extrabold text-blue-950 hover:bg-blue-50 flex items-center gap-2 transition-colors cursor-pointer">
                                            <span class="text-sm">👁️</span>
                                            <span>View Breakdown ({{ $entriesCount }})</span>
                                        </button>

                                        <!-- 📜 Printable Statement Link -->
                                        <a href="{{ route('maintenance.statement', $unit->id) }}" target="_blank" @click="open = false"
                                           class="w-full px-3.5 py-2 text-left font-bold text-slate-800 hover:bg-slate-100 flex items-center gap-2 transition-colors cursor-pointer block">
                                            <span class="text-sm">📜</span>
                                            <span>View Dues Statement</span>
                                        </a>
                                    </div>

                                    @if($showActions)
                                        <div class="py-1">
                                            <!-- + Build Entry Link -->
                                            <button type="button" wire:click="openEntryModal('{{ $unit->id }}', false)" @click="open = false"
                                                    class="w-full px-3.5 py-2 text-left font-bold text-slate-800 hover:bg-emerald-50 hover:text-emerald-950 flex items-center gap-2 transition-colors cursor-pointer">
                                                <span class="text-sm">➕</span>
                                                <span>Add Maintenance Entry</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 italic">No maintenance contribution records available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- SUB-TAB 1 (MODE B): MODERN GRID CARD VIEW -->
    <div x-show="activeSubTab === 'flats' && displayMode === 'card'" class="p-4 sm:p-5 bg-slate-50/50">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($units as $unit)
                @php
                    $monthly = (float) ($unit->monthly_maintenance_amount ?? 0);
                    $totalBilled = $unit->calculated_total_maintenance;
                    $paid = $unit->calculated_total_paid;
                    $outstanding = $unit->calculated_outstanding;
                    $backlogDues = $unit->calculated_backlog_dues;
                    $entriesCount = $unit->maintenanceEntries ? $unit->maintenanceEntries->count() : 0;

                    $status = $outstanding <= 0 ? 'Paid' : ($paid > 0 ? 'Partial' : 'Due');
                    $statusClass = $status === 'Paid'
                        ? 'bg-emerald-100 text-emerald-900 border-emerald-300'
                        : ($status === 'Partial' ? 'bg-amber-100 text-amber-950 border-amber-300' : 'bg-rose-100 text-rose-950 border-rose-300');
                    $resident = $unit->memberships->first()?->person?->name ?? 'Unassigned Resident';
                    $pct = $totalBilled > 0 ? round(($paid / $totalBilled) * 100) : 0;
                @endphp
                <div class="p-4 bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between gap-3">
                    <div>
                        <!-- Header Pill & Badges -->
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-900 font-black text-xs shadow-2xs">
                                    Flat {{ $unit->flat_number }}
                                </span>
                                <span class="text-[11px] font-extrabold text-slate-600">
                                    {{ $unit->building?->name ?? 'Tower' }}
                                </span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </div>

                        <!-- Resident Info -->
                        <div class="mt-3">
                            <h4 class="text-sm font-black text-slate-900 leading-tight">{{ $resident }}</h4>
                            <p class="text-[10px] text-slate-500 font-semibold mt-0.5">Floor {{ $unit->floor ?? '-' }} • {{ $entriesCount }} Month Entries Registered</p>
                        </div>

                        <!-- Collection Progress Bar -->
                        <div class="mt-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                            <div class="flex justify-between items-center text-[10px] font-extrabold mb-1">
                                <span class="text-slate-600 uppercase">Collection Progress:</span>
                                <span class="{{ $pct >= 100 ? 'text-emerald-700' : 'text-slate-800' }}">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full {{ $pct >= 100 ? 'bg-emerald-600' : ($pct >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                        </div>

                        <!-- Financial Summary Cards -->
                        <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                            <div class="p-2 rounded-lg bg-slate-50 border border-slate-200">
                                <span class="text-[8px] font-black uppercase text-slate-400 block">Rate</span>
                                <strong class="text-xs font-black text-slate-800 font-mono block mt-0.5">₹ {{ number_format($monthly, 0) }}</strong>
                            </div>
                            <div class="p-2 rounded-lg bg-emerald-50 border border-emerald-200">
                                <span class="text-[8px] font-black uppercase text-emerald-800 block">Paid</span>
                                <strong class="text-xs font-black text-emerald-900 font-mono block mt-0.5">₹ {{ number_format($paid, 0) }}</strong>
                            </div>
                            <div class="p-2 rounded-lg {{ $outstanding > 0 ? 'bg-rose-50 border border-rose-200' : 'bg-slate-50 border border-slate-200' }}">
                                <span class="text-[8px] font-black uppercase {{ $outstanding > 0 ? 'text-rose-800' : 'text-slate-400' }} block">Due</span>
                                <strong class="text-xs font-black {{ $outstanding > 0 ? 'text-rose-900' : 'text-slate-400' }} font-mono block mt-0.5">₹ {{ number_format($outstanding, 0) }}</strong>
                            </div>
                        </div>

                        @if($backlogDues > 0)
                            <div class="mt-2.5 p-2 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-between text-[10px] text-amber-900 font-extrabold">
                                <span>⚠️ Backlog Arrears:</span>
                                <strong class="font-mono">₹ {{ number_format($backlogDues, 2) }}</strong>
                            </div>
                        @endif
                    </div>

                    <!-- Action Bar Footer -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5">
                        <button type="button" wire:click="openMonthlyBreakdownModal('{{ $unit->id }}')" 
                                class="flex-1 py-1.5 px-2 rounded-lg text-[11px] font-black bg-blue-50 hover:bg-blue-100 text-blue-950 border border-blue-300 transition-all flex items-center justify-center gap-1 cursor-pointer">
                            <span>👁️</span> Breakdown
                        </button>
                        <a href="{{ route('maintenance.statement', $unit->id) }}" target="_blank" 
                           class="py-1.5 px-2 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-all flex items-center justify-center gap-1 cursor-pointer">
                            <span>📜</span> Statement
                        </a>
                        @if($showActions)
                            <button type="button" wire:click="openEntryModal('{{ $unit->id }}', false)" 
                                    class="py-1.5 px-2 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition-all cursor-pointer">
                                + Entry
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center text-slate-400 italic">No resident flat records available.</div>
            @endforelse
        </div>
    </div>

    <!-- SUB-TAB 2: SOCIETY-WIDE MONTHLY BREAKDOWN TABLE -->
    <div x-show="activeSubTab === 'monthly'" class="overflow-x-auto">
        <table class="w-full text-xs text-left text-slate-600">
            <thead class="sticky top-0 z-10 bg-slate-100 text-slate-500 uppercase text-[9px] tracking-widest border-b border-slate-200 font-extrabold">
                <tr>
                    <th class="p-3.5">Month & Year</th>
                    <th class="p-3.5 text-center">Total Entries</th>
                    <th class="p-3.5 text-right">Target Billed</th>
                    <th class="p-3.5 text-right">Backlog Charges</th>
                    <th class="p-3.5 text-right">Collected Amount</th>
                    <th class="p-3.5 text-right">Outstanding Dues</th>
                    <th class="p-3.5 text-center">Collection Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200/70 bg-white">
                @forelse($monthlyBreakdown as $row)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3.5">
                            <div class="font-black text-slate-900 text-xs">{{ $row['month'] }}</div>
                        </td>
                        <td class="p-3.5 text-center font-bold text-slate-700">{{ $row['entries_count'] }} Flats</td>
                        <td class="p-3.5 text-right font-black text-slate-900">{{ format_indian_currency($row['target']) }}</td>
                        <td class="p-3.5 text-right font-bold text-amber-700">{{ format_indian_currency($row['backlogs']) }}</td>
                        <td class="p-3.5 text-right font-black text-emerald-700">{{ format_indian_currency($row['paid']) }}</td>
                        <td class="p-3.5 text-right font-black {{ $row['due'] > 0 ? 'text-rose-700' : 'text-slate-400' }}">
                            {{ format_indian_currency($row['due']) }}
                        </td>
                        <td class="p-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <span class="font-black text-xs {{ $row['rate'] >= 80 ? 'text-emerald-700' : ($row['rate'] >= 50 ? 'text-amber-700' : 'text-rose-700') }}">
                                    {{ $row['rate'] }}%
                                </span>
                                <div class="w-16 bg-slate-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $row['rate'] >= 80 ? 'bg-emerald-600' : ($row['rate'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min($row['rate'], 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 italic">No month-wise breakdown data registered yet. Click <strong>"+ Build Maintenance Entry"</strong> or <strong>"⚡ Generate Monthly Bills"</strong> above to record month-wise billing entries.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL 1: MAINTENANCE ENTRY & BACKLOG BUILDER MODAL -->
    @if($showEntryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs text-slate-800 overflow-hidden">
            <div style="max-height: calc(100vh - 4rem); height: auto;" 
                 class="w-full max-w-xl bg-white border border-slate-200 rounded-2xl shadow-2xl flex flex-col overflow-hidden relative mx-auto my-auto">
                <!-- Header (Crisp Dark Typography & Distinct Contrast) -->
                <div style="flex-shrink: 0;" class="px-5 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-100/90 text-slate-900">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest {{ $this->entryIsBacklog ? 'bg-amber-600 text-white' : 'bg-blue-600 text-white' }} shadow-xs">
                            {{ $this->entryIsBacklog ? '⚠️ BACKLOG ENTRY' : '📅 MONTHLY ENTRY' }}
                        </span>
                        <h3 class="text-sm font-black text-slate-900 tracking-tight">
                            {{ $this->entryIsBacklog ? 'Build Historical Backlog / Past Dues Entry' : 'Build Month-Wise Maintenance Entry' }}
                        </h3>
                    </div>
                    <button type="button" wire:click="closeEntryModal" 
                            class="w-7 h-7 rounded-lg bg-slate-200/80 hover:bg-slate-300 text-slate-600 hover:text-slate-900 font-bold flex items-center justify-center transition-all text-lg leading-none cursor-pointer">
                        ×
                    </button>
                </div>

                <!-- Body / Form (Scrollable Container inside fixed box) -->
                <div style="flex: 1 1 auto; overflow-y: auto; min-height: 0;" class="p-4 sm:p-5 space-y-4 bg-white text-slate-800 text-xs">
                    <!-- Historical Backlog Toggle Card -->
                    <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/60 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-black text-amber-950 block">Historical Backlog / Past Dues Entry?</span>
                            <span class="text-[10px] text-amber-800 font-medium block mt-0.5">Check if adding prior year/quarter arrears, late penalties, or opening backlog balances.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" wire:model.live="entryIsBacklog" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                        </label>
                    </div>

                    <!-- Flat & Resident Selection -->
                    <div>
                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Target Flat & Resident *</label>
                        <select wire:model.live="entryUnitId" class="w-full text-xs p-2.5 rounded-lg bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                            <option value="">-- Choose Flat Resident --</option>
                            @foreach($units as $u)
                                @php $res = $u->memberships->first()?->person?->name ?? 'Unassigned'; @endphp
                                <option value="{{ $u->id }}">Flat {{ $u->flat_number }} - {{ $res }} (Base Rate: ₹{{ number_format($u->monthly_maintenance_amount, 2) }}/mo)</option>
                            @endforeach
                        </select>
                        @error('entryUnitId') <span class="text-rose-500 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Billing Month & Year -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Billing Month *</label>
                            <select wire:model="entryBillingMonth" class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Billing Year *</label>
                            <select wire:model="entryBillingYear" class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>
                    </div>

                    <!-- Title & Due Date -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Entry Title *</label>
                            <input type="text" wire:model="entryTitle" placeholder="e.g. Monthly Maintenance" 
                                   class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-semibold text-slate-900 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Due Date</label>
                            <input type="date" wire:model="entryDueDate" 
                                   class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-semibold text-slate-900 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Financial Amounts Grid -->
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <h4 class="text-[10px] font-black uppercase text-slate-700 tracking-wider">Breakdown Financial Amounts</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <div>
                                <label class="block text-[9px] font-extrabold text-slate-600 uppercase mb-1">Base Amount (₹)</label>
                                <input type="number" step="0.01" wire:model.live="entryBaseAmount" 
                                       class="w-full text-xs p-1.5 rounded bg-white border border-slate-200 font-bold text-slate-900">
                            </div>
                            <div>
                                <label class="block text-[9px] font-extrabold text-amber-800 uppercase mb-1">Backlog Arrears (₹)</label>
                                <input type="number" step="0.01" wire:model.live="entryBacklogAmount" 
                                       class="w-full text-xs p-1.5 rounded bg-white border border-amber-300 font-bold text-amber-900">
                            </div>
                            <div>
                                <label class="block text-[9px] font-extrabold text-slate-600 uppercase mb-1">Late Fee (₹)</label>
                                <input type="number" step="0.01" wire:model.live="entryLateFee" 
                                       class="w-full text-xs p-1.5 rounded bg-white border border-slate-200 font-semibold text-slate-900">
                            </div>
                            <div>
                                <label class="block text-[9px] font-extrabold text-slate-600 uppercase mb-1">Utility Charge (₹)</label>
                                <input type="number" step="0.01" wire:model.live="entryUtilityCharge" 
                                       class="w-full text-xs p-1.5 rounded bg-white border border-slate-200 font-semibold text-slate-900">
                            </div>
                        </div>

                        <!-- Real-time Total Due Calculation Card -->
                        @php
                            $calcTotalDue = (float)($this->entryBaseAmount ?: 0) + (float)($this->entryBacklogAmount ?: 0) + (float)($this->entryLateFee ?: 0) + (float)($this->entryUtilityCharge ?: 0);
                        @endphp
                        <div class="p-2.5 bg-white rounded-lg border border-slate-200 flex justify-between items-center text-xs">
                            <span class="font-extrabold uppercase text-[10px] text-slate-600">Calculated Total Due Amount:</span>
                            <strong class="text-sm font-black text-slate-900 font-mono">₹ {{ number_format($calcTotalDue, 2) }}</strong>
                        </div>
                    </div>

                    <!-- Payment & Status Section -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Amount Paid Now (₹)</label>
                            <input type="number" step="0.01" wire:model.live="entryAmountPaid" placeholder="0.00" 
                                   class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-emerald-800 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Payment Mode</label>
                            <select wire:model="entryPaymentMode" class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-bold text-slate-900 focus:border-blue-500">
                                <option value="UPI">UPI / GPay / PhonePe</option>
                                <option value="Bank Transfer">NEFT / RTGS / IMPS</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <!-- Entry Remarks / Additional Notes -->
                    <div>
                        <label class="block text-[10px] font-extrabold text-slate-700 uppercase mb-1">Remarks / Additional Notes</label>
                        <input type="text" wire:model="entryRemarks" placeholder="e.g. Paid via GPay ref #123456, special discount approved" 
                               class="w-full text-xs p-2 rounded bg-white border border-slate-200 font-medium text-slate-900 focus:border-blue-500">
                    </div>
                </div>

                <!-- Footer (100% Fixed at bottom) -->
                <div style="flex-shrink: 0;" class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeEntryModal" class="px-4 py-2 rounded-lg text-xs font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveMaintenanceEntry" class="px-5 py-2 rounded-lg text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-md transition-all cursor-pointer">
                        Save Maintenance Entry (₹ {{ number_format($calcTotalDue, 2) }})
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: MONTHLY BREAKDOWN VIEW MODAL FOR SELECTED RESIDENT FLAT -->
    @if($showBreakdownModal && $selectedBreakdownUnit)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xs text-slate-800 overflow-hidden">
            <div style="max-height: calc(100vh - 4rem); height: auto;" 
                 class="w-full max-w-3xl bg-white border border-slate-200 rounded-2xl shadow-2xl flex flex-col overflow-hidden relative mx-auto my-auto">
                <!-- Header (100% Fixed at top) -->
                <div style="flex-shrink: 0;" class="px-5 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-100/90 text-slate-900">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[9px] font-black uppercase tracking-widest bg-blue-600 text-white shadow-xs">
                                📊 MONTH-WISE BREAKDOWN
                            </span>
                            <h3 class="text-sm font-black text-slate-900">
                                Flat {{ $selectedBreakdownUnit->flat_number }} - {{ $selectedBreakdownUnit->memberships->first()?->person?->name ?? 'Resident' }}
                            </h3>
                        </div>
                        <p class="text-[11px] text-slate-600 mt-0.5 font-medium">
                            Monthly Base Maintenance Rate: <strong>{{ format_indian_currency($selectedBreakdownUnit->monthly_maintenance_amount) }}/mo</strong>
                        </p>
                    </div>
                    <button type="button" wire:click="closeMonthlyBreakdownModal" 
                            class="w-7 h-7 rounded-lg bg-slate-200/80 hover:bg-slate-300 text-slate-600 hover:text-slate-900 font-bold flex items-center justify-center transition-all text-lg leading-none cursor-pointer">
                        ×
                    </button>
                </div>

                <!-- Body (Scrollable Container) -->
                <div style="flex: 1 1 auto; overflow-y: auto; min-height: 0;" class="p-4 sm:p-5 space-y-4 bg-white text-slate-800 text-xs">
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-100 text-slate-500 uppercase text-[9px] tracking-wider font-extrabold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Month / Entry</th>
                                    <th class="p-3">Type</th>
                                    <th class="p-3 text-right">Base</th>
                                    <th class="p-3 text-right">Backlog</th>
                                    <th class="p-3 text-right">Total Due</th>
                                    <th class="p-3 text-right">Paid</th>
                                    <th class="p-3 text-right">Outstanding</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse($selectedBreakdownUnit->maintenanceEntries as $entry)
                                    @php
                                        $due = max($entry->total_due - $entry->amount_paid, 0);
                                        $statusClass = $entry->status === 'Paid' 
                                            ? 'bg-emerald-100 text-emerald-800 border-emerald-300' 
                                            : ($entry->status === 'Partial' ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-rose-100 text-rose-800 border-rose-300');
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="p-3 font-bold text-slate-900">
                                            {{ $entry->month_name ?: $entry->billing_year }}
                                            <span class="text-[10px] text-slate-400 block font-normal">{{ $entry->title }}</span>
                                            @if($entry->remarks)
                                                <span class="text-[10px] text-slate-500 italic block mt-0.5">Note: {{ $entry->remarks }}</span>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            @if($entry->is_backlog)
                                                <span class="inline-flex rounded bg-amber-50 text-amber-800 border border-amber-300 px-1.5 py-0.5 text-[9px] font-bold">
                                                    ⚠️ Backlog
                                                </span>
                                            @else
                                                <span class="inline-flex rounded bg-blue-50 text-blue-800 border border-blue-200 px-1.5 py-0.5 text-[9px] font-bold">
                                                    📅 Monthly
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-right font-semibold text-slate-700">{{ format_indian_currency($entry->base_maintenance) }}</td>
                                        <td class="p-3 text-right font-semibold text-amber-700">{{ format_indian_currency($entry->backlog_amount) }}</td>
                                        <td class="p-3 text-right font-black text-slate-900">{{ format_indian_currency($entry->total_due) }}</td>
                                        <td class="p-3 text-right font-black text-emerald-700">{{ format_indian_currency($entry->amount_paid) }}</td>
                                        <td class="p-3 text-right font-black {{ $due > 0 ? 'text-rose-700' : 'text-slate-400' }}">{{ format_indian_currency($due) }}</td>
                                        <td class="p-3">
                                            <span class="inline-flex rounded border px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $statusClass }}">
                                                {{ $entry->status }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                <a href="{{ route('maintenance.invoice', $entry->id) }}" target="_blank" 
                                                   class="px-2 py-1 rounded-md text-[10px] font-black bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 inline-flex items-center gap-1 transition-all">
                                                    📄 Invoice
                                                </a>
                                                @if($due > 0 && $showActions)
                                                    <button type="button" wire:click="openEntryModal('{{ $selectedBreakdownUnit->id }}', {{ $entry->is_backlog ? 'true' : 'false' }})" 
                                                            class="px-2.5 py-1 rounded-md text-[10px] font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs cursor-pointer">
                                                        Collect {{ format_indian_currency($due) }}
                                                    </button>
                                                @else
                                                    <span class="text-[10px] font-bold text-emerald-600">✓ Settled</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="p-6 text-center text-slate-400 italic">No month-wise maintenance entries generated for Flat {{ $selectedBreakdownUnit->flat_number }} yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer (100% Fixed at bottom) -->
                <div style="flex-shrink: 0;" class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                    <button type="button" wire:click="closeMonthlyBreakdownModal" class="px-5 py-2 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-900 text-white transition-all cursor-pointer">
                        Close Breakdown
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
