@props([
    'data' => [],
])

@php
    $assets = $data['assets'] ?? [];
    $liabilities = $data['liabilities_and_equity'] ?? [];
    $metrics = $data['metrics'] ?? [];
    $isBalanced = $data['is_balanced'] ?? true;
@endphp

<div class="space-y-6">
    <!-- Action Bar (Hidden on Print) -->
    <div class="no-print bg-white rounded-2xl p-4 border-2 border-slate-300 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-slate-900 text-white flex items-center justify-center font-extrabold text-xl shadow-sm shrink-0">
                📊
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-black text-slate-950 tracking-tight">Financial Balance Sheet Statement</h3>
                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-900 font-mono text-[10px] uppercase font-black border border-blue-300">
                        Landscape A4 Ready
                    </span>
                </div>
                <p class="text-xs font-bold text-slate-700">Audited tabular presentation of Society Assets, Liabilities, Reserves & Retained Capital.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 sm:ml-auto shrink-0">
            @if($isBalanced)
                <span class="px-3.5 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-950 border border-emerald-400 flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                    Balanced Ledger (100%)
                </span>
            @endif

            <button onclick="window.print()" 
                    class="px-5 py-2.5 rounded-xl bg-slate-950 hover:bg-slate-900 text-black font-extrabold text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer border border-slate-800 active:scale-95 whitespace-nowrap">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Balance Sheet (Landscape)</span>
            </button>
        </div>
    </div>

    <!-- Balance Sheet Printable Document Box (A4 Landscape Formatted) -->
    <div class="bg-white rounded-2xl border-2 border-slate-300 shadow-xl p-6 sm:p-8 space-y-6 print-container" id="printable-balance-sheet">
        <!-- Letterhead Header -->
        <div class="border-b-2 border-slate-900 pb-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest block font-mono">Regn No: MUM/HSG/2018/7892 • Form N (Rule 62)</span>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight uppercase font-serif">NARMADA CO-OPERATIVE HOUSING SOCIETY LTD.</h1>
                    <p class="text-xs text-slate-700 font-semibold">Registered under Maharashtra Co-operative Societies Act, 1960</p>
                    <p class="text-[11px] text-slate-500">Plot 42-45, Sector 15, Palm Beach Road, Sanpada, Navi Mumbai - 400705</p>
                </div>
                <div class="text-right shrink-0">
                    <div class="inline-block px-3 py-1 rounded bg-slate-900 text-white text-[11px] font-black uppercase tracking-wider font-mono">
                        STATEMENT OF FINANCIAL POSITION
                    </div>
                    <p class="text-xs text-slate-900 font-bold font-mono mt-1">As of {{ $data['as_of_date'] ?? now()->format('F d, Y') }}</p>
                    <p class="text-[10px] text-slate-500 font-mono">Financial Year 2026-2027</p>
                </div>
            </div>
        </div>

        <!-- Master Tabular Accounting Ledger Grid (Side-by-Side T-Format Table) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 border-2 border-slate-900 rounded-lg overflow-hidden bg-slate-900 shadow-sm">
            
            <!-- LEFT TABLE: CAPITAL, RESERVES & LIABILITIES (Light Red Theme) -->
            <div class="bg-white border-r border-slate-900 flex flex-col justify-between">
                <div>
                    <!-- Header (Light Red Header) -->
                    <div class="bg-rose-100 text-rose-950 px-4 py-2.5 border-b-2 border-rose-300 flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider font-mono">CAPITAL, RESERVES & LIABILITIES</span>
                        <span class="text-[10px] font-mono text-rose-700 font-extrabold">AMOUNT (₹)</span>
                    </div>

                    <!-- Particulars Table -->
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-300 font-black text-slate-700 text-[10px] uppercase tracking-wider">
                                <th class="py-2 px-3">Particulars of Liabilities & Funds</th>
                                <th class="py-2 px-2 text-center w-12">Sch</th>
                                <th class="py-2 px-3 text-right w-28">Details (₹)</th>
                                <th class="py-2 px-3 text-right w-32">Final Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-rose-100">
                            <!-- 1. CURRENT LIABILITIES -->
                            <tr class="bg-slate-50/80 font-bold text-slate-900 text-[11px]">
                                <td colspan="4" class="py-1.5 px-3 uppercase tracking-wide border-t border-slate-300 text-slate-800">
                                    1. Current Liabilities & Provisions
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Unpaid Vendor & Expense Bills Payable</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">L-1</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($liabilities['current_liabilities']['unpaid_bills'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-400">-</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Advance Member Maintenance Collections</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">L-2</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($liabilities['current_liabilities']['advance_collections'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-400">-</td>
                            </tr>
                            <tr class="font-bold text-slate-900 bg-slate-100/50">
                                <td colspan="3" class="py-1.5 px-3 text-right text-[11px] text-slate-600 font-semibold">Subtotal Current Liabilities:</td>
                                <td class="py-1.5 px-3 text-right font-mono font-bold text-slate-900">{{ format_indian_currency($liabilities['current_liabilities']['total_current_liabilities'] ?? 0) }}</td>
                            </tr>

                            <!-- 2. RESERVES & FUNDS -->
                            <tr class="bg-slate-50/80 font-bold text-slate-900 text-[11px]">
                                <td colspan="4" class="py-1.5 px-3 uppercase tracking-wide border-t border-slate-300 text-slate-800">
                                    2. Statutory Reserves & Special Funds
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Building Sinking Fund Reserve (18%)</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">R-1</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($liabilities['reserves']['sinking_fund'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-400">-</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">General Contingency Reserve Fund (12%)</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">R-2</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($liabilities['reserves']['general_reserve'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-400">-</td>
                            </tr>
                            <tr class="font-bold text-slate-900 bg-slate-100/50">
                                <td colspan="3" class="py-1.5 px-3 text-right text-[11px] text-slate-600 font-semibold">Subtotal Reserves & Funds:</td>
                                <td class="py-1.5 px-3 text-right font-mono font-bold text-slate-900">{{ format_indian_currency($liabilities['reserves']['total_reserves'] ?? 0) }}</td>
                            </tr>

                            <!-- 3. CAPITAL & ACCUMULATED SURPLUS -->
                            <tr class="bg-slate-50/80 font-bold text-slate-900 text-[11px]">
                                <td colspan="4" class="py-1.5 px-3 uppercase tracking-wide border-t border-slate-300 text-slate-800">
                                    3. Member Capital & Retained Equity
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Accumulated Retained Equity Surplus</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">C-1</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($liabilities['capital_equity']['accumulated_surplus'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono font-bold text-emerald-800">{{ format_indian_currency($liabilities['capital_equity']['accumulated_surplus'] ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Left Grand Total Row (Matching Highlight) -->
                <div class="bg-slate-950 text-black border-t-2 border-slate-950 p-3 flex items-center justify-between font-black text-sm font-mono">
                    <span class="uppercase tracking-wider text-slate-200">TOTAL LIABILITIES & CAPITAL</span>
                    <span class="text-base text-amber-300 font-extrabold underline underline-offset-4 decoration-double">
                        {{ format_indian_currency($liabilities['total_liabilities_and_equity'] ?? 0) }}
                    </span>
                </div>
            </div>

            <!-- RIGHT TABLE: ASSETS & PROPERTIES (Light Green Theme) -->
            <div class="bg-white flex flex-col justify-between">
                <div>
                    <!-- Header (Light Green Header) -->
                    <div class="bg-emerald-100 text-emerald-950 px-4 py-2.5 border-b-2 border-emerald-300 flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider font-mono">PROPERTY, ASSETS & RECEIVABLES</span>
                        <span class="text-[10px] font-mono text-emerald-700 font-extrabold">AMOUNT (₹)</span>
                    </div>

                    <!-- Particulars Table -->
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-300 font-black text-slate-700 text-[10px] uppercase tracking-wider">
                                <th class="py-2 px-3">Particulars of Properties & Assets</th>
                                <th class="py-2 px-2 text-center w-12">Sch</th>
                                <th class="py-2 px-3 text-right w-28">Details (₹)</th>
                                <th class="py-2 px-3 text-right w-32">Final Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-emerald-100">
                            <!-- 1. CURRENT ASSETS -->
                            <tr class="bg-slate-50/80 font-bold text-slate-900 text-[11px]">
                                <td colspan="4" class="py-1.5 px-3 uppercase tracking-wide border-t border-slate-300 text-slate-800">
                                    1. Current Assets & Liquid Balances
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Cash in Hand (Building Main Cash Fund)</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">A-1</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($assets['current']['cash_in_hand'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-400">-</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Bank Account Balances (Savings & Current)</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">A-2</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($assets['current']['bank_balances'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-400">-</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Inventory Stock Valuation (Store Items)</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">A-3</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($assets['current']['inventory_valuation'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-400">-</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Maintenance Dues Receivable (Members)</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">A-4</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($assets['current']['maintenance_receivables'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-400">-</td>
                            </tr>
                            <tr class="font-bold text-slate-900 bg-slate-100/50">
                                <td colspan="3" class="py-1.5 px-3 text-right text-[11px] text-slate-600 font-semibold">Subtotal Current Assets:</td>
                                <td class="py-1.5 px-3 text-right font-mono font-bold text-slate-900">{{ format_indian_currency($assets['current']['total_current_assets'] ?? 0) }}</td>
                            </tr>

                            <!-- 2. FIXED ASSETS -->
                            <tr class="bg-slate-50/80 font-bold text-slate-900 text-[11px]">
                                <td colspan="4" class="py-1.5 px-3 uppercase tracking-wide border-t border-slate-300 text-slate-800">
                                    2. Fixed & Non-Current Properties
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-3 text-slate-700 pl-6">Building Equipment, Lifts & Generators</td>
                                <td class="py-1.5 px-2 text-center font-mono text-[10px] text-slate-400">F-1</td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-800">{{ format_indian_currency($assets['fixed']['machinery_equipment'] ?? 0) }}</td>
                                <td class="py-1.5 px-3 text-right font-mono font-bold text-slate-900">{{ format_indian_currency($assets['fixed']['machinery_equipment'] ?? 0) }}</td>
                            </tr>
                            <tr class="font-bold text-slate-900 bg-slate-100/50">
                                <td colspan="3" class="py-1.5 px-3 text-right text-[11px] text-slate-600 font-semibold">Subtotal Fixed Assets:</td>
                                <td class="py-1.5 px-3 text-right font-mono font-bold text-slate-900">{{ format_indian_currency($assets['fixed']['total_fixed_assets'] ?? 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right Grand Total Row (Matching Highlight) -->
                <div class="bg-slate-950 text-black border-t-2 border-slate-950 p-3 flex items-center justify-between font-black text-sm font-mono">
                    <span class="uppercase tracking-wider text-slate-200">TOTAL ASSETS & PROPERTIES</span>
                    <span class="text-base text-amber-300 font-extrabold underline underline-offset-4 decoration-double">
                        {{ format_indian_currency($assets['total_assets'] ?? 0) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Key Financial Ratio Metrics Bar (Single Horizontal Line Layout) -->
        <div class="grid grid-cols-4 gap-3 p-3 rounded-xl bg-slate-100 border border-slate-300 font-mono text-xs whitespace-nowrap">
            <div class="flex items-center justify-between gap-2 border-r border-slate-300 pr-3">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider font-sans">Net Working Capital:</span>
                <strong class="text-slate-950 text-xs font-black">{{ format_indian_currency($metrics['working_capital'] ?? 0) }}</strong>
            </div>
            <div class="flex items-center justify-between gap-2 border-r border-slate-300 pr-3">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider font-sans">Total Stock Inventory:</span>
                <strong class="text-slate-950 text-xs font-black">{{ $metrics['inventory_item_count'] ?? 0 }} Registered Items</strong>
            </div>
            <div class="flex items-center justify-between gap-2 border-r border-slate-300 pr-3">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider font-sans">Reserved Stock Budget:</span>
                <strong class="text-amber-900 text-xs font-black">{{ format_indian_currency($metrics['reserved_stock_budget'] ?? 0) }}</strong>
            </div>
            <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider font-sans">Low Stock Reorders:</span>
                <strong class="text-rose-700 text-xs font-black">{{ $metrics['low_stock_item_count'] ?? 0 }} Reorder Alert(s)</strong>
            </div>
        </div>

        <!-- Audit & Verification Sign-off Section -->
        <div class="pt-6 border-t border-slate-300 space-y-3">
            <div class="flex items-center justify-between text-[11px] font-mono px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-300 text-amber-950 font-bold shadow-xs">
                <span class="flex items-center gap-2">
                    <span class="text-amber-600 font-sans">📜</span>
                    Certified true copy extract from Society Audited Account Register
                </span>
                <span class="text-amber-900 bg-amber-200/70 px-2 py-0.5 rounded text-[10px]">Audit Ref No: AUD/2026/FIN-009</span>
            </div>

            <div class="grid grid-cols-3 gap-8 text-center pt-6">
                <div class="space-y-8">
                    <div class="border-b border-slate-400 pb-1 text-slate-300 text-xs font-mono">[ Signature & Stamp ]</div>
                    <div>
                        <strong class="block text-xs font-black text-slate-900 uppercase">Prepared By</strong>
                        <span class="text-[10px] text-slate-600 block">Society Cashier / Treasurer</span>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="border-b border-slate-400 pb-1 text-slate-300 text-xs font-mono">[ Signature & Stamp ]</div>
                    <div>
                        <strong class="block text-xs font-black text-slate-900 uppercase">Verified & Audited By</strong>
                        <span class="text-[10px] text-slate-600 block">Managing Committee Secretary</span>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="border-b border-slate-400 pb-1 text-slate-300 text-xs font-mono">[ Signature & Stamp ]</div>
                    <div>
                        <strong class="block text-xs font-black text-slate-900 uppercase">Approved By</strong>
                        <span class="text-[10px] text-slate-600 block">Society President</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide layout chrome, headers, sidebars */
    body * {
        visibility: hidden;
    }
    .no-print, header, nav, aside {
        display: none !important;
    }
    
    /* Reveal and stretch balance sheet printable block */
    #printable-balance-sheet, #printable-balance-sheet * {
        visibility: visible;
    }
    
    #printable-balance-sheet {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 6mm 10mm !important;
        border: none !important;
        box-shadow: none !important;
        background: #ffffff !important;
    }

    /* Force Landscape layout */
    @page {
        size: A4 landscape;
        margin: 6mm 10mm;
    }

    /* Force grid to 2-columns in print mode */
    .grid-cols-1 {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    .grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    }

    /* Crisp borders for print */
    table, th, td {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
