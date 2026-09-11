<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceEntry;
use App\Models\Organization;
use App\Models\Unit;
use Illuminate\Http\Request;

class MaintenanceInvoiceController extends Controller
{
    /**
     * Display printable invoice / receipt for a maintenance entry.
     */
    public function invoice(string $entryId)
    {
        $entry = MaintenanceEntry::with([
            'unit.building',
            'unit.memberships.person',
            'person',
            'recorder',
        ])->findOrFail($entryId);

        $unit = $entry->unit;
        $person = $entry->person ?: $unit?->memberships->first()?->person;
        $organization = Organization::find($entry->organization_id) ?: (auth()->check() ? auth()->user()->organization : null);

        $invoiceNumber = 'INV-' . date('Y', strtotime($entry->created_at)) . '-' . strtoupper(substr(str_replace('-', '', $entry->id), 0, 6));

        return view('maintenance.invoice', compact('entry', 'unit', 'person', 'organization', 'invoiceNumber'));
    }

    /**
     * Display printable maintenance collection statement for a resident flat.
     */
    public function statement(string $unitId)
    {
        $unit = Unit::with([
            'building',
            'memberships.person',
            'maintenanceEntries' => function ($query) {
                $query->orderBy('billing_year', 'asc')->orderBy('billing_month', 'asc')->orderBy('created_at', 'asc');
            },
        ])->findOrFail($unitId);

        $person = $unit->memberships->first()?->person;
        $organization = Organization::find($unit->organization_id) ?: (auth()->check() ? auth()->user()->organization : null);
        $entries = $unit->maintenanceEntries;

        // Compute running balances
        $runningBalance = 0;
        $ledgerRows = $entries->map(function ($entry) use (&$runningBalance) {
            $due = max((float)$entry->total_due - (float)$entry->amount_paid, 0);
            $runningBalance += $due;
            return [
                'entry' => $entry,
                'running_balance' => $runningBalance,
            ];
        });

        $totalBilled = $entries->sum('total_due');
        $totalPaid = $entries->sum('amount_paid');
        $totalOutstanding = max($totalBilled - $totalPaid, 0);
        $totalBacklogs = $entries->where('is_backlog', true)->sum('backlog_amount');

        return view('maintenance.statement', compact(
            'unit',
            'person',
            'organization',
            'entries',
            'ledgerRows',
            'totalBilled',
            'totalPaid',
            'totalOutstanding',
            'totalBacklogs'
        ));
    }
}
