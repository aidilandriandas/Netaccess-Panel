@extends('layouts.app')
@section('title', 'Detail Customer')

@section('content')
<div class="space-y-6">
    {{-- Customer Info --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $customer->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $customer->company_name ?? '-' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-status-badge :status="$customer->status" />
                <a href="{{ route('customers.edit', $customer) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm">Edit</a>
                {{-- WhatsApp buttons --}}
                @if($customer->phone)
                <div x-data class="relative inline-block">
                    <button @click="$refs.waMenu.classList.toggle('hidden')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm">WhatsApp</button>
                    <div x-ref="waMenu" class="hidden absolute right-0 mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg py-1 z-10 w-40">
                        @foreach(['h7' => 'Reminder H-7', 'h3' => 'Reminder H-3', 'h1' => 'Reminder H-1', 'expired' => 'Reminder Expired'] as $type => $label)
                        <button @click="fetch('{{ route('whatsapp.customer-reminder', [$customer, $type]) }}').then(r=>r.json()).then(d=>window.open(d.url,'_blank'))"
                                class="block w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500 dark:text-gray-400">Phone:</span> <span class="text-gray-800 dark:text-gray-200">{{ $customer->phone ?? '-' }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Email:</span> <span class="text-gray-800 dark:text-gray-200">{{ $customer->email ?? '-' }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">Tipe:</span> <span class="text-gray-800 dark:text-gray-200">{{ ucfirst(str_replace('_', '/', $customer->customer_type)) }}</span></div>
            <div class="md:col-span-3"><span class="text-gray-500 dark:text-gray-400">Alamat:</span> <span class="text-gray-800 dark:text-gray-200">{{ $customer->address ?? '-' }}</span></div>
            @if($customer->notes)<div class="md:col-span-3"><span class="text-gray-500 dark:text-gray-400">Catatan:</span> <span class="text-gray-800 dark:text-gray-200">{{ $customer->notes }}</span></div>@endif
        </div>
    </div>

    {{-- VPN Users --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">VPN Users ({{ $customer->vpnUsers->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            @if($customer->vpnUsers->isEmpty())
                <p class="p-4 text-sm text-gray-500 dark:text-gray-400">Belum ada VPN user.</p>
            @else
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <th class="px-4 py-2">Username</th><th class="px-4 py-2">Paket</th><th class="px-4 py-2">IP</th><th class="px-4 py-2">Expired</th><th class="px-4 py-2">Status</th>
                </tr></thead>
                <tbody>
                @foreach($customer->vpnUsers as $vu)
                <tr class="border-b border-gray-50 dark:border-gray-700/50">
                    <td class="px-4 py-2"><a href="{{ route('vpn-users.show', $vu) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $vu->username }}</a></td>
                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $vu->package->name }}</td>
                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $vu->assigned_ip }}</td>
                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $vu->expired_at?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-2"><x-status-badge :status="$vu->status" /></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Invoices --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Invoices ({{ $customer->invoices->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            @if($customer->invoices->isEmpty())
                <p class="p-4 text-sm text-gray-500 dark:text-gray-400">Belum ada invoice.</p>
            @else
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                    <th class="px-4 py-2">No Invoice</th><th class="px-4 py-2">Paket</th><th class="px-4 py-2">Total</th><th class="px-4 py-2">Due Date</th><th class="px-4 py-2">Status</th>
                </tr></thead>
                <tbody>
                @foreach($customer->invoices as $inv)
                <tr class="border-b border-gray-50 dark:border-gray-700/50">
                    <td class="px-4 py-2"><a href="{{ route('invoices.show', $inv) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $inv->invoice_number }}</a></td>
                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $inv->package->name }}</td>
                    <td class="px-4 py-2 text-gray-800 dark:text-gray-200">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $inv->due_date->format('d M Y') }}</td>
                    <td class="px-4 py-2"><x-status-badge :status="$inv->status" /></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
