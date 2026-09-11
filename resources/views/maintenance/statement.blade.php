<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Statement - Flat {{ $unit->flat_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .statement-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 p-4 sm:p-8 flex flex-col items-center">

    <!-- Top Action Bar (Hidden during print) -->
    <div class="w-full max-w-4xl mb-6 flex items-center justify-between no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <button onclick="window.history.back()" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all flex items-center gap-1.5 cursor-pointer">
                ← Back
            </button>
            <span class="text-xs font-black text-slate-900 uppercase tracking-wide">
                📜 Resident Maintenance Dues & Collection Ledger Statement
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-md transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Statement / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Statement Card -->
    <div class="statement-card w-full max-w-4xl bg-white rounded-2xl shadow-xl border border-slate-200 p-8 sm:p-10 relative overflow-hidden">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 border-b border-slate-200 pb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-slate-900 text-white font-black flex items-center justify-center text-base shadow-sm">
                        🏢
                    </span>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">
                        {{ $organization->name ?? 'Narmada Housing Cooperative Society' }}
                    </h1>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Resident Maintenance Dues & Collection Ledger Statement
                </p>
            </div>

            <div class="text-left sm:text-right">
                <span class="inline-block px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-900 border border-blue-300">
                    STATEMENT OF ACCOUNT
                </span>
                <p class="text-xs text-slate-500 font-semibold mt-2">Generated On: {{ date('d M Y, h:i A') }}</p>
                <p class="text-[11px] text-slate-400 font-normal">Statement Period: All Time Ledger Dues</p>
            </div>
        </div>

        <!-- Resident Profile Summary Box -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 py-6 border-b border-slate-200">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block">RESIDENT NAME</span>
                <strong class="text-base font-black text-slate-900 block mt-0.5">{{ $person->name ?? 'Unassigned Resident' }}</strong>
                <span class="text-xs text-slate-500 font-medium">Primary Flat Member</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block">FLAT IDENTIFICATION</span>
                <strong class="text-base font-black text-slate-900 block mt-0.5">Flat {{ $unit->flat_number }}</strong>
                <span class="text-xs text-slate-500 font-medium">{{ $unit->building?->name ?? 'Main Tower' }} (Floor {{ $unit->floor ?? '-' }})</span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block">MONTHLY BASE RATE</span>
                <strong class="text-base font-black text-slate-900 block mt-0.5">₹ {{ number_format($unit->monthly_maintenance_amount, 2) }}/mo</strong>
                <span class="text-xs text-slate-500 font-medium">Standard Maintenance Share</span>
            </div>
        </div>

        <!-- 4-Grid Overall Ledger Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 my-6">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <span class="uppercase tracking-wider text-slate-500 font-black text-[9px] block">Total Target Billed:</span>
                <strong class="text-slate-900 font-black text-lg block mt-0.5">₹ {{ number_format($totalBilled, 2) }}</strong>
                <span class="text-[10px] text-slate-500 font-medium block mt-0.5">All billing entries</span>
            </div>

            <div class="p-4 rounded-xl bg-amber-50/80 border border-amber-200">
                <span class="uppercase tracking-wider text-amber-900 font-black text-[9px] block">Backlog Arrears:</span>
                <strong class="text-amber-950 font-black text-lg block mt-0.5">₹ {{ number_format($totalBacklogs, 2) }}</strong>
                <span class="text-[10px] text-amber-800 font-medium block mt-0.5">Historical dues</span>
            </div>

            <div class="p-4 rounded-xl bg-emerald-50/80 border border-emerald-200">
                <span class="uppercase tracking-wider text-emerald-900 font-black text-[9px] block">Total Collected Paid:</span>
                <strong class="text-emerald-950 font-black text-lg block mt-0.5">₹ {{ number_format($totalPaid, 2) }}</strong>
                <span class="text-[10px] text-emerald-800 font-medium block mt-0.5">Cleared payments</span>
            </div>

            <div class="p-4 rounded-xl bg-rose-50/80 border border-rose-200">
                <span class="uppercase tracking-wider text-rose-900 font-black text-[9px] block">Net Outstanding Dues:</span>
                <strong class="text-rose-950 font-black text-lg block mt-0.5">₹ {{ number_format($totalOutstanding, 2) }}</strong>
                <span class="text-[10px] text-rose-800 font-medium block mt-0.5">Current unpaid balance</span>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="my-6">
            <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider mb-3">Itemized Month-by-Month Billing & Payment History</h3>
            <div class="overflow-hidden border border-slate-200 rounded-xl">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-100 text-slate-600 font-extrabold uppercase text-[9px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-3">Billing Month</th>
                            <th class="p-3">Entry Title & Type</th>
                            <th class="p-3 text-right">Base (₹)</th>
                            <th class="p-3 text-right">Backlog (₹)</th>
                            <th class="p-3 text-right">Total Due (₹)</th>
                            <th class="p-3 text-right">Paid (₹)</th>
                            <th class="p-3 text-right">Payment Details</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-right no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($ledgerRows as $row)
                            @php
                                $entry = $row['entry'];
                                $due = max((float)$entry->total_due - (float)$entry->amount_paid, 0);
                                $statusClass = $entry->status === 'Paid'
                                    ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
                                    : ($entry->status === 'Partial' ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-rose-100 text-rose-800 border-rose-300');
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 font-black text-slate-900">
                                    {{ $entry->month_name ?: $entry->billing_year }}
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ date('d M Y', strtotime($entry->created_at)) }}</span>
                                </td>
                                <td class="p-3 font-semibold text-slate-800">
                                    {{ $entry->title }}
                                    @if($entry->is_backlog)
                                        <span class="inline-block ml-1 px-1.5 py-0.5 rounded text-[8px] font-black uppercase bg-amber-100 text-amber-900 border border-amber-300">
                                            Backlog
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-right font-bold text-slate-700">₹ {{ number_format($entry->base_maintenance, 2) }}</td>
                                <td class="p-3 text-right font-bold text-amber-700">₹ {{ number_format($entry->backlog_amount, 2) }}</td>
                                <td class="p-3 text-right font-black text-slate-900">₹ {{ number_format($entry->total_due, 2) }}</td>
                                <td class="p-3 text-right font-black text-emerald-700">₹ {{ number_format($entry->amount_paid, 2) }}</td>
                                <td class="p-3 text-right text-[10px] text-slate-600">
                                    @if($entry->amount_paid > 0)
                                        <span class="font-bold text-slate-800 block">{{ $entry->payment_mode ?: 'Paid' }}</span>
                                        <span class="text-slate-400 block">{{ $entry->paid_at ? date('d M Y', strtotime($entry->paid_at)) : '' }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Unpaid</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border {{ $statusClass }}">
                                        {{ $entry->status }}
                                    </span>
                                </td>
                                <td class="p-3 text-right no-print">
                                    <a href="{{ route('maintenance.invoice', $entry->id) }}" target="_blank" 
                                       class="px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 text-[10px] font-black inline-flex items-center gap-1 transition-all">
                                        📄 Invoice
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-slate-400 italic">No maintenance ledger entries registered for Flat {{ $unit->flat_number }} yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer & Signature Block -->
        <div class="mt-10 pt-6 border-t border-slate-200 flex justify-between items-end">
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Official Society Ledger Statement</p>
                <p class="text-[10px] text-slate-400 font-normal">Generated on {{ date('d M Y, h:i A') }}</p>
            </div>
            <div class="text-center">
                <div class="w-36 border-b border-slate-400 mb-1"></div>
                <p class="text-xs font-black text-slate-900 uppercase">Treasurer / Secretary</p>
                <p class="text-[10px] text-slate-500 font-medium">{{ $organization->name ?? 'Society Office' }}</p>
            </div>
        </div>
    </div>

</body>
</html>
