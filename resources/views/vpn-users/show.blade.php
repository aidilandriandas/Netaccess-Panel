@extends('layouts.app')
@section('title', 'Detail VPN User')

@section('content')

<div style="margin-bottom:14px;display:flex;justify-content:flex-end;">
    @include('components.client-360-button', [
    'userId' => $vpnUser->user_id ?? $vpnUser->customer?->user_id ?? null,
    'customerId' => $vpnUser->customer_id ?? $vpnUser->customer?->id ?? null,
])
</div>

<div class="space-y-6">

    {{-- Header & Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $vpnUser->username }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Customer:
                    <a href="{{ route('customers.show', $vpnUser->customer) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $vpnUser->customer->name }}
                    </a>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <x-status-badge :status="$vpnUser->status" />

                @if($vpnUser->status === 'active')
                <form method="POST" action="{{ route('vpn-users.suspend', $vpnUser) }}"
                      x-data @submit.prevent="if(confirm('Suspend VPN user ini?')) $el.submit()">
                    @csrf
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded-lg text-sm">
                        Suspend
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('vpn-users.unsuspend', $vpnUser) }}">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm">
                        Unsuspend
                    </button>
                </form>
                @endif

                <a href="{{ route('vpn-users.download-config', $vpnUser) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm">
                    Download Panduan
                </a>

                <form method="POST" action="{{ route('vpn-users.destroy', $vpnUser) }}"
                      x-data @submit.prevent="if(confirm('Hapus VPN user ini?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-sm">
                        Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500 dark:text-gray-400">Paket:</span>
                <span class="text-gray-800 dark:text-gray-200">{{ $vpnUser->package->name }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">IP Address:</span>
                <span class="font-mono text-gray-800 dark:text-gray-200">{{ $vpnUser->assigned_ip ?? '-' }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Mulai:</span>
                <span class="text-gray-800 dark:text-gray-200">{{ $vpnUser->started_at?->format('d M Y H:i') ?? '-' }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Expired:</span>
                <span class="text-gray-800 dark:text-gray-200 {{ $vpnUser->isExpired() ? 'text-red-600 dark:text-red-400 font-bold' : '' }}">
                    {{ $vpnUser->expired_at?->format('d M Y H:i') ?? '-' }}
                </span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Sisa:</span>
                <span class="text-gray-800 dark:text-gray-200">
                    {{ $vpnUser->daysUntilExpiry() !== null ? $vpnUser->daysUntilExpiry() . ' hari' : '-' }}
                </span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Status Mikrotik:</span>
                @if($activeSession)
                    <span class="text-green-600 dark:text-green-400 font-medium">● Online</span>
                @else
                    <span class="text-gray-400">● Offline</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Perpanjang --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">Perpanjang Masa Aktif</h3>
        <form method="POST" action="{{ route('vpn-users.extend', $vpnUser) }}" class="flex items-end gap-3">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Jumlah Hari</label>
                <input type="number" name="days" value="30" min="1" max="365" required
                       class="w-32 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                Perpanjang
            </button>
        </form>
    </div>

    {{-- Kredensial & Panduan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Kredensial --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6"
             x-data="{ showPass: false }">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">Kredensial L2TP</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-0.5">Username</span>
                    <code class="bg-gray-100 dark:bg-gray-900 px-3 py-1.5 rounded-lg text-sm font-mono text-gray-800 dark:text-gray-200 block select-all">{{ $vpnUser->username }}</code>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="text-gray-500 dark:text-gray-400">Password</span>
                        <button @click="showPass = !showPass" class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                x-text="showPass ? 'Sembunyikan' : 'Tampilkan'"></button>
                    </div>
                    <code x-show="showPass" x-cloak
                          class="bg-gray-100 dark:bg-gray-900 px-3 py-1.5 rounded-lg text-sm font-mono text-gray-800 dark:text-gray-200 block select-all">{{ $vpnUser->l2tp_password }}</code>
                    <code x-show="!showPass"
                          class="bg-gray-100 dark:bg-gray-900 px-3 py-1.5 rounded-lg text-sm font-mono text-gray-400 block">
                        ••••••••••••••••
                    </code>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-gray-400 block mb-0.5">IP Address</span>
                    <code class="bg-gray-100 dark:bg-gray-900 px-3 py-1.5 rounded-lg text-sm font-mono text-gray-800 dark:text-gray-200 block">{{ $vpnUser->assigned_ip ?? '-' }}</code>
                </div>
            </div>
        </div>

        {{-- Panduan Koneksi --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">Panduan Koneksi L2TP/IPSec</h3>
            <pre class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-700 dark:text-gray-300 overflow-x-auto whitespace-pre-wrap">{{ $instructions }}</pre>
        </div>
    </div>

    {{-- Session Aktif --}}
    @if($activeSession)
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-green-200 dark:border-green-700 p-6">
        <h3 class="text-sm font-semibold text-green-700 dark:text-green-400 mb-3">● Sesi Aktif di Mikrotik</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            @foreach($activeSession as $key => $val)
                @if(!str_starts_with($key, '.'))
                <div>
                    <span class="text-gray-500 dark:text-gray-400">{{ $key }}:</span>
                    <span class="text-gray-800 dark:text-gray-200 font-mono">{{ $val }}</span>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
