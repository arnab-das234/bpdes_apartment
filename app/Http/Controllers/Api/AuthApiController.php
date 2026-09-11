<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\TenantIdentity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * Authenticate user and issue Sanctum bearer token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $tokenName = $request->device_name ?: 'Mobile App API Token';
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'organization_id' => $user->organization_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'effective_role' => $user->effectiveRoleKey(),
            ],
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load(['organization', 'unit.building', 'person']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Revoke active token (Logout).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully.',
        ]);
    }
}
