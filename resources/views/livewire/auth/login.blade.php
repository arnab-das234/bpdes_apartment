<div class="min-h-[70vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="mx-auto h-12 w-12 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-2xl text-white shadow-lg shadow-blue-500/20">
            B
        </div>
        <h2 class="mt-6 text-3xl font-extrabold text-slate-900 tracking-tight">Sign in to BPDES</h2>
        <p class="mt-2 text-sm text-slate-500">
            Building Planning, Development & Authority Management System
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="glass-panel p-8 rounded-xl shadow-xl">
            <form wire:submit.prevent="login" class="space-y-6">
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                    <input id="email" type="email" wire:model="email" placeholder="e.g. admin@society.com" 
                           class="w-full text-xs p-3 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20" required>
                    @error('email') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                    <input id="password" type="password" wire:model="password" placeholder="••••••••" 
                           class="w-full text-xs p-3 rounded bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20" required>
                    @error('password') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <button type="submit" class="w-full py-3 px-4 rounded bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/10 transition-all">
                        Sign In
                    </button>
                </div>
            </form>

            <!-- Test credentials tips -->
            <div class="mt-6 border-t border-slate-200 pt-6">
                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Local Demo & Role Logins</h4>
                <div class="space-y-2 text-[11px] text-slate-600">
                    
                    <!-- Cashier Role Card -->
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-300 shadow-sm space-y-1">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="font-extrabold text-emerald-950 text-xs block">💸 Cashier / Treasurer Desk</span>
                                <span class="font-mono text-emerald-800 text-[10px]">cashier@society.com</span>
                            </div>
                            <button type="button" wire:click="fillCashierCredentials" 
                                    class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] shadow cursor-pointer">
                                ⚡ Login as Cashier
                            </button>
                        </div>
                    </div>

                    <div class="p-2.5 rounded bg-slate-50 border border-slate-200 shadow-sm flex justify-between items-center">
                        <div>
                            <span class="font-bold text-slate-800">President (Royal Palm):</span>
                            <span class="font-mono text-slate-500">president@royalpalm.in</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded bg-slate-200 text-[9px] font-mono">password</span>
                    </div>

                    <div class="p-2.5 rounded bg-slate-50 border border-slate-200 shadow-sm flex justify-between items-center">
                        <div>
                            <span class="font-bold text-slate-800">Superadmin:</span>
                            <span class="font-mono text-slate-500">superadmin@bpdes.app</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded bg-slate-200 text-[9px] font-mono">password</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
