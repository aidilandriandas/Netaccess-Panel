@extends('layouts.app')
@section('title', 'VPS: ' . $service->hostname)

@section('content')

<div style="margin-bottom:14px;display:flex;justify-content:flex-end;">
    @include('components.client-360-button', [
    'userId' => $vpsService->user_id ?? $vpsService->customer?->user_id ?? null,
    'customerId' => $vpsService->customer_id ?? $vpsService->customer?->id ?? null,
])
</div>

<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $service->hostname }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $service->customer?->name }} &middot; {{ $service->vpsPackage?->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <x-status-badge :status="$service->status" />
            <a href="{{ route('vps-services.edit', $service) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm transition-colors">Edit</a>
        </div>
    </div>

    {{-- Info Grid --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">IP Address</p>
                <p class="font-medium text-gray-800 dark:text-white font-mono">{{ $service->main_ip ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">OS</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ $service->os_name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">CPU</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ $service->cpu_cores }} Core</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">RAM</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ $service->ram_mb >= 1024 ? ($service->ram_mb / 1024) . ' GB' : $service->ram_mb . ' MB' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">Storage</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ $service->storage_gb }} GB</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">Bandwidth</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ $service->bandwidth_gb ? $service->bandwidth_gb . ' GB' : 'Unlimited' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">SSH Port</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ $service->ssh_port }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">Server</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ $service->vpsServer?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">Username</p>
                <p class="font-medium text-gray-800 dark:text-white font-mono">{{ $service->username ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">Password</p>
                <p class="font-medium text-gray-800 dark:text-white" x-data="{ show: false }">
                    <span x-show="!show">••••••••</span>
                    <span x-show="show" x-cloak class="font-mono">{{ $service->password ?? '-' }}</span>
                    <button @click="show = !show" class="text-blue-500 hover:text-blue-700 text-xs ml-2" x-text="show ? 'Hide' : 'Show'"></button>
                </p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">Started</p>
                <p class="font-medium text-gray-800 dark:text-white">{{ $service->started_at?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-xs">Expired</p>
                <p class="font-medium {{ $service->isExpired() ? 'text-red-600' : 'text-gray-800 dark:text-white' }}">
                    {{ $service->expired_at?->format('d/m/Y') ?? '-' }}
                    @if($service->expired_at)
                        ({{ $service->daysUntilExpiry() > 0 ? $service->daysUntilExpiry() . ' hari lagi' : 'Sudah expired' }})
                    @endif
                </p>
            </div>
        </div>
        @if($service->notes)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">Notes</p>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $service->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Actions</h3>
        <div class="flex flex-wrap gap-2">
            @if($service->status === 'active')
            <form method="POST" action="{{ route('vps-services.suspend', $service) }}" x-data @submit.prevent="if(confirm('Suspend VPS ini?')) $el.submit()">
                @csrf
                <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-lg text-sm transition-colors">Suspend</button>
            </form>
            @endif

            @if($service->status === 'suspended')
            <form method="POST" action="{{ route('vps-services.unsuspend', $service) }}">
                @csrf
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-sm transition-colors">Unsuspend</button>
            </form>
            @endif

            @if(!in_array($service->status, ['terminated']))
            <form method="POST" action="{{ route('vps-services.terminate', $service) }}" x-data @submit.prevent="if(confirm('TERMINATE VPS ini? Aksi ini tidak bisa dibatalkan.')) $el.submit()">
                @csrf
                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-lg text-sm transition-colors">Terminate</button>
            </form>
            @endif

            {{-- Extend Expiry --}}
            <form method="POST" action="{{ route('vps-services.extend', $service) }}" class="flex items-center gap-2">
                @csrf
                <input type="number" name="days" value="30" min="1" max="365" class="w-20 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-sm transition-colors">Extend (hari)</button>
            </form>

            <form method="POST" action="{{ route('vps-services.destroy', $service) }}" x-data @submit.prevent="if(confirm('Hapus record VPS ini?')) $el.submit()">
                @csrf @method('DELETE')
                <button class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-1.5 rounded-lg text-sm transition-colors">Hapus Record</button>
            </form>
        </div>
    </div>

    {{-- Related Orders --}}
    @if($service->vpsOrders->count())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">VPS Orders</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50">
                    <th class="px-4 py-2">Order #</th><th class="px-4 py-2">Harga</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Invoice</th><th class="px-4 py-2">Tanggal</th>
                </tr>
            </thead>
            <tbody>
            @foreach($service->vpsOrders as $order)
                <tr class="border-t border-gray-100 dark:border-gray-700/50">
                    <td class="px-4 py-2 font-mono text-xs">{{ $order->order_number }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($order->price, 0, ',', '.') }}</td>
                    <td class="px-4 py-2"><x-status-badge :status="$order->status" /></td>
                    <td class="px-4 py-2">{{ $order->invoice?->invoice_number ?? '-' }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $order->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
