<div class="min-h-[85vh] flex items-center justify-center py-8 sm:px-6 lg:px-8">
    <div class="w-full max-w-xl glass-panel p-8 rounded-xl shadow-xl flex flex-col gap-6">
        
        <!-- Header Branding -->
        <div class="flex items-center gap-3 border-b border-slate-200/60 pb-4">
            <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-xl text-white shadow-lg shadow-blue-500/20">
                B
            </div>
            <div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Resident Self-Onboarding</h2>
                <p class="text-xs text-slate-500">Register your credentials and claim your flat unit.</p>
            </div>
        </div>

        <!-- Registration Form -->
        <form wire:submit.prevent="registerResident" class="space-y-4">
            
            <!-- Target Complex / Society Selection -->
            <div>
                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Target Complex / Society</label>
                <select wire:model.live="selectedOrgId" 
                        class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500">
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
                @error('selectedOrgId') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
            </div>

            <!-- Name and Mobile -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Full Name</label>
                    <input type="text" wire:model="name" placeholder="e.g. Shyamal Sen" 
                           class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                    @error('name') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Mobile Number</label>
                    <input type="text" wire:model="mobile" placeholder="e.g. 9830098301" 
                           class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                    @error('mobile') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Email Address (Login Username) -->
            <div>
                <label class="block text-[10px] font-semibold text-slate-700 mb-1">Email Address (Login Username)</label>
                <input type="email" wire:model="email" placeholder="e.g. resident@society.in" 
                       class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                @error('email') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Password</label>
                    <input type="password" wire:model="password" placeholder="••••••••" 
                           class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                    @error('password') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" wire:model="password_confirmation" placeholder="••••••••" 
                           class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                </div>
            </div>

            <!-- FLAT UNIT IDENTIFICATION -->
            <div class="border-t border-slate-200/60 pt-4 space-y-4">
                <h3 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Flat / Apartment Coordinates</h3>

                <div class="grid grid-cols-3 gap-4">
                    <!-- Flat Number Text Field -->
                    <div class="col-span-1">
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Flat Number</label>
                        <input type="text" wire:model="flatNumber" placeholder="e.g. 302" 
                               class="w-full text-xs p-2.5 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500" required>
                        @error('flatNumber') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Room Count Selector -->
                    <div class="col-span-2">
                        <label class="block text-[10px] font-semibold text-slate-700 mb-1">Room Count (BHK)</label>
                        <div class="flex gap-1.5">
                            @foreach(['1BHK', '2BHK', '3BHK', 'PENTHOUSE'] as $bhk)
                                <button type="button" wire:click="selectBhk('{{ $bhk }}')" 
                                        wire:key="bhk-{{ $bhk }}"
                                        class="flex-1 py-2 text-[9px] font-extrabold rounded-lg border text-center transition-all leading-tight {{ $selectedBhk === $bhk ? 'bg-blue-600 text-white border-blue-500' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300' }}">
                                    {{ $bhk === 'PENTHOUSE' ? 'Penthouse' : $bhk }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Floor Level Selector -->
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1">Floor Level</label>
                    <div class="flex gap-1.5">
                        @foreach([0, 1, 2, 3, 4] as $floor)
                            <button type="button" wire:click="selectFloor({{ $floor }})" 
                                    wire:key="floor-{{ $floor }}"
                                    class="flex-1 py-2 text-[9px] font-extrabold rounded-lg border text-center transition-all {{ $selectedFloor === $floor ? 'bg-blue-600 text-white border-blue-500' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300' }}">
                                {{ $floor === 0 ? 'Ground' : $floor . ' Floor' }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- OCCUPANCY PREFERENCES -->
            <div class="border-t border-slate-200/60 pt-4 space-y-4">
                <h3 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Occupant Details</h3>

                <!-- Family Size Selector -->
                <div>
                    <label class="block text-[10px] font-semibold text-slate-700 mb-1.5">Number of Family Members</label>
                    <div class="flex gap-1.5">
                        @foreach([1, 2, 3, 4, 5] as $num)
                            <button type="button" wire:click="$set('familyMembers', {{ $num }})" 
                                    wire:key="fam-{{ $num }}"
                                    class="h-8 w-8 text-xs font-bold rounded-lg border transition-all {{ $familyMembers === $num ? 'bg-blue-600 text-white border-blue-500' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                                {{ $num }}{{ $num === 5 ? '+' : '' }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Professional & Parking Toggles -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Professional -->
                    <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 bg-slate-50/50">
                        <span class="text-[10px] font-bold text-slate-700">Working Professional?</span>
                        <button type="button" wire:click="$toggle('isProfessional')" 
                                class="h-6 w-11 rounded-full p-0.5 transition-colors focus:outline-none {{ $isProfessional ? 'bg-blue-600' : 'bg-slate-300' }}">
                            <div class="h-5 w-5 rounded-full bg-white transition-transform transform {{ $isProfessional ? 'translate-x-5' : 'translate-x-0' }}"></div>
                        </button>
                    </div>

                    <!-- Parking Slot -->
                    <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 bg-slate-50/50">
                        <span class="text-[10px] font-bold text-slate-700">Car Parking Space?</span>
                        <button type="button" wire:click="$toggle('carParking')" 
                                class="h-6 w-11 rounded-full p-0.5 transition-colors focus:outline-none {{ $carParking ? 'bg-blue-600' : 'bg-slate-300' }}">
                            <div class="h-5 w-5 rounded-full bg-white transition-transform transform {{ $carParking ? 'translate-x-5' : 'translate-x-0' }}"></div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Complete Submit Button -->
            <div class="pt-6 border-t border-slate-200/60 mt-6">
                <button type="submit" 
                        class="w-full py-3 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                    Complete Onboarding
                </button>
            </div>
        </form>

        <div class="border-t border-slate-200 pt-4 flex justify-between items-center text-xs">
            <span class="text-slate-500">Already registered? <a href="/login" class="text-blue-600 hover:underline font-semibold">Sign In</a></span>
        </div>

    </div>
</div>
