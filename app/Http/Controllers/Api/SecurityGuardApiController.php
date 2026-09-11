<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Premises\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecurityGuardApiController extends Controller
{
    /**
     * Log visitor check-in.
     */
    public function visitorCheckIn(Request $request)
    {
        $request->validate([
            'visitor_name' => 'required|string|min:3',
            'visitor_mobile' => 'nullable|string',
            'entry_type' => 'required|in:visitor,delivery,resident,staff',
            'flat_number' => 'nullable|string',
            'vehicle_number' => 'nullable|string',
        ]);

        $user = $request->user();
        $unitId = null;

        if ($request->flat_number) {
            $unitId = Unit::where('organization_id', $user->organization_id)
                ->where('flat_number', $request->flat_number)
                ->value('id');
        }

        $logId = DB::table('security_logs')->insertGetId([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $user->organization_id,
            'unit_id' => $unitId,
            'visitor_name' => $request->visitor_name,
            'visitor_mobile' => $request->visitor_mobile,
            'entry_type' => $request->entry_type,
            'vehicle_number' => $request->vehicle_number ? strtoupper($request->vehicle_number) : null,
            'entry_time' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visitor entry logged successfully.',
            'log_id' => $logId,
        ], 201);
    }

    /**
     * Fast RFID scan check for resident / guard access.
     */
    public function scanRfid(Request $request)
    {
        $request->validate([
            'rfid_tag' => 'required|string',
        ]);

        $user = $request->user();

        $resident = DB::table('security_residents')
            ->where('organization_id', $user->organization_id)
            ->where('rfid_tag', $request->rfid_tag)
            ->first();

        if (!$resident) {
            return response()->json([
                'success' => false,
                'access' => 'DENIED',
                'message' => 'Unrecognized RFID Card/Tag.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'access' => $resident->access_status === 'active' ? 'GRANTED' : 'DENIED',
            'resident' => $resident,
        ]);
    }

    /**
     * Mark visitor exit.
     */
    public function visitorCheckOut(string $logId)
    {
        $updated = DB::table('security_logs')
            ->where('id', $logId)
            ->update([
                'exit_time' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => (bool)$updated,
            'message' => $updated ? 'Visitor exit logged.' : 'Security log record not found.',
        ]);
    }
}
