@extends('layouts.app')
@section('title', 'Detail VPS Order')

@section('content')
@php
    $order = $vpsOrder ?? $order ?? $vps_order ?? null;
@endphp

@if(!$order)
    <div class="bg-red-900/30 border border-red-700 text-red-300 px-4 py-3 rounded-lg">
        Data VPS order tidak ditemukan / variable tidak dikirim dari controller.
    </div>
@else
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

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-4xl">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $order->order_number }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $order->customer?->name ?? '-' }} · {{ $order->vpsPackage?->name ?? '-' }}
                </p>
            </div>

            <x-status-badge :status="$order->status" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Hostname</span><br>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ $order->hostname ?? '-' }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">OS</span><br>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ $order->os_name ?? $order->os ?? '-' }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Billing Cycle</span><br>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ ucfirst($order->billing_cycle ?? 'monthly') }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Harga</span><br>
                <span class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($order->price ?? $order->total ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Invoice</span><br>
                @if($order->invoice)
                    <a href="{{ route('invoices.show', $order->invoice) }}"
                       class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $order->invoice->invoice_number }}
                    </a>
                @else
                    <span class="font-semibold text-gray-900 dark:text-white">-</span>
                @endif
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">VPS Service</span><br>
                @if($order->vpsService)
                    <a href="{{ route('vps-services.show', $order->vpsService) }}"
                       class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $order->vpsService->hostname ?? 'Detail VPS' }}
                    </a>
                @else
                    <span class="font-semibold text-gray-900 dark:text-white">
                        Belum diprovision
                    </span>
                @endif
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Tanggal Order</span><br>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}
                </span>
            </div>
        </div>

        @if($order->notes)
            <div class="mt-6 bg-gray-100 dark:bg-gray-700/50 rounded-lg p-4 text-sm text-gray-700 dark:text-gray-300">
                {{ $order->notes }}
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-4xl">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Aksi</h3>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('vps-orders.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                Kembali
            </a>

            <form method="POST" action="{{ route('vps-orders.update-status', $order) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="pending">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm">
                    Set Pending
                </button>
            </form>

            <form method="POST" action="{{ route('vps-orders.update-status', $order) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="paid">
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm">
                    Set Paid
                </button>
            </form>

            <form method="POST" action="{{ route('vps-orders.update-status', $order) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="active">
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm">
                    Set Active
                </button>
            </form>

            <form method="POST" action="{{ route('vps-orders.update-status', $order) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="failed">
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm">
                    Set Failed
                </button>
            </form>

            <form method="POST" action="{{ route('vps-orders.update-status', $order) }}"
                  onsubmit="return confirm('Cancel VPS order ini?')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="px-4 py-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white text-sm">
                    Cancel
                </button>
            </form>

            <form method="POST" action="{{ route('vps-orders.destroy', $order) }}"
                  onsubmit="return confirm('Yakin hapus VPS order {{ $order->order_number }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-white text-sm">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
