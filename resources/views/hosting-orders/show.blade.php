@extends('layouts.app')
@section('title', 'Detail Hosting Order')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="bg-green-900/30 border border-green-700 text-green-300 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-900/30 border border-red-700 text-red-300 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ $hostingOrder->order_number }}
            </h2>

            <x-status-badge :status="$hostingOrder->status" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Domain:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $hostingOrder->domain }}</span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Customer:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $hostingOrder->customer?->name ?? '-' }}</span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Paket:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $hostingOrder->hostingPackage?->name ?? '-' }}</span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Total:</span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($hostingOrder->total, 0, ',', '.') }}</span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Invoice:</span>
                @if($hostingOrder->invoice)
                    <a href="{{ route('invoices.show', $hostingOrder->invoice) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                        {{ $hostingOrder->invoice->invoice_number }}
                    </a>
                @else
                    <span class="font-semibold text-gray-900 dark:text-white">-</span>
                @endif
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Tanggal Order:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $hostingOrder->created_at?->format('d M Y H:i') ?? '-' }}</span>
            </div>
        </div>

        @if($hostingOrder->provision_log)
            <div class="mt-6 bg-gray-100 dark:bg-gray-700/50 rounded-lg p-4 text-sm text-gray-700 dark:text-gray-300 font-mono">
                {{ $hostingOrder->provision_log }}
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Aksi</h3>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('hosting-orders.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                Kembali
            </a>

            @if(!in_array($hostingOrder->status, ['active', 'provisioning']) && !$hostingOrder->hosting_account_id)
                <form method="POST" action="{{ route('hosting-orders.provision', $hostingOrder) }}"
                      onsubmit="return confirm('Provision hosting order ini sekarang?')">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm">
                        Provision
                    </button>
                </form>
            @endif

            @if(!in_array($hostingOrder->status, ['active', 'cancelled']))
                <form method="POST" action="{{ route('hosting-orders.cancel', $hostingOrder) }}"
                      onsubmit="return confirm('Cancel hosting order ini?')">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white text-sm">
                        Cancel
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('hosting-orders.update-status', $hostingOrder) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="pending">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm">
                    Set Pending
                </button>
            </form>

            <form method="POST" action="{{ route('hosting-orders.update-status', $hostingOrder) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="paid">
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm">
                    Set Paid
                </button>
            </form>

            <form method="POST" action="{{ route('hosting-orders.update-status', $hostingOrder) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="active">
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm">
                    Set Active
                </button>
            </form>

            <form method="POST" action="{{ route('hosting-orders.update-status', $hostingOrder) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="failed">
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm">
                    Set Failed
                </button>
            </form>

            @if(!in_array($hostingOrder->status, ['active', 'provisioning']))
                <form method="POST" action="{{ route('hosting-orders.destroy', $hostingOrder) }}"
                      onsubmit="return confirm('Yakin hapus hosting order ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-white text-sm">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
