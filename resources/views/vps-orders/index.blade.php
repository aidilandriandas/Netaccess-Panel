@extends('layouts.app')
@section('title', 'VPS Orders')

@section('content')
@if(session('success'))
    <div class="mb-4 bg-green-900/30 border border-green-700 text-green-300 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-900/30 border border-red-700 text-red-300 px-4 py-3 rounded-lg">
        {{ session('error') }}
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Order # / Hostname..."
               class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-blue-500 outline-none">

        <select name="status"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">Semua Status</option>
            @foreach(['pending','unpaid','paid','provisioning','active','failed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
            Filter
        </button>
    </form>

    <a href="{{ route('vps-orders.create') }}"
       class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm whitespace-nowrap">
        + Buat Order
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="px-4 py-3">Order #</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Package</th>
                    <th class="px-4 py-3">Hostname</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse($orders as $order)
                <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                        {{ $order->order_number }}
                    </td>

                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $order->customer?->name ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $order->vpsPackage?->name ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $order->hostname ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200 font-medium">
                        Rp {{ number_format($order->price ?? $order->total ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        <x-status-badge :status="$order->status" />
                    </td>

                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $order->created_at?->format('d/m/Y') ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <a href="{{ route('vps-orders.show', $order) }}"
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-xs font-medium">
                                Detail
                            </a>

                            <form method="POST"
                                  action="{{ route('vps-orders.update-status', $order) }}"
                                  class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit"
                                        class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 text-xs font-medium">
                                    Cancel
                                </button>
                            </form>

                            <form method="POST"
                                  action="{{ route('vps-orders.destroy', $order) }}"
                                  class="inline"
                                  onsubmit="return confirm('Yakin hapus VPS order {{ $order->order_number }}?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 text-xs font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        Belum ada VPS order.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $orders->links() }}
    </div>
</div>
@endsection
