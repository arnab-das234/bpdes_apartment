<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Planning\Models\Proposal;
use Illuminate\Http\Request;

class AuditorApiController extends Controller
{
    /**
     * List proposals requiring technical verification signoff.
     */
    public function pendingVerifications(Request $request)
    {
        $user = $request->user();

        $proposals = Proposal::where('organization_id', $user->organization_id)
            ->where('status', Proposal::STATUS_VERIFICATION)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $proposals->count(),
            'data' => $proposals,
        ]);
    }

    /**
     * Technical sign-off by auditor.
     */
    public function verifyProposal(Request $request, string $id)
    {
        $proposal = Proposal::where('organization_id', $request->user()->organization_id)
            ->findOrFail($id);

        if ($proposal->status !== Proposal::STATUS_VERIFICATION) {
            return response()->json([
                'success' => false,
                'message' => "Proposal is currently in '{$proposal->status}' status and cannot be verified.",
            ], 422);
        }

        $documentPath = $proposal->document_path;
        if ($request->hasFile('verification_doc')) {
            $documentPath = $request->file('verification_doc')->store('verifications', 'public');
        }

        $proposal->update([
            'status' => Proposal::STATUS_VERIFIED,
            'document_path' => $documentPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Proposal '{$proposal->title}' verified successfully.",
            'data' => $proposal,
        ]);
    }
}
