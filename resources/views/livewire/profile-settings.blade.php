<div>
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Profile Settings</h2>
        <p class="text-sm text-slate-500">Manage account details and personal workspace information.</p>
    </div>

    @if(session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-sm flex items-center gap-2 shadow-sm">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 glass-panel p-6 rounded-xl shadow-sm">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-5">Profile Information</h3>
            <form wire:submit.prevent="saveProfile" class="space-y-5">
                <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center">
                    <div class="h-16 w-16 overflow-hidden rounded-lg border border-slate-200 bg-white flex items-center justify-center shrink-0">
                        @if($profilePhoto)
                            <img src="{{ $profilePhoto->temporaryUrl() }}" alt="Profile preview" class="h-full w-full object-cover">
                        @elseif(auth()->user()?->profile_photo_path || auth()->user()?->person?->photo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->profile_photo_path ?? auth()->user()->person->photo_path) }}" alt="{{ $name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-lg font-black text-blue-700">{{ strtoupper(substr($name ?: 'U', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Profile Image</label>
                        <input type="file" wire:model="profilePhoto" accept="image/*" class="w-full text-xs p-2.5 rounded bg-white border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        <p class="text-[10px] text-slate-400 mt-1">Upload JPG, PNG, or WEBP up to 2MB.</p>
                        @error('profilePhoto') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Full Name</label>
                        <input type="text" wire:model="name" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        @error('name') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" wire:model="email" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        @error('email') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Mobile</label>
                        <input type="text" wire:model="mobile" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        @error('mobile') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Occupation</label>
                        <input type="text" wire:model="occupation" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        @error('occupation') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">PAN</label>
                        <input type="text" wire:model="pan" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        @error('pan') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Family Members</label>
                        <input type="number" wire:model="familyMembers" min="1" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                        @error('familyMembers') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Address</label>
                    <textarea wire:model="address" rows="3" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500"></textarea>
                    @error('address') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="checkbox" wire:model="isProfessional" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Working professional
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="checkbox" wire:model="carParking" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Car parking allocated
                    </label>
                </div>

                <button type="submit" class="px-5 py-2.5 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition-all">
                    Save Profile
                </button>
            </form>
        </div>

        <div class="glass-panel p-6 rounded-xl shadow-sm">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-5">Security</h3>
            <form wire:submit.prevent="updatePassword" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Current Password</label>
                    <input type="password" wire:model="currentPassword" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                    @error('currentPassword') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">New Password</label>
                    <input type="password" wire:model="newPassword" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                    @error('newPassword') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" wire:model="newPasswordConfirmation" class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit" class="w-full py-2.5 rounded bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-all">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>
