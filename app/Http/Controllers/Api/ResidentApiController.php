<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\MaintenanceEntry;
use App\Modules\Finance\Models\ElectricityBill;
use App\Modules\Communication\Models\Complaint;
use App\Modules\Premises\Models\Unit;
use Illuminate\Http\Request;

class ResidentApiController extends Controller
{
    /**
     * Get maintenance bills for authenticated resident unit.
     */
    public function maintenanceInvoices(Request $request)
    {
        $user = $request->user();
        $unitId = $user->unit_id;

        if (!$unitId && $user->person_id) {
            $unitId = Unit::whereHas('memberships', fn($q) => $q->where('person_id', $user->person_id))->value('id');
        }

        if (!$unitId) {
            return response()->json([
                'success' => false,
                'message' => 'No unit allocated to current user.',
            ], 404);
        }

        $entries = MaintenanceEntry::where('unit_id', $unitId)
            ->orderBy('billing_year', 'desc')
            ->orderBy('billing_month', 'desc')
            ->get();

        $totalOutstanding = $entries->sum(fn($e) => max($e->total_due - $e->amount_paid, 0));

        return response()->json([
            'success' => true,
            'unit_id' => $unitId,
            'total_outstanding' => $totalOutstanding,
            'data' => $entries,
        ]);
    }

    /**
     * Get UPI Payment Payload for quick mobile payment.
     */
    public function upiPaymentPayload(Request $request, string $entryId)
    {
        $entry = MaintenanceEntry::with('unit')->findOrFail($entryId);

        $due = max((float)$entry->total_due - (float)$entry->amount_paid, 0);
        $pa = "society.treasurer@upi"; // Default UPI VPA
        $pn = "Royal Palm Co-Op Society";
        $tn = "Maintenance Flat " . ($entry->unit?->flat_number ?? 'N/A') . " (" . $entry->month_name . ")";

        $upiUri = "upi://pay?pa={$pa}&pn=" . urlencode($pn) . "&am={$due}&cu=INR&tn=" . urlencode($tn);

        return response()->json([
            'success' => true,
            'entry_id' => $entry->id,
            'amount_due' => $due,
            'upi_uri' => $upiUri,
        ]);
    }

    /**
     * Submit a maintenance complaint / grievance ticket.
     */
    public function submitComplaint(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'description' => 'required|string|min:10',
            'priority' => 'required|in:Low,Medium,High,Critical',
        ]);

        $user = $request->user();

        $complaint = Complaint::create([
            'organization_id' => $user->organization_id,
            'unit_id' => $user->unit_id,
            'person_id' => $user->person_id,
            'category' => $request->category,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'reported',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint logged successfully.',
            'data' => $complaint,
        ], 201);
    }
}
