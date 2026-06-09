@extends('layouts.client')
@section('title', 'Invoices')

@section('content')
@php
    $invoiceRows = $invoices ?? collect();
    $isPaginator = $invoiceRows instanceof \Illuminate\Pagination\AbstractPaginator;
    $invoiceItems = $isPaginator ? $invoiceRows->getCollection() : collect($invoiceRows);

    $totalInvoices = $invoiceItems->count();

    $unpaidCount = $invoiceItems->filter(function ($invoice) {
        return in_array($invoice->status ?? '', ['unpaid', 'pending']);
    })->count();

    $paidCount = $invoiceItems->where('status', 'paid')->count();

    $overdueCount = $invoiceItems->filter(function ($invoice) {
        return ($invoice->status ?? '') !== 'paid'
            && $invoice->due_date
            && $invoice->due_date->lt(now());
    })->count();

    $totalUnpaidAmount = $invoiceItems->filter(function ($invoice) {
        return ($invoice->status ?? '') !== 'paid';
    })->sum(function ($invoice) {
        return $invoice->total ?? $invoice->amount ?? 0;
    });
@endphp

<style>
.invoice-wrap{display:flex;flex-direction:column;gap:24px}
.invoice-hero{border-radius:26px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(234,179,8,.22),transparent 34%),radial-gradient(circle at bottom left,rgba(37,99,235,.22),transparent 35%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(30,27,75,.84));box-shadow:0 24px 60px rgba(0,0,0,.25)}
.invoice-hero-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:24px;align-items:center}
.invoice-pill{display:inline-flex;padding:7px 14px;border-radius:999px;border:1px solid rgba(250,204,21,.32);color:#fde68a;background:rgba(234,179,8,.12);font-size:12px;font-weight:900;margin-bottom:16px}
.invoice-title{color:white;font-size:34px;line-height:1.1;font-weight:950;margin:0}
.invoice-desc{color:#cbd5e1;font-size:15px;line-height:1.7;margin-top:12px;max-width:720px}
.invoice-paybox{border-radius:22px;padding:22px;background:rgba(2,6,23,.50);border:1px solid rgba(255,255,255,.10)}
.invoice-label{color:#94a3b8;font-size:13px;font-weight:800}
.invoice-amount{color:white;font-size:30px;font-weight:950;margin-top:8px}
.invoice-note-text{color:#cbd5e1;font-size:13px;line-height:1.6;margin-top:10px}
.invoice-wa{display:inline-flex;margin-top:16px;text-decoration:none;color:white;background:#16a34a;padding:12px 16px;border-radius:14px;font-size:14px;font-weight:950}
.invoice-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.invoice-stat{border-radius:22px;padding:22px;min-height:138px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.78);box-shadow:0 20px 45px rgba(0,0,0,.18)}
.invoice-stat.total{background:linear-gradient(135deg,rgba(37,99,235,.22),rgba(15,23,42,.84))}
.invoice-stat.unpaid{background:linear-gradient(135deg,rgba(234,179,8,.20),rgba(15,23,42,.84))}
.invoice-stat.paid{background:linear-gradient(135deg,rgba(34,197,94,.18),rgba(15,23,42,.84))}
.invoice-stat.overdue{background:linear-gradient(135deg,rgba(239,68,68,.18),rgba(15,23,42,.84))}
.invoice-stat-icon{font-size:27px;margin-bottom:14px}
.invoice-stat-title{color:#cbd5e1;font-size:14px;font-weight:900}
.invoice-stat-number{color:white;font-size:34px;font-weight:950;margin-top:8px}
.invoice-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.80);box-shadow:0 20px 45px rgba(0,0,0,.18);overflow:hidden}
.invoice-card-head{padding:22px;display:flex;align-items:center;justify-content:space-between;gap:14px;border-bottom:1px solid rgba(148,163,184,.14)}
.invoice-card-title{color:white;font-size:20px;font-weight:950}
.invoice-card-sub{color:#94a3b8;font-size:13px;margin-top:5px}
.invoice-table-wrap{overflow-x:auto}
.invoice-table{width:100%;border-collapse:collapse}
.invoice-table th,.invoice-table td{padding:16px 22px;text-align:left;border-bottom:1px solid rgba(148,163,184,.12)}
.invoice-table th{color:#94a3b8;font-size:12px;text-transform:uppercase;letter-spacing:.06em;font-weight:950}
.invoice-table td{color:#cbd5e1;font-size:14px}
.invoice-number{color:#60a5fa;font-weight:950;text-decoration:none}
.invoice-total{color:white;font-weight:950}
.invoice-badge{display:inline-flex;padding:7px 11px;border-radius:999px;font-size:12px;font-weight:950;text-transform:uppercase}
.invoice-badge.paid{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.invoice-badge.unpaid,.invoice-badge.pending{color:#fde047;background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.24)}
.invoice-badge.cancelled,.invoice-badge.failed,.invoice-badge.overdue{color:#fca5a5;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.24)}
.invoice-action{display:inline-flex;text-decoration:none;border-radius:12px;padding:9px 12px;font-size:12px;font-weight:950;color:white;background:rgba(37,99,235,.90)}
.invoice-empty{text-align:center;padding:50px 24px;color:#94a3b8}
.invoice-empty-icon{font-size:46px;margin-bottom:14px}
.invoice-empty-title{color:white;font-size:22px;font-weight:950}
@media(max-width:1100px){.invoice-hero-grid{grid-template-columns:1fr}.invoice-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){.invoice-title{font-size:28px}.invoice-stats{grid-template-columns:1fr}.invoice-table th:nth-child(2),.invoice-table td:nth-child(2){display:none}}
</style>

<div class="invoice-wrap">

    <section class="invoice-hero">
        <div class="invoice-hero-grid">
            <div>
                <div class="invoice-pill">🧾 Billing Center</div>
                <h1 class="invoice-title">Invoices</h1>
                <p class="invoice-desc">
                    Pantau semua tagihan layanan kamu di sini. Cek status pembayaran,
                    jatuh tempo, dan total invoice yang masih perlu dibayar.
                </p>
            </div>

            <div class="invoice-paybox">
                <div class="invoice-label">Total Belum Dibayar</div>
                <div class="invoice-amount">
                    Rp {{ number_format($totalUnpaidAmount, 0, ',', '.') }}
                </div>
                <div class="invoice-note-text">
                    Jika sudah melakukan pembayaran tapi status belum berubah, hubungi admin untuk konfirmasi.
                </div>
                <a href="{{ route('client.tickets.create', ['category' => 'billing', 'subject' => 'Konfirmasi pembayaran invoice']) }}" class="invoice-wa">
                    Konfirmasi Pembayaran via Tiket
                </a>
            </div>
        </div>
    </section>

    <section class="invoice-stats">
        <div class="invoice-stat total">
            <div class="invoice-stat-icon">📄</div>
            <div class="invoice-stat-title">Total Invoice</div>
            <div class="invoice-stat-number">{{ $totalInvoices }}</div>
        </div>

        <div class="invoice-stat unpaid">
            <div class="invoice-stat-icon">🟡</div>
            <div class="invoice-stat-title">Belum Bayar</div>
            <div class="invoice-stat-number">{{ $unpaidCount }}</div>
        </div>

        <div class="invoice-stat paid">
            <div class="invoice-stat-icon">✅</div>
            <div class="invoice-stat-title">Paid</div>
            <div class="invoice-stat-number">{{ $paidCount }}</div>
        </div>

        <div class="invoice-stat overdue">
            <div class="invoice-stat-icon">⚠️</div>
            <div class="invoice-stat-title">Overdue</div>
            <div class="invoice-stat-number">{{ $overdueCount }}</div>
        </div>
    </section>

    <section class="invoice-card">
        <div class="invoice-card-head">
            <div>
                <div class="invoice-card-title">Daftar Invoice</div>
                <div class="invoice-card-sub">Riwayat invoice dan status pembayaran layanan kamu.</div>
            </div>
        </div>

        @if($invoiceItems->count())
            <div class="invoice-table-wrap">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Layanan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Jatuh Tempo</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoiceItems as $invoice)
                            @php
                                $status = $invoice->status ?? '-';

                                if ($status !== 'paid' && $invoice->due_date && $invoice->due_date->lt(now())) {
                                    $statusClass = 'overdue';
                                } else {
                                    $statusClass = $status;
                                }

                                $serviceName = $invoice->package?->name
                                    ?? $invoice->service_name
                                    ?? $invoice->description
                                    ?? 'Layanan';
                            @endphp

                            <tr>
                                <td>
                                    <a href="{{ route('client.invoices.show', $invoice) }}" class="invoice-number">
                                        {{ $invoice->invoice_number ?? $invoice->number ?? 'INV-'.$invoice->id }}
                                    </a>
                                </td>
                                <td>{{ $serviceName }}</td>
                                <td>
                                    <div class="invoice-total">
                                        Rp {{ number_format($invoice->total ?? $invoice->amount ?? 0, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="invoice-badge {{ $statusClass }}">
                                        {{ $statusClass === 'overdue' ? 'Overdue' : ucfirst($status) }}
                                    </span>
                                </td>
                                <td>{{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('client.invoices.show', $invoice) }}" class="invoice-action">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($isPaginator)
                <div style="padding:18px 22px;border-top:1px solid rgba(148,163,184,.14);">
                    {{ $invoiceRows->links() }}
                </div>
            @endif
        @else
            <div class="invoice-empty">
                <div class="invoice-empty-icon">🧾</div>
                <div class="invoice-empty-title">Belum ada invoice</div>
                <div style="margin-top:8px;">Invoice layanan kamu akan muncul di sini.</div>
            </div>
        @endif
    </section>

</div>
@endsection
