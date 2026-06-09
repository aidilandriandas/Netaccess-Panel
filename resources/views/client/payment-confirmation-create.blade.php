@extends('layouts.client')

@section('title', 'Konfirmasi Pembayaran')

@section('content')
@php
    $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

<div class="max-w-3xl mx-auto space-y-6">
    <div class="rounded-2xl border border-slate-700/60 bg-slate-900/70 p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-white">Konfirmasi Pembayaran</h1>
                <p class="mt-1 text-sm text-slate-400">
                    Upload bukti transfer untuk invoice ini.
                </p>
            </div>

            <div class="text-right">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Invoice</div>
                <div class="text-lg font-black text-white">
                    {{ $invoice->invoice_number ?? 'INV-'.$invoice->id }}
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mt-5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-bold text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-700/60 bg-slate-950/40 p-4">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Total</div>
                <div class="mt-1 text-xl font-black text-white">{{ $money($invoice->total ?? 0) }}</div>
            </div>

            <div class="rounded-xl border border-slate-700/60 bg-slate-950/40 p-4">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Status</div>
                <div class="mt-1 text-xl font-black text-yellow-300">{{ strtoupper($invoice->status ?? 'unpaid') }}</div>
            </div>

            <div class="rounded-xl border border-slate-700/60 bg-slate-950/40 p-4">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Due Date</div>
                <div class="mt-1 text-xl font-black text-white">
                    {{ !empty($invoice->due_date) ? date('d M Y', strtotime($invoice->due_date)) : '-' }}
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('client.payment-confirmations.store', $invoice->id) }}" enctype="multipart/form-data"
          class="rounded-2xl border border-slate-700/60 bg-slate-900/70 p-6 shadow-xl">
        @csrf

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">Nominal Transfer</label>
                <input type="number" name="amount" value="{{ old('amount', (int)($invoice->total ?? 0)) }}"
                       class="w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-white">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">Bank / Metode Pembayaran</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="BCA / Mandiri / QRIS / DANA"
                       class="w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-white">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">Nama Pengirim</label>
                <input type="text" name="account_name" value="{{ old('account_name') }}" placeholder="Nama pemilik rekening"
                       class="w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-white">
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-300">Bukti Transfer</label>
                <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf,.webp"
                       class="w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-white">
                <p class="mt-1 text-xs text-slate-500">Format: jpg, png, webp, atau pdf. Maks 4MB.</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-bold text-slate-300">Catatan</label>
                <textarea name="notes" rows="4" placeholder="Contoh: transfer dari BCA a/n Aidil jam 10:30"
                          class="w-full rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-3 text-white">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            @if(Route::has('client.invoices.show'))
                <a href="{{ route('client.invoices.show', $invoice->id) }}"
                   class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-black text-slate-300 hover:bg-slate-800">
                    Kembali
                </a>
            @endif

            <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 px-5 py-3 text-sm font-black text-white shadow-lg hover:opacity-90">
                Kirim Konfirmasi
            </button>
        </div>
    </form>
</div>
@endsection
