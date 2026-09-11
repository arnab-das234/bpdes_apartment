@php
    $user = Auth::user();
    $organization = $user?->organization;
    $customRole = $user?->roleRelation;
    $roleKey = $user?->effectiveRoleKey() ?? 'guest';

    $workspaceThemes = [
        'superadmin' => [
            'title' => 'Superadmin Workspace',
            'role' => 'Platform Superadmin',
            'position' => 'Global System Authority',
            'caption' => 'Organization Onboarding, Multi-Tenant Governance & System RBAC',
            'initials' => 'SA',
            'route' => '/superadmin/dashboard',
            'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'accent_bg' => 'bg-indigo-600 text-white',
            'card_border' => 'border-indigo-200 bg-indigo-50/50',
            'banner_tag' => 'SYSTEM PLATFORM',
            'icon_svg' => '<svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        ],
        'president' => [
            'title' => 'President Workspace',
            'role' => 'President',
            'position' => 'Executive Approval Officer',
            'caption' => 'Executive Proposal Decisions, Committee Oversight & Fund Approvals',
            'initials' => 'PR',
            'route' => '/president-inbox',
            'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'accent_bg' => 'bg-emerald-600 text-white',
            'card_border' => 'border-emerald-200 bg-emerald-50/50',
            'banner_tag' => 'EXECUTIVE OFFICE',
            'icon_svg' => '<svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        ],
        'secretary' => [
            'title' => 'Secretary Workspace',
            'role' => 'Secretary',
            'position' => 'Operations & Administration Desk',
            'caption' => 'Society Management, Resolutions, Project Milestones & Resident Records',
            'initials' => 'SE',
            'route' => '/control-center',
            'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
            'accent_bg' => 'bg-amber-600 text-white',
            'card_border' => 'border-amber-200 bg-amber-50/50',
            'banner_tag' => 'ADMINISTRATION DESK',
            'icon_svg' => '<svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        ],
        'treasurer' => [
            'title' => 'Treasurer Workspace',
            'role' => 'Treasurer',
            'position' => 'Chief Financial & Ledger Controller',
            'caption' => 'Reserve Accounts, Maintenance Dues, Audit Trails & Double-Entry Ledgers',
            'initials' => 'TR',
            'route' => '/control-center',
            'badge' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            'accent_bg' => 'bg-cyan-600 text-white',
            'card_border' => 'border-cyan-200 bg-cyan-50/50',
            'banner_tag' => 'TREASURY DESK',
            'icon_svg' => '<svg class="w-5 h-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 10v-1m0 1c-1.11 0-2.08-.402-2.599-1M5 5h14v14H5z"/></svg>',
        ],
        'resident' => [
            'title' => 'Resident Workspace',
            'role' => 'Resident Member',
            'position' => 'Flat Unit & Self-Service Portal',
            'caption' => 'Flat Profile, Maintenance Bills, Grievance Logging & Community Board',
            'initials' => 'RE',
            'route' => '/resident/dashboard',
            'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
            'accent_bg' => 'bg-purple-600 text-white',
            'card_border' => 'border-purple-200 bg-purple-50/50',
            'banner_tag' => 'MEMBER PORTAL',
            'icon_svg' => '<svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        ],
    ];

    $theme = $workspaceThemes[$roleKey] ?? [
        'title' => 'Committee Workspace',
        'role' => $customRole?->name ? ucwords($customRole->name) : ucwords(str_replace('_', ' ', $user?->role ?? 'Member')),
        'position' => 'Assigned Executive Officer',
        'caption' => 'Role-Based Society Governance, Planning & Operations Workspace',
        'initials' => strtoupper(substr($customRole?->name ?? $user?->role ?? 'MB', 0, 2)),
        'route' => '/control-center',
        'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
        'accent_bg' => 'bg-blue-600 text-white',
        'card_border' => 'border-blue-200 bg-blue-50/50',
        'banner_tag' => 'COMMITTEE OFFICE',
        'icon_svg' => '<svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
    ];

    $orgName = $organization?->short_name ?? $organization?->name ?? 'BPDES Authority';
    $orgFullName = $organization?->name ?? 'Building Premises Digital Estate System';
    $displayName = $user?->name ?? 'Guest User';
    $displayInitials = collect(explode(' ', trim($displayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: $theme['initials'];
    $profilePhotoPath = $user?->profile_photo_path ?? $user?->person?->photo_path;
    $profilePhotoUrl = $profilePhotoPath ? \Illuminate\Support\Facades\Storage::url($profilePhotoPath) : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'BPDES - Control Center' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full text-slate-800 antialiased bg-slate-50">
    <div class="flex h-full min-h-screen">
        <!-- SIDEBAR -->
        <aside class="w-72 bg-white text-slate-700 border-r border-slate-200 flex flex-col justify-between shrink-0 shadow-sm">
            <div>
                <!-- Brand Header -->
                <div class="px-5 py-5 border-b border-slate-200 bg-white">
                    <a href="{{ $theme['route'] }}" wire:navigate class="flex items-center gap-3 group">
                        <div class="h-11 w-11 rounded-lg {{ $theme['accent_bg'] }} flex items-center justify-center font-black text-xl text-white shadow-sm group-hover:scale-105 transition-all">
                            B
                        </div>
                        <div class="min-w-0">
                            <h1 class="font-black text-lg tracking-wider uppercase leading-tight text-slate-900 group-hover:text-blue-600 transition-colors">BPDES</h1>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold truncate">{{ $orgName }}</p>
                        </div>
                    </a>

                    <!-- Dynamic Role Box -->
                    <div class="mt-5 overflow-hidden rounded-lg border {{ $theme['card_border'] }}">
                        <div class="relative px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest border {{ $theme['badge'] }} mb-1">
                                        {{ $theme['banner_tag'] }}
                                    </span>
                                    <h2 class="text-base font-extrabold leading-tight text-slate-900 mt-1">{{ $theme['title'] }}</h2>
                                    <p class="text-[10px] text-slate-500 font-semibold mt-0.5">{{ $theme['position'] }}</p>
                                </div>
                                <div class="h-10 w-10 overflow-hidden rounded-lg bg-white border border-slate-200 flex items-center justify-center text-xs font-black text-slate-700 shrink-0 shadow-xs">
                                    @if($profilePhotoUrl)
                                        <img src="{{ $profilePhotoUrl }}" alt="{{ $displayName }}" class="h-full w-full object-cover">
                                    @else
                                        {!! $theme['icon_svg'] !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-slate-200/80 px-4 py-2.5 bg-white/80">
                            <p class="text-[10px] font-medium leading-relaxed text-slate-500 line-clamp-2">{{ $theme['caption'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Nav links -->
                <nav class="p-4 space-y-1">
                    @auth
                        @if($roleKey === 'superadmin' || is_null($user->organization_id))
                            <a href="/superadmin/dashboard" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->is('superadmin/dashboard') ? 'bg-slate-100 text-blue-700 font-bold border border-slate-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                                Superadmin Panel
                            </a>
                            <a href="/admin/organizations" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->is('admin/organizations') ? 'bg-slate-100 text-blue-700 font-bold border border-slate-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                Organizations CRUD
                            </a>
                        @elseif($roleKey === 'resident')
                            <a href="/resident/dashboard" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->is('resident/dashboard') ? 'bg-slate-100 text-purple-700 font-bold border border-slate-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="h-5 w-5 shrink-0 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                My Flat Profile
                            </a>
                        @else
                            <a href="/control-center" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->is('control-center') && ! in_array(request()->query('tab'), ['inventory-desk', 'balance-sheet'], true) ? 'bg-slate-100 text-blue-700 font-bold border border-slate-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" /></svg>
                                Control Center
                            </a>

                            @if($roleKey === 'cashier')
                            <!-- Society Finance & Inventory Sidebar Section -->
                            <div class="pl-3 space-y-1 pt-1 border-l-2 border-slate-200 ml-5 my-1">
                                <a href="/control-center?tab=inventory-desk" wire:navigate class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->is('control-center') && request()->query('tab') === 'inventory-desk' ? 'bg-indigo-50 text-indigo-700 font-extrabold border border-indigo-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <span class="text-sm">📦</span>
                                    <span>Inventory & Stock Reserve</span>
                                </a>
                                <a href="/control-center?tab=balance-sheet" wire:navigate class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->is('control-center') && request()->query('tab') === 'balance-sheet' ? 'bg-cyan-50 text-cyan-700 font-extrabold border border-cyan-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <span class="text-sm">📊</span>
                                    <span>Balance Sheet Report</span>
                                </a>
                            </div>
                            @endif
                            
                            @if($roleKey === 'president')
                                <a href="/president-inbox" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->is('president-inbox') && ! in_array(request()->query('tab'), ['cash-release-bills', 'inventory-desk', 'balance-sheet'], true) ? 'bg-slate-100 text-emerald-700 font-bold border border-slate-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2m16 4h-2a2 2 0 01-2 2v1a2 2 0 01-2 2H8a2 2 0 01-2-2v-1a2 2 0 00-2-2H2" /></svg>
                                    President's Inbox
                                    @php
                                        $pendingCount = \App\Modules\Planning\Models\Proposal::where('status', \App\Modules\Planning\Models\Proposal::STATUS_PRESIDENT_REVIEW)->count();
                                    @endphp
                                    @if($pendingCount > 0)
                                        <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            {{ $pendingCount }}
                                        </span>
                                    @endif
                                </a>

                                <!-- President Finance & Inventory Sidebar Section -->
                                <div class="pl-3 space-y-1 pt-1 border-l-2 border-slate-200 ml-5 my-1">
                                    <a href="/president-inbox?tab=cash-release-bills" wire:navigate class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->is('president-inbox') && request()->query('tab') === 'cash-release-bills' ? 'bg-emerald-50 text-emerald-700 font-extrabold border border-emerald-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <span class="text-sm">💸</span>
                                        <span>Cash Release Desk</span>
                                    </a>
                                    <a href="/president-inbox?tab=inventory-desk" wire:navigate class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->is('president-inbox') && request()->query('tab') === 'inventory-desk' ? 'bg-indigo-50 text-indigo-700 font-extrabold border border-indigo-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <span class="text-sm">📦</span>
                                        <span>Inventory & Stock Reserve</span>
                                    </a>
                                    <a href="/president-inbox?tab=balance-sheet" wire:navigate class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-bold transition-all {{ request()->is('president-inbox') && request()->query('tab') === 'balance-sheet' ? 'bg-cyan-50 text-cyan-700 font-extrabold border border-cyan-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                        <span class="text-sm">📊</span>
                                        <span>Balance Sheet Report</span>
                                    </a>
                                </div>
                            @endif
                        @endif

                        <div class="pt-3 mt-3 border-t border-slate-200 space-y-1">
                            <a href="/profile/settings" wire:navigate class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all {{ request()->is('profile/settings') ? 'bg-slate-100 text-slate-900 border border-slate-200 shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <svg class="h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8.97 8.97 0 0112 15c2.21 0 4.236.799 5.804 2.121M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Profile Settings
                            </a>
                            <a href="/logout" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-all">
                                <svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                Logout
                            </a>
                        </div>
                    @endauth
                </nav>
            </div>

            <!-- Footer Card -->
            <footer class="p-4 border-t border-slate-200 bg-white">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 overflow-hidden rounded-lg flex items-center justify-center text-xs font-black text-white shrink-0 {{ $theme['accent_bg'] }} shadow-xs">
                            @if($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="{{ $displayName }}" class="h-full w-full object-cover">
                            @else
                                {{ $displayInitials }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-extrabold text-slate-900 truncate leading-tight">{{ $displayName }}</p>
                            <span class="inline-block px-2 py-0.5 text-[8px] font-extrabold uppercase tracking-widest rounded border {{ $theme['badge'] }} mt-0.5 truncate">
                                {{ $theme['role'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        </aside>

        <!-- MAIN WORKSPACE CONTENT -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-50">
            <!-- TOP HEADER -->
            <header class="min-h-20 border-b border-slate-200 bg-white px-8 py-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between shrink-0 shadow-2xs">
                <!-- Title & Role Info -->
                <div class="flex items-center gap-4 min-w-0">
                    <div class="h-12 w-12 overflow-hidden rounded-xl flex items-center justify-center text-base font-black text-slate-700 bg-slate-50 border border-slate-200 shrink-0 shadow-sm">
                        @if($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="{{ $displayName }}" class="h-full w-full object-cover">
                        @else
                            {!! $theme['icon_svg'] !!}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded text-[9px] uppercase tracking-widest border {{ $theme['badge'] }}">
                                {{ $theme['banner_tag'] }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">• {{ $theme['position'] }}</span>
                        </div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight leading-tight mt-0.5 truncate">{{ $theme['title'] }}</h2>
                        <p class="text-xs text-slate-500 truncate mt-0.5">
                            <span>Organization:</span> <strong class="text-slate-800 font-bold">{{ $orgFullName }}</strong>
                            <span class="mx-2 text-slate-300">|</span>
                            <span>Active User:</span> <strong class="text-slate-800 font-bold">{{ $displayName }}</strong>
                        </p>
                    </div>
                </div>

                <!-- Status Pills -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    @if($organization?->official_email)
                        <div class="px-3 py-1.5 rounded-lg bg-white text-slate-700 border border-slate-200 font-bold shadow-2xs flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{{ $organization->official_email }}</span>
                        </div>
                    @endif
                    @if($organization?->official_mobile)
                        <div class="px-3 py-1.5 rounded-lg bg-white text-slate-700 border border-slate-200 font-bold shadow-2xs flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ $organization->official_mobile }}</span>
                        </div>
                    @endif
                    <div class="px-3 py-1.5 rounded-lg {{ $theme['badge'] }} font-bold shadow-2xs flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ $theme['role'] }} Active</span>
                    </div>
                </div>
            </header>

            <!-- MAIN WORKSPACE CONTENT SLOT -->
            <div class="p-8 flex-1">
                {{ $slot }}
            </div>

            <!-- PAGE FOOTER -->
            <footer class="px-8 py-2 border-t border-slate-200 bg-white text-center shrink-0">
                <p class="text-slate-400 font-medium text-xs">BPDES Control Center | {{ now()->format('d M Y') }}</p>
            </footer>
        </main>
    </div>

    @livewireScripts
</body>
</html>
