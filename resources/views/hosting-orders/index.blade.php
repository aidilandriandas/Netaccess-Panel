@extends('layouts.app')
@section('title', 'Hosting Orders')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari order/domain..."
               class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-blue-500 outline-none">

        <select name="status" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">Semua Status</option>
            @foreach(['pending','paid','provisioning','active','failed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
            Filter
        </button>
    </form>

    <a href="{{ route('hosting-orders.create') }}" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Buat Order
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="px-4 py-3">Order #</th>
                    <th class="px-4 py-3">Domain</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Paket</th>
                    <th class="px-4 py-3">Total</th>
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
                        {{ $order->domain }}
                    </td>

                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $order->customer?->name ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $order->hostingPackage?->name ?? '-' }}
                    </td>

                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </td>

                    <td class="px-4 py-3">
                        <x-status-badge :status="$order->status" />
                    </td>

                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        {{ $order->created_at?->format('d M Y') ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('hosting-orders.show', $order) }}"
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-xs font-medium">
                                Detail
                            </a>

                            <form method="POST"
                                  action="{{ route('hosting-orders.destroy', $order) }}"
                                  class="inline"
                                  onsubmit="return confirm('Yakin hapus hosting order {{ $order->order_number }}?')">
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
                        Belum ada order hosting.
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
