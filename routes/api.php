<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ResidentApiController;
use App\Http\Controllers\Api\SecurityGuardApiController;
use App\Http\Controllers\Api\AuditorApiController;

// Public Auth Endpoints
Route::post('/v1/auth/login', [AuthApiController::class, 'login']);

// Sanctum Protected API Surface
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Auth & Profile
    Route::get('/auth/profile', [AuthApiController::class, 'profile']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    // Resident Mobile App Surface
    Route::get('/resident/invoices', [ResidentApiController::class, 'maintenanceInvoices']);
    Route::get('/resident/invoices/{entryId}/upi-payload', [ResidentApiController::class, 'upiPaymentPayload']);
    Route::post('/resident/complaints', [ResidentApiController::class, 'submitComplaint']);

    // Security Guard App Surface
    Route::post('/security/check-in', [SecurityGuardApiController::class, 'visitorCheckIn']);
    Route::post('/security/scan-rfid', [SecurityGuardApiController::class, 'scanRfid']);
    Route::put('/security/check-out/{logId}', [SecurityGuardApiController::class, 'visitorCheckOut']);

    // Auditor Mobile App Surface
    Route::get('/auditor/proposals/pending', [AuditorApiController::class, 'pendingVerifications']);
    Route::post('/auditor/proposals/{id}/verify', [AuditorApiController::class, 'verifyProposal']);
});
