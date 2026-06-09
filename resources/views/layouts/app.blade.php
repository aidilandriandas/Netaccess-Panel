<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - NetAccess Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @php
    $adminPolishPath = public_path('css/admin-polish.css');
    $adminPolishVersion = file_exists($adminPolishPath) ? filemtime($adminPolishPath) : time();
@endphp
<link rel="stylesheet" href="{{ asset('css/admin-polish.css') }}?v={{ $adminPolishVersion }}">
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen transition-colors duration-200">

@php
    try {
        $pendingPaymentConfirmations = \Illuminate\Support\Facades\Schema::hasTable('payment_confirmations')
            ? \Illuminate\Support\Facades\DB::table('payment_confirmations')->where('status', 'pending')->count()
            : 0;
    } catch (\Throwable $e) {
        $pendingPaymentConfirmations = 0;
    }
@endphp


<div class="na-global-bg" aria-hidden="true">
    <span class="na-global-orb orb-a"></span>
    <span class="na-global-orb orb-b"></span>
    <span class="na-global-orb orb-c"></span>
    <span class="na-global-orb orb-d"></span>
    <i class="na-global-line line-a"></i>
    <i class="na-global-line line-b"></i>
</div>

    <div class="flex min-h-screen">
        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/50 lg:hidden" x-transition.opacity></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-slate-800 via-slate-900 to-slate-950 dark:from-gray-900 dark:via-gray-950 dark:to-black transform transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-0 flex flex-col shadow-xl">
            {{-- Logo --}}
            <div class="flex items-center justify-between h-16 px-4 border-b border-white/10">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <span class="text-lg font-bold text-white">NetAccess</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                @php $current = request()->route()->getName() ?? ''; @endphp

                <x-nav-link href="{{ route('dashboard') }}" :active="$current === 'dashboard'" icon="home">Dashboard</x-nav-link>
                <x-nav-link href="{{ route('customers.index') }}" :active="str_starts_with($current, 'customers')" icon="users">Customers</x-nav-link>
                <x-nav-link href="{{ route('vpn-users.index') }}" :active="str_starts_with($current, 'vpn-users')" icon="vpn">VPN Users</x-nav-link>
                <x-nav-link href="{{ route('packages.index') }}" :active="str_starts_with($current, 'packages')" icon="package">Packages</x-nav-link>
                <x-nav-link href="{{ route('invoices.index') }}" :active="str_starts_with($current, 'invoices')" icon="invoice">Invoices</x-nav-link>

                <div class="pt-3 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">Hosting</p>
                </div>

                <x-nav-link href="{{ route('hosting-accounts.index') }}" :active="str_starts_with($current, 'hosting-accounts')" icon="server">Hosting Accounts</x-nav-link>
                <x-nav-link href="{{ route('unified-orders.create') }}" :active="request()->routeIs('unified-orders.*')" icon="invoice">Orders</x-nav-link>

                <div class="pt-3 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">VPS</p>
                </div>

                <x-nav-link href="{{ route('vps-servers.index') }}" :active="str_starts_with($current, 'vps-servers')" icon="server">VPS Servers</x-nav-link>
                <x-nav-link href="{{ route('vps-services.index') }}" :active="str_starts_with($current, 'vps-services')" icon="vpn">VPS Services</x-nav-link>

                <div class="pt-3 pb-1 px-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">System</p>
                </div>

                <x-nav-link href="{{ route('server-status') }}" :active="$current === 'server-status'" icon="server">Server Status</x-nav-link>
                <x-nav-link href="{{ route('activity-logs') }}" :active="$current === 'activity-logs'" icon="log">Activity Logs</x-nav-link>
                <x-nav-link href="{{ route('settings') }}" :active="str_starts_with($current, 'settings')" icon="settings">Settings</x-nav-link>
                <x-nav-link href="{{ route('admin.full-backups.index') }}" :active="request()->routeIs('admin.full-backups.*') || str_starts_with($current, 'backups')" icon="backup">Backup</x-nav-link>
                @if(auth()->user()->isOwner())
                <x-nav-link href="{{ route('admin-users.index') }}" :active="str_starts_with($current, 'admin-users')" icon="admin">Users</x-nav-link>
                @endif
            
@php
    $adminTicketBadge = 0;

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('support_tickets') && auth()->check()) {
            $adminTicketBadge = \App\Models\SupportTicket::where('status', 'open')->count();
        }
    } catch (\Throwable $e) {
        $adminTicketBadge = 0;
    }
@endphp

<a href="{{ route('admin.tickets.index') }}"
   style="display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:16px;text-decoration:none;font-weight:900;color:{{ request()->routeIs('admin.tickets.*') ? '#ffffff' : '#cbd5e1' }};background:{{ request()->routeIs('admin.tickets.*') ? 'linear-gradient(135deg,#16a34a,#2563eb)' : 'rgba(15,23,42,.35)' }};border:1px solid rgba(148,163,184,.14);margin-top:8px;">
    <span>🎫</span>
    <span>Support Tickets</span>
    @if(($adminTicketBadge ?? 0) > 0)
        <span style="margin-left:auto;min-width:24px;height:24px;padding:0 8px;border-radius:999px;background:#ef4444;color:white;font-size:12px;font-weight:950;display:inline-flex;align-items:center;justify-content:center;">
            {{ $adminTicketBadge > 99 ? '99+' : $adminTicketBadge }}
        </span>
    @endif
</a>

@if(Route::has('payment-confirmations.index'))
    <a href="{{ route('payment-confirmations.index') }}"
   class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('payment-confirmations.*') ? 'active bg-purple-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
    <span class="flex items-center gap-3">
        <span>💳</span>
        <span>Payment Confirmations</span>
    </span>

    @if(($pendingPaymentConfirmations ?? 0) > 0)
        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-black text-white shadow-lg">
            {{ $pendingPaymentConfirmations }}
        </span>
    @endif
</a>
@endif


</nav>

            {{-- User Section --}}
            <div class="border-t border-white/10 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-cyan-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="ml-3 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-red-400 transition-colors" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top Bar --}}
            <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 lg:px-6 shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800 dark:text-white">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                            class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </button>
                    <a href="{{ route('change-password') }}" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors" title="Change Password">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </a>
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
                {{-- Flash Messages --}}
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 dark:bg-emerald-800 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-sm text-emerald-700 dark:text-emerald-300 flex-1">{{ session('success') }}</span>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-red-100 dark:bg-red-800 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <span class="text-sm text-red-700 dark:text-red-300 flex-1">{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-500 hover:text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
