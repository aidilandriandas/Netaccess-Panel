@extends('layouts.app')
@section('title', 'Buat VPN User L2TP')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Buat VPN User L2TP (Mikrotik)</h2>
        <form method="POST" action="{{ route('vpn-users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer *</label>
                <select name="customer_id" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Pilih Customer</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->company_name ? " ({$c->company_name})" : '' }}
                    </option>
                    @endforeach
                </select>
                @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paket VPN *</label>
                <select name="package_id" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Pilih Paket</option>
                    @foreach($packages as $p)
                    <option value="{{ $p->id }}" {{ old('package_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} - Rp {{ number_format($p->price, 0, ',', '.') }}/{{ $p->duration_days }} hari
                    </option>
                    @endforeach
                </select>
                @error('package_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-xs text-blue-700 dark:text-blue-300">
                <p class="font-medium mb-1">Otomatis dibuat:</p>
                <ul class="list-disc ml-4 space-y-0.5">
                    <li>Username & password L2TP (random, aman)</li>
                    <li>IP address dari pool Mikrotik</li>
                    <li>PPP Secret di Mikrotik via API</li>
                    <li>Invoice tagihan</li>
                </ul>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm transition-colors">
                    Buat VPN User
                </button>
                <a href="{{ route('vpn-users.index') }}" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-lg text-sm transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
