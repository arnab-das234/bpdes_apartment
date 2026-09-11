<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\Component;

class ProfileSettings extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public string $mobile = '';
    public string $address = '';
    public string $occupation = '';
    public string $pan = '';
    public string $familyMembers = '';
    public bool $isProfessional = false;
    public bool $carParking = false;
    public $profilePhoto = null;

    public function mount(): void
    {
        $user = Auth::user();
        $person = $user?->person;

        $this->name = $user?->name ?? '';
        $this->email = $user?->email ?? '';
        $this->mobile = $person?->mobile ?? '';
        $this->address = $person?->address ?? '';
        $this->occupation = $person?->occupation ?? '';
        $this->pan = $person?->pan ?? '';
        $this->familyMembers = (string) ($person?->family_members ?? '');
        $this->isProfessional = (bool) ($person?->is_professional ?? false);
        $this->carParking = (bool) ($person?->car_parking ?? false);
    }

    public function saveProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->where('organization_id', $user?->organization_id)
                    ->ignore($user?->id),
            ],
            'mobile' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:80'],
            'pan' => ['nullable', 'string', 'max:20'],
            'familyMembers' => ['nullable', 'integer', 'min:1', 'max:25'],
            'profilePhoto' => ['nullable', 'image', 'max:2048'],
        ]);

        $photoPath = $user->profile_photo_path;

        if ($this->profilePhoto) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            $photoPath = $this->profilePhoto->store('profile-photos', 'public');
        }

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'profile_photo_path' => $photoPath,
        ]);

        if ($user->person) {
            $user->person->update([
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->mobile,
                'address' => $this->address,
                'occupation' => $this->occupation,
                'pan' => $this->pan,
                'family_members' => $this->familyMembers ?: null,
                'is_professional' => $this->isProfessional,
                'car_parking' => $this->carParking,
                'photo_path' => $photoPath,
            ]);
        }

        $this->profilePhoto = null;

        session()->flash('message', 'Profile settings updated successfully.');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        $this->validate([
            'currentPassword' => ['required'],
            'newPassword' => ['required', 'string', 'min:6', 'same:newPasswordConfirmation'],
        ]);

        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'The current password is incorrect.');
            return;
        }

        $user->update([
            'password' => $this->newPassword,
        ]);

        $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation');
        session()->flash('message', 'Password updated successfully.');
    }

    public function render(): View
    {
        return view('livewire.profile-settings')->layout('components.layouts.app');
    }
}
