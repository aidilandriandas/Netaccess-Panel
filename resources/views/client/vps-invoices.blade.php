@extends('layouts.client')
@section('title', 'VPS Invoices: ' . $vpsService->hostname)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Invoices - {{ $vpsService->hostname }}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Daftar invoice terkait VPS ini.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="px-4 py-3">Invoice #</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Due Date</th><th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoices as $inv)
                <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 font-mono text-xs text-gray-800 dark:text-gray-200">
                        <a href="{{ route('client.invoices.show', $inv) }}" class="text-blue-600 hover:text-blue-800">{{ $inv->invoice_number }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $inv->due_date?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3"><x-status-badge :status="$inv->status" /></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada invoice untuk VPS ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('client.vps-services.show', $vpsService) }}" class="inline-flex bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-1.5 rounded-lg text-sm transition-colors">Kembali ke Detail VPS</a>
</div>
@endsection
