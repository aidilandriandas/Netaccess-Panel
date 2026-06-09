@extends('layouts.app')
@section('title', 'Detail Hosting: ' . $hostingAccount->domain)

@section('content')

<div style="margin-bottom:14px;display:flex;justify-content:flex-end;">
    @include('components.client-360-button', [
    'userId' => $hostingAccount->user_id ?? $hostingAccount->customer?->user_id ?? null,
    'customerId' => $hostingAccount->customer_id ?? $hostingAccount->customer?->id ?? null,
])
</div>

<div class="max-w-4xl space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ $hostingAccount->domain }}</h2>
            <x-status-badge :status="$hostingAccount->status" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500 dark:text-gray-400">Username:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ $hostingAccount->username }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Password:</span> <span class="font-mono text-gray-800 dark:text-gray-200">{{ $hostingAccount->password }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Customer:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ $hostingAccount->customer->name }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Paket:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ $hostingAccount->hostingPackage->name }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Server Type:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ strtoupper($hostingAccount->server_type) }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Disk:</span> <span class="text-gray-800 dark:text-gray-200">{{ number_format($hostingAccount->hostingPackage->disk_space_mb) }} MB</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Mulai:</span> <span class="text-gray-800 dark:text-gray-200">{{ $hostingAccount->started_at?->format('d M Y') ?? '-' }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Expired:</span> <span class="text-gray-800 dark:text-gray-200">{{ $hostingAccount->expired_at?->format('d M Y') ?? '-' }}</span></div>
        </div>
        @if($hostingAccount->notes)
        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg text-sm text-gray-600 dark:text-gray-400">{{ $hostingAccount->notes }}</div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Aksi</h3>
        <div class="flex flex-wrap gap-2">
            @if($hostingAccount->status === 'pending')
            <form method="POST" action="{{ route('hosting-accounts.provision', $hostingAccount) }}">
                @csrf
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Provision</button>
            </form>
            @endif
            @if($hostingAccount->status === 'active')
            <form method="POST" action="{{ route('hosting-accounts.suspend', $hostingAccount) }}" x-data @submit.prevent="if(confirm('Suspend akun ini?')) $el.submit()">
                @csrf
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Suspend</button>
            </form>
            @endif
            @if($hostingAccount->status === 'suspended')
            <form method="POST" action="{{ route('hosting-accounts.unsuspend', $hostingAccount) }}">
                @csrf
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Unsuspend</button>
            </form>
            @endif
            @if(in_array($hostingAccount->status, ['active', 'suspended']))
            <form method="POST" action="{{ route('hosting-accounts.terminate', $hostingAccount) }}" x-data @submit.prevent="if(confirm('TERMINATE akun ini? Aksi ini tidak bisa dibatalkan!')) $el.submit()">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Terminate</button>
            </form>
            @endif
            <a href="{{ route('hosting-accounts.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Kembali</a>
        </div>
    </div>
</div>
@endsection
