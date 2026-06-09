@extends('layouts.client')
@section('title', 'Detail Invoice')

@section('content')









@php
    $serviceName = $invoice->package?->name
        ?? $invoice->service_name
        ?? $invoice->description
        ?? 'Layanan';

    $status = $invoice->status ?? '-';

    if ($status !== 'paid' && $invoice->due_date && $invoice->due_date->lt(now())) {
        $statusClass = 'overdue';
        $statusText = 'Overdue';
    } else {
        $statusClass = $status;
        $statusText = ucfirst($status);
    }
@endphp

<style>
.inv-detail-wrap{display:flex;flex-direction:column;gap:24px}
.inv-detail-hero{border-radius:26px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(234,179,8,.22),transparent 34%),radial-gradient(circle at bottom left,rgba(37,99,235,.20),transparent 35%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(30,27,75,.84));box-shadow:0 24px 60px rgba(0,0,0,.25)}
.inv-top{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap}
.inv-title{color:white;font-size:32px;font-weight:950;margin:0}
.inv-sub{color:#94a3b8;margin-top:8px}
.inv-badge{display:inline-flex;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:950;text-transform:uppercase}
.inv-badge.paid{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.inv-badge.unpaid,.inv-badge.pending{color:#fde047;background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.24)}
.inv-badge.overdue,.inv-badge.failed,.inv-badge.cancelled{color:#fca5a5;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.24)}
.inv-grid{display:grid;grid-template-columns:1.4fr .8fr;gap:20px}
.inv-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.80);box-shadow:0 20px 45px rgba(0,0,0,.18);padding:24px}
.inv-card h2{color:white;font-size:20px;font-weight:950;margin:0 0 18px 0}
.inv-row{display:flex;justify-content:space-between;gap:18px;padding:14px 0;border-bottom:1px solid rgba(148,163,184,.12)}
.inv-row:last-child{border-bottom:0}
.inv-label{color:#94a3b8;font-weight:800;font-size:14px}
.inv-value{color:white;font-weight:900;text-align:right}
.inv-total{font-size:28px;color:white;font-weight:950}
.inv-btn{display:inline-flex;text-decoration:none;justify-content:center;border-radius:14px;padding:12px 16px;color:white;font-size:14px;font-weight:950;background:#16a34a;width:100%;margin-top:16px}
.inv-back{display:inline-flex;text-decoration:none;border-radius:14px;padding:12px 16px;color:#e2e8f0;font-size:14px;font-weight:950;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16)}
@media(max-width:900px){.inv-grid{grid-template-columns:1fr}.inv-title{font-size:26px}}
</style>

<div class="inv-detail-wrap">

    <section class="inv-detail-hero">
        <div class="inv-top">
            <div>
                <h1 class="inv-title">
                    {{ $invoice->invoice_number ?? $invoice->number ?? 'INV-'.$invoice->id }}
                </h1>
                <div class="inv-sub">Detail tagihan layanan kamu.</div>
            </div>

            <span class="inv-badge {{ $statusClass }}">
                {{ $statusText }}
            </span>
        </div>
    </section>

    <section class="inv-grid">
        <div class="inv-card">
            <h2>Informasi Invoice</h2>

            <div class="inv-row">
                <div class="inv-label">Layanan</div>
                <div class="inv-value">{{ $serviceName }}</div>
            </div>

            <div class="inv-row">
                <div class="inv-label">Customer</div>
                <div class="inv-value">{{ $invoice->customer?->name ?? auth()->user()->name ?? '-' }}</div>
            </div>

            <div class="inv-row">
                <div class="inv-label">Tanggal Invoice</div>
                <div class="inv-value">{{ $invoice->created_at?->format('d M Y') ?? '-' }}</div>
            </div>

            <div class="inv-row">
                <div class="inv-label">Jatuh Tempo</div>
                <div class="inv-value">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</div>
            </div>

            <div class="inv-row">
                <div class="inv-label">Status</div>
                <div class="inv-value">{{ $statusText }}</div>
            </div>
        </div>

        <div class="inv-card">
            <h2>Ringkasan Pembayaran</h2>

            <div class="inv-label">Total Tagihan</div>
            <div class="inv-total">
                Rp {{ number_format($invoice->total ?? $invoice->amount ?? 0, 0, ',', '.') }}
            </div>

@if(Route::has('client.payment-confirmations.create') && strtolower($invoice->status ?? '') !== 'paid')
    <a href="{{ route('client.payment-confirmations.create', $invoice->id) }}"
       class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-500">
        Konfirmasi Pembayaran
    </a>
@endif


            

            <a href="{{ route('client.invoices') }}" class="inv-back" style="margin-top:12px;width:100%;">
                Kembali ke Invoice
            </a>
        </div>
    </section>

</div>
@endsection
