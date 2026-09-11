<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    public function mount(): void
    {
        // Redirect if already logged in
        if (Auth::check()) {
            $this->redirectUser();
        }

        // Auto-seed the superadmin for local convenience
        if (!User::where('email', 'superadmin@bpdes.app')->exists()) {
            User::create([
                'name' => 'System Superadmin',
                'email' => 'superadmin@bpdes.app',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'organization_id' => null,
            ]);
        }

        // Auto-seed dedicated Cashier account for Cash Release Desk
        if (!User::where('email', 'cashier@society.com')->exists()) {
            $org = \App\Models\Organization::first();
            User::create([
                'name' => 'Society Cashier & Treasurer',
                'email' => 'cashier@society.com',
                'password' => bcrypt('password'),
                'role' => 'cashier',
                'organization_id' => $org?->id,
            ]);
        }
    }

    public function fillCashierCredentials(): void
    {
        $this->email = 'cashier@society.com';
        $this->password = 'password';
        $this->login();
    }

    public function login(): mixed
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            
            // Set session organization ID for multi-tenant context
            $user = Auth::user();
            if ($user->organization_id) {
                session(['selected_org_id' => $user->organization_id]);
            } else {
                session()->forget('selected_org_id');
            }

            return $this->redirectUser();
        }

        $this->addError('email', 'The provided credentials do not match our records.');
        return null;
    }

    protected function redirectUser(): mixed
    {
        $user = Auth::user();
        if ($user->role === 'superadmin' || is_null($user->organization_id)) {
            return redirect()->to('/superadmin/dashboard');
        }

        return redirect()->to($user->homeRoute());
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.app', ['title' => 'BPDES Login']);
    }
}
