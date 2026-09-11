<div class="relative overflow-hidden min-h-[85vh] flex flex-col justify-between py-12 px-4 sm:px-6 lg:px-8">
    
    <!-- Background Glowing Radial Gradients -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -right-40 w-[450px] h-[450px] bg-purple-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 left-1/3 w-80 h-80 bg-amber-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-6xl mx-auto w-full flex-1 flex flex-col justify-center items-center text-center">
        
        <!-- Authenticated Quick Redirect Link -->
        @auth
            <div class="mb-8 p-1.5 px-4 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold flex items-center gap-2 shadow-sm animate-bounce">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-ping"></span>
                Signed in as {{ Auth::user()->name }}
                <a href="/" class="ml-2 underline font-bold hover:text-blue-800">Go to Dashboard ➔</a>
            </div>
        @endauth

        <!-- Platform branding title -->
        <div class="space-y-4 mb-12">
            <span class="px-3.5 py-1 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[10px] font-black uppercase tracking-widest shadow-md shadow-blue-500/10">
                Next-Gen Cooperative Tech
            </span>
            <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight max-w-3xl mx-auto">
                The Smart Apartment & <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600">Society Master</span> Platform
            </h1>
            <p class="text-sm text-slate-500 max-w-2xl mx-auto leading-relaxed">
                BPDES streamlines residential cooperative management with strict multi-tenant Row-Level Security, visual flat onboarding wizards, double-entry financial accounting, and cooperative voting feedback.
            </p>
        </div>

        <!-- High-Impact Colorful UI Cards (Primary CTAs) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-4xl mb-16">
            
            <!-- Registration / Onboarding Card -->
            <div class="glass-panel p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-all border border-slate-200/80 bg-white/60 relative overflow-hidden group flex flex-col justify-between items-start text-left">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500/5 rounded-full group-hover:scale-150 transition-all duration-500"></div>
                <div>
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-blue-500/20 mb-6">
                        +
                    </div>
                    <h3 class="text-lg font-black text-slate-800 leading-snug">Resident Onboarding</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Onboard your flat, specify room sizes (BHK), floor numbers, family count, and claim your designated parking slot using our interactive selection map.
                    </p>
                </div>
                <a href="/register" class="w-full mt-8 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 text-white text-xs font-black text-center shadow-md shadow-blue-500/10 hover:shadow-lg hover:shadow-blue-500/20 hover:scale-[1.01] transition-all">
                    Register Flat Unit ➔
                </a>
            </div>

            <!-- Access Workspace / Login Card -->
            <div class="glass-panel p-8 rounded-2xl shadow-xl hover:shadow-2xl transition-all border border-slate-200/80 bg-white/60 relative overflow-hidden group flex flex-col justify-between items-start text-left">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-purple-500/5 rounded-full group-hover:scale-150 transition-all duration-500"></div>
                <div>
                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-purple-500/20 mb-6">
                        ➔
                    </div>
                    <h3 class="text-lg font-black text-slate-800 leading-snug">Access Workspace</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Log in as a Superadmin to manage organizations, or as a President/Committee Member to manage towers, appoint roles, and handle financial double-entry bookkeeping.
                    </p>
                </div>
                <a href="/login" class="w-full mt-8 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 text-white text-xs font-black text-center shadow-md shadow-purple-500/10 hover:shadow-lg hover:shadow-purple-500/20 hover:scale-[1.01] transition-all">
                    Sign In to Dashboard ➔
                </a>
            </div>

        </div>

        <!-- System Architecture Highlights -->
        <div class="border-t border-slate-200 pt-12 w-full">
            <h3 class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400 mb-8">Platform Features</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-left">
                <div class="p-4 rounded-xl bg-slate-50/50 border border-slate-200/40">
                    <h4 class="text-xs font-bold text-slate-800">Multi-Tenant RLS</h4>
                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">PostgreSQL-level isolation guards for all society documents and records.</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50/50 border border-slate-200/40">
                    <h4 class="text-xs font-bold text-slate-800">Double-Entry Ledger</h4>
                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">Accurate financial bookkeeping for maintenance dues and invoices.</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50/50 border border-slate-200/40">
                    <h4 class="text-xs font-bold text-slate-800">Democratic Upgrades</h4>
                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">Submit proposals and vote on society improvements directly.</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50/50 border border-slate-200/40">
                    <h4 class="text-xs font-bold text-slate-800">Grievance Desks</h4>
                    <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">Track plumbing or electrical maintenance on visual pipelines.</p>
                </div>
            </div>
        </div>

    </div>
</div>
