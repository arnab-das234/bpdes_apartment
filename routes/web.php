<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\ControlCenter;
use App\Livewire\PresidentInbox;
use App\Livewire\OrganizationCrud;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\SuperadminDashboard;
use App\Livewire\ResidentDashboard;
use App\Livewire\LandingPage;
use App\Livewire\ProfileSettings;
use App\Http\Controllers\MaintenanceInvoiceController;

Route::get('/', LandingPage::class)->name('landing');
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');

Route::get('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/superadmin/dashboard', SuperadminDashboard::class)->name('superadmin.dashboard');
    Route::get('/control-center', ControlCenter::class)->name('control-center');
    Route::get('/resident/dashboard', ResidentDashboard::class)->name('resident.dashboard');
    Route::get('/president-inbox', PresidentInbox::class)->name('president-inbox');
    Route::get('/admin/organizations', OrganizationCrud::class)->name('organizations');
    Route::get('/profile/settings', ProfileSettings::class)->name('profile.settings');

    // Maintenance Invoice & Collection Statement printable routes
    Route::get('/maintenance/invoice/{entryId}', [MaintenanceInvoiceController::class, 'invoice'])->name('maintenance.invoice');
    Route::get('/maintenance/statement/{unitId}', [MaintenanceInvoiceController::class, 'statement'])->name('maintenance.statement');
});
