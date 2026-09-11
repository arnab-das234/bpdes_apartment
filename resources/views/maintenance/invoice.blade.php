<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Invoice {{ $invoiceNumber }} - Flat {{ $unit->flat_number }}</title>
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
            .invoice-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 p-4 sm:p-8 flex flex-col items-center">

    <!-- Action Bar (Hidden during print) -->
    <div class="w-full max-w-3xl mb-6 flex items-center justify-between no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <button onclick="window.history.back()" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all flex items-center gap-1.5 cursor-pointer">
                ← Back
            </button>
            <span class="text-xs font-black text-slate-900 uppercase tracking-wide">
                📄 Maintenance Receipt & Invoice
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-700 text-white shadow-md transition-all flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Invoice / Save PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Invoice Card -->
    <div class="invoice-card w-full max-w-3xl bg-white rounded-2xl shadow-xl border border-slate-200 p-8 sm:p-10 relative overflow-hidden">
        
        <!-- Top Banner / Watermark Accent -->
        <div class="absolute top-0 left-0 right-0 h-3 {{ $entry->status === 'Paid' ? 'bg-emerald-600' : ($entry->status === 'Partial' ? 'bg-amber-500' : 'bg-rose-600') }}"></div>

        <!-- Header Block -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 border-b border-slate-200 pb-6 mt-2">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-blue-600 text-white font-black flex items-center justify-center text-base shadow-sm">
                        🏢
                    </span>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">
                        {{ $organization->name ?? 'Narmada Housing Cooperative Society' }}
                    </h1>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Registered Society Office | Tax & Maintenance Desk
                </p>
                <p class="text-[11px] text-slate-400 font-normal">
                    Email: management@{{ strtolower(str_replace(' ', '', $organization->name ?? 'narmada')) }}.org | Support Desk
                </p>
            </div>

            <div class="text-left sm:text-right">
                <span class="inline-block px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest {{ $entry->status === 'Paid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($entry->status === 'Partial' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-rose-100 text-rose-800 border border-rose-300') }}">
                    STATUS: {{ strtoupper($entry->status) }}
                </span>
                <h2 class="text-lg font-black text-slate-900 mt-2 font-mono">{{ $invoiceNumber }}</h2>
                <p class="text-xs text-slate-500 font-semibold">Issue Date: {{ date('d M Y', strtotime($entry->created_at)) }}</p>
            </div>
        </div>

        <!-- Resident & Billing Info Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-200">
            <!-- Billed To -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block mb-1">BILLED TO RESIDENT</span>
                <h3 class="text-base font-black text-slate-900">{{ $person->name ?? 'Resident Flat ' . $unit->flat_number }}</h3>
                <p class="text-xs font-bold text-slate-700 mt-0.5">Flat Number: <span class="font-black text-slate-900">Flat {{ $unit->flat_number }}</span></p>
                <p class="text-xs text-slate-500 font-medium">Building: {{ $unit->building?->name ?? 'Main Tower' }} (Floor {{ $unit->floor ?? '-' }})</p>
                @if($unit->meter_number)
                    <p class="text-[11px] text-slate-500 font-medium mt-1">Meter #: {{ $unit->meter_number }}</p>
                @endif
            </div>

            <!-- Billing Cycle Details -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider block mb-1">BILLING CYCLE & DETAILS</span>
                <p class="text-xs font-bold text-slate-700">Billing Period: <span class="font-black text-slate-900">{{ $entry->month_name ?: $entry->billing_year }}</span></p>
                <p class="text-xs font-bold text-slate-700 mt-1">Entry Title: <span class="font-black text-slate-900">{{ $entry->title }}</span></p>
                <p class="text-xs text-slate-500 font-medium mt-1">Due Date: <span class="font-bold text-rose-700">{{ $entry->due_date ? date('d M Y', strtotime($entry->due_date)) : 'Immediate' }}</span></p>
                @if($entry->is_backlog)
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                        ⚠️ HISTORICAL BACKLOG ENTRY
                    </span>
                @endif
            </div>
        </div>

        <!-- Line Item Breakdown Table -->
        <div class="my-6">
            <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider mb-3">Itemized Maintenance Dues Breakdown</h3>
            <div class="overflow-hidden border border-slate-200 rounded-xl">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-100 text-slate-600 font-extrabold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="p-3.5">Item Description</th>
                            <th class="p-3.5 text-center">Category</th>
                            <th class="p-3.5 text-right">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr>
                            <td class="p-3.5 font-bold text-slate-900">
                                Base Monthly Maintenance Rate
                                <span class="text-[10px] text-slate-400 block font-normal">Monthly fixed society operational charge</span>
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Base Dues</span>
                            </td>
                            <td class="p-3.5 text-right font-black text-slate-900">₹ {{ number_format($entry->base_maintenance, 2) }}</td>
                        </tr>

                        @if($entry->backlog_amount > 0)
                            <tr>
                                <td class="p-3.5 font-bold text-amber-950">
                                    Historical Backlog Arrears / Prior Dues
                                    <span class="text-[10px] text-amber-700 block font-normal">Accumulated unpaid balance from previous periods</span>
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-300">Backlog</span>
                                </td>
                                <td class="p-3.5 text-right font-black text-amber-900">₹ {{ number_format($entry->backlog_amount, 2) }}</td>
                            </tr>
                        @endif

                        @if($entry->late_fee > 0)
                            <tr>
                                <td class="p-3.5 font-bold text-rose-950">
                                    Late Payment Fee / Interest Penalty
                                    <span class="text-[10px] text-rose-700 block font-normal">Applicable penalty for delayed settlement</span>
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-rose-50 text-rose-800 border border-rose-300">Late Fee</span>
                                </td>
                                <td class="p-3.5 text-right font-black text-rose-900">₹ {{ number_format($entry->late_fee, 2) }}</td>
                            </tr>
                        @endif

                        @if($entry->utility_charge > 0)
                            <tr>
                                <td class="p-3.5 font-bold text-slate-900">
                                    Utility / Electricity Sub-meter Charge
                                    <span class="text-[10px] text-slate-500 block font-normal">Common & sub-meter electricity charge share</span>
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-800 border border-purple-200">Utility</span>
                                </td>
                                <td class="p-3.5 text-right font-black text-slate-900">₹ {{ number_format($entry->utility_charge, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Calculations & Net Due Total Summary -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 my-6">
            <div>
                @if($entry->amount_paid > 0)
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-emerald-800">
                            Payment Received: <span class="font-black">₹ {{ number_format($entry->amount_paid, 2) }}</span>
                        </p>
                        @if($entry->payment_mode)
                            <p class="text-[11px] text-slate-600 font-medium">Mode: {{ $entry->payment_mode }} {{ $entry->reference_number ? '| Ref: ' . $entry->reference_number : '' }}</p>
                        @endif
                        @if($entry->paid_at)
                            <p class="text-[11px] text-slate-500 font-medium">Paid On: {{ date('d M Y', strtotime($entry->paid_at)) }}</p>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-slate-500 font-medium italic">No payment recorded yet for this invoice.</p>
                @endif
            </div>

            <div class="w-full sm:w-auto text-left sm:text-right space-y-1">
                <div class="flex justify-between sm:justify-end gap-6 text-xs text-slate-600">
                    <span class="font-bold">Total Bill Amount:</span>
                    <strong class="font-black text-slate-900">₹ {{ number_format($entry->total_due, 2) }}</strong>
                </div>
                <div class="flex justify-between sm:justify-end gap-6 text-xs text-emerald-700">
                    <span class="font-bold">Total Paid:</span>
                    <strong class="font-black">₹ {{ number_format($entry->amount_paid, 2) }}</strong>
                </div>
                <div class="flex justify-between sm:justify-end gap-6 text-sm pt-2 border-t border-slate-300">
                    <span class="font-black text-slate-900 uppercase">Balance Due:</span>
                    <strong class="font-black text-lg {{ ($entry->total_due - $entry->amount_paid) > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                        ₹ {{ number_format(max($entry->total_due - $entry->amount_paid, 0), 2) }}
                    </strong>
                </div>
            </div>
        </div>

        <!-- Remarks & Payment Collection Instructions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-600 pt-2 border-t border-slate-200">
            <div>
                <span class="font-black text-slate-900 block mb-1">REMARKS / NOTES:</span>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    {{ $entry->remarks ?: 'Please retain this receipt for your monthly maintenance records.Dues are payable on or before the 10th of every month.' }}
                </p>
            </div>
            <div>
                <span class="font-black text-slate-900 block mb-1">SOCIETY PAYMENT DESK:</span>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    UPI ID: <span class="font-mono font-bold text-slate-800">society.narmada@upi</span><br>
                    Bank AC: <span class="font-mono font-bold text-slate-800">98760011223344 (HDFC Bank)</span><br>
                    IFSC: <span class="font-mono font-bold text-slate-800">HDFC0001234</span>
                </p>
            </div>
        </div>

        <!-- Signature Block -->
        <div class="mt-10 pt-6 border-t border-slate-200 flex justify-between items-end">
            <div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Computer Generated Receipt</p>
                <p class="text-[10px] text-slate-400 font-normal">Generated on {{ date('d M Y, h:i A') }}</p>
            </div>
            <div class="text-center">
                <div class="w-32 border-b border-slate-400 mb-1"></div>
                <p class="text-xs font-black text-slate-900 uppercase">Authorized Signatory</p>
                <p class="text-[10px] text-slate-500 font-medium">President / Treasurer, {{ $organization->name ?? 'Society' }}</p>
            </div>
        </div>
    </div>

</body>
</html>
