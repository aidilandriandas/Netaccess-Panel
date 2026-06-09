@extends('layouts.app')
@section('title', 'Detail Invoice')

@section('content')







<div style="margin-bottom:14px;display:flex;justify-content:flex-end;">
    @include('components.client-360-button', [
    'userId' => $invoice->user_id ?? $invoice->customer?->user_id ?? null,
    'customerId' => $invoice->customer_id ?? $invoice->customer?->id ?? null,
])
</div>

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
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $invoice->invoice_number }}
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Detail invoice pelanggan
                </p>
            </div>

            <x-status-badge :status="$invoice->status" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Customer:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ $invoice->customer?->name ?? '-' }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Paket/Layanan:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ $invoice->package?->name ?? 'Layanan Non-VPN' }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Subtotal:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Setup Fee:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($invoice->setup_fee ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Discount:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($invoice->discount ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Total:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($invoice->total ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Due Date:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                </span>
            </div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">Paid At:</span>
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ $invoice->paid_at?->format('d M Y H:i') ?? '-' }}
                </span>
            </div>

            @if($invoice->vpnUser)
                <div>
                    <span class="text-gray-500 dark:text-gray-400">VPN User:</span>
                    <a href="{{ route('vpn-users.show', $invoice->vpnUser) }}"
                       class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $invoice->vpnUser->username }}
                    </a>
                </div>
            @endif
        </div>

        @if($invoice->notes)
            <div class="mt-6 bg-gray-100 dark:bg-gray-700/50 rounded-lg p-4 text-sm text-gray-700 dark:text-gray-300">
                <div class="font-semibold mb-1">Notes:</div>
                {{ $invoice->notes }}
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Aksi</h3>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('invoices.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                Kembali
            </a>

            @if($invoice->status !== 'paid')
                <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}"
                      onsubmit="return confirm('Tandai invoice {{ $invoice->invoice_number }} sebagai paid?')">
                    @csrf

                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm">
                        Mark Paid
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                  onsubmit="return confirm('Yakin hapus invoice {{ $invoice->invoice_number }}?')">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
