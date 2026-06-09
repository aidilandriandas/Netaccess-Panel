@extends('layouts.client')
@section('title', 'Dashboard')

@section('content')
@php
    $vpnActive = $vpnActive ?? $vpnCount ?? $activeVpn ?? 0;
    $hostingActive = $hostingActive ?? $hostingCount ?? $activeHosting ?? 0;
    $vpsActive = $vpsActive ?? $vpsCount ?? $activeVps ?? 0;
    $unpaidInvoices = $unpaidInvoices ?? $invoiceUnpaid ?? $unpaidCount ?? 0;

    $latestInvoices = $latestInvoices ?? $invoices ?? collect();

    $vpnServer = \App\Models\Setting::get('mikrotik_public_ip')
        ?? \App\Models\Setting::get('mikrotik_host')
        ?? '-';
@endphp

<style>
.client-wrap {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.client-hero {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 28px;
    border: 1px solid rgba(148, 163, 184, .18);
    background:
        radial-gradient(circle at top right, rgba(139, 92, 246, .28), transparent 35%),
        radial-gradient(circle at bottom left, rgba(59, 130, 246, .22), transparent 35%),
        linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(30, 27, 75, .82));
    box-shadow: 0 24px 60px rgba(0,0,0,.24);
}

.client-hero-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
    gap: 24px;
    align-items: center;
}

.client-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: 999px;
    border: 1px solid rgba(96, 165, 250, .35);
    color: #bfdbfe;
    background: rgba(37, 99, 235, .12);
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 16px;
}

.client-title {
    font-size: 34px;
    line-height: 1.1;
    font-weight: 900;
    color: white;
    margin: 0;
}

.client-desc {
    margin-top: 12px;
    max-width: 680px;
    color: #cbd5e1;
    font-size: 15px;
    line-height: 1.7;
}

.client-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
}

.client-btn-primary,
.client-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 16px;
    border-radius: 14px;
    font-size: 14px;
    font-weight: 800;
    text-decoration: none;
}

.client-btn-primary {
    color: #0f172a;
    background: white;
}

.client-btn-secondary {
    color: white;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.18);
}

.client-server-card {
    border-radius: 20px;
    padding: 22px;
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(2, 6, 23, .46);
}

.client-server-top {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: center;
}

.client-label {
    color: #94a3b8;
    font-size: 13px;
    font-weight: 700;
}

.client-server-ip {
    color: white;
    font-size: 26px;
    font-weight: 900;
    margin-top: 6px;
}

.client-copy {
    border: none;
    background: #2563eb;
    color: white;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 800;
    cursor: pointer;
}

.client-mini-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 18px;
}

.client-mini {
    border-radius: 14px;
    padding: 14px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.10);
}

.client-mini strong {
    display: block;
    color: white;
    margin-top: 4px;
}

.client-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.client-stat {
    min-height: 160px;
    border-radius: 22px;
    padding: 22px;
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(15, 23, 42, .78);
    box-shadow: 0 20px 45px rgba(0,0,0,.18);
}

.client-stat.purple { background: linear-gradient(135deg, rgba(124,58,237,.22), rgba(15,23,42,.82)); }
.client-stat.blue { background: linear-gradient(135deg, rgba(37,99,235,.22), rgba(15,23,42,.82)); }
.client-stat.green { background: linear-gradient(135deg, rgba(16,185,129,.20), rgba(15,23,42,.82)); }
.client-stat.yellow { background: linear-gradient(135deg, rgba(234,179,8,.20), rgba(15,23,42,.82)); }

.client-stat-icon {
    font-size: 28px;
    margin-bottom: 16px;
}

.client-stat-title {
    color: #cbd5e1;
    font-weight: 800;
    font-size: 14px;
}

.client-stat-number {
    color: white;
    font-weight: 900;
    font-size: 36px;
    margin-top: 8px;
}

.client-stat-desc {
    color: #94a3b8;
    font-size: 13px;
    margin-top: 10px;
}

.client-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.65fr) minmax(320px, .75fr);
    gap: 20px;
}

.client-card {
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(15, 23, 42, .78);
    overflow: hidden;
    box-shadow: 0 20px 45px rgba(0,0,0,.18);
}

.client-card-head {
    padding: 20px 22px;
    border-bottom: 1px solid rgba(148, 163, 184, .14);
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
}

.client-card-title {
    color: white;
    font-size: 18px;
    font-weight: 900;
}

.client-card-sub {
    color: #94a3b8;
    font-size: 13px;
    margin-top: 4px;
}

.client-service-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    padding: 22px;
}

.client-service {
    display: block;
    min-height: 150px;
    padding: 20px;
    border-radius: 18px;
    text-decoration: none;
    border: 1px solid rgba(148, 163, 184, .16);
    background: rgba(30, 41, 59, .50);
    transition: .18s ease;
}

.client-service:hover {
    transform: translateY(-3px);
    border-color: rgba(96, 165, 250, .5);
}

.client-service-icon {
    font-size: 30px;
    margin-bottom: 16px;
}

.client-service-title {
    color: white;
    font-weight: 900;
}

.client-service-desc {
    color: #94a3b8;
    font-size: 13px;
    line-height: 1.5;
    margin-top: 7px;
}

.client-table {
    width: 100%;
    border-collapse: collapse;
}

.client-table th,
.client-table td {
    padding: 15px 22px;
    text-align: left;
    border-bottom: 1px solid rgba(148, 163, 184, .12);
}

.client-table th {
    color: #94a3b8;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .06em;
}

.client-table td {
    color: #cbd5e1;
    font-size: 14px;
}

.client-badge {
    display: inline-flex;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
}

.client-badge.paid {
    color: #86efac;
    background: rgba(34,197,94,.14);
    border: 1px solid rgba(34,197,94,.20);
}

.client-badge.unpaid {
    color: #fde047;
    background: rgba(234,179,8,.14);
    border: 1px solid rgba(234,179,8,.20);
}

.client-side {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.client-support {
    padding: 22px;
    border-radius: 22px;
    border: 1px solid rgba(34, 211, 238, .22);
    background: linear-gradient(135deg, rgba(6,182,212,.18), rgba(15,23,42,.84));
}

.client-support h3,
.client-tips h3,
.client-status h3 {
    color: white;
    font-size: 18px;
    font-weight: 900;
    margin: 0;
}

.client-support p,
.client-tips p,
.client-status p {
    color: #cbd5e1;
    font-size: 14px;
    line-height: 1.6;
    margin-top: 10px;
}

.client-wa {
    display: block;
    margin-top: 16px;
    text-align: center;
    padding: 12px 14px;
    border-radius: 14px;
    color: white;
    background: #16a34a;
    font-weight: 900;
    text-decoration: none;
}

.client-tips,
.client-status {
    padding: 22px;
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(15, 23, 42, .78);
}

.client-tip-item {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

.client-tip-num {
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(59,130,246,.16);
    color: #93c5fd;
    font-weight: 900;
}

.client-tip-title {
    color: white;
    font-weight: 900;
}

.client-tip-desc {
    color: #94a3b8;
    font-size: 13px;
    margin-top: 3px;
}

@media (max-width: 1100px) {
    .client-hero-grid,
    .client-main-grid {
        grid-template-columns: 1fr;
    }

    .client-stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .client-hero {
        padding: 22px;
    }

    .client-title {
        font-size: 28px;
    }

    .client-stat-grid,
    .client-service-grid,
    .client-mini-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="client-wrap">

    <section class="client-hero">
        <div class="client-hero-grid">
            <div>
                <div class="client-pill">NetAccess Client Portal</div>

                <h1 class="client-title">
                    Halo, {{ auth()->user()->name ?? 'Client' }} 👋
                </h1>

                <p class="client-desc">
                    Kelola layanan VPN, Hosting, VPS, dan invoice kamu dalam satu dashboard.
                    Semua informasi koneksi dan status layanan bisa dicek di sini.
                </p>

                <div class="client-actions">
                    <a href="{{ route('client.vpn-services') }}" class="client-btn-primary">
                        Cek VPN Saya
                    </a>

                    <a href="{{ route('client.invoices') }}" class="client-btn-secondary">
                        Lihat Invoice
                    </a>
                </div>
            </div>

            <div class="client-server-card">
                <div class="client-server-top">
                    <div>
                        <div class="client-label">Ringkasan Akun</div>
                        <div class="client-server-ip">Client Area</div>
                    </div>

                    <span style="display:inline-flex;align-items:center;gap:8px;background:rgba(34,197,94,.14);border:1px solid rgba(34,197,94,.22);color:#86efac;padding:10px 14px;border-radius:999px;font-size:13px;font-weight:900;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;"></span>
                        Active
                    </span>
                </div>

                <div class="client-mini-grid">
                    <div class="client-mini">
                        <div class="client-label">Total Layanan</div>
                        <strong>{{ $vpnActive + $hostingActive + $vpsActive }}</strong>
                    </div>

                    <div class="client-mini">
                        <div class="client-label">Invoice Belum Bayar</div>
                        <strong style="color:#fde047;">{{ $unpaidInvoices }}</strong>
                    </div>
                </div>

                <div style="margin-top:16px;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
                    <a href="{{ route('client.vpn-services') }}" style="text-align:center;text-decoration:none;background:rgba(124,58,237,.14);border:1px solid rgba(124,58,237,.22);color:#ddd6fe;padding:12px 8px;border-radius:14px;font-size:12px;font-weight:900;">
                        VPN
                    </a>
                    <a href="{{ route('client.hosting-services') }}" style="text-align:center;text-decoration:none;background:rgba(37,99,235,.14);border:1px solid rgba(37,99,235,.22);color:#bfdbfe;padding:12px 8px;border-radius:14px;font-size:12px;font-weight:900;">
                        Hosting
                    </a>
                    <a href="{{ route('client.vps-services') }}" style="text-align:center;text-decoration:none;background:rgba(16,185,129,.14);border:1px solid rgba(16,185,129,.22);color:#bbf7d0;padding:12px 8px;border-radius:14px;font-size:12px;font-weight:900;">
                        VPS
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="client-stat-grid">
        <div class="client-stat purple">
            <div class="client-stat-icon">🔐</div>
            <div class="client-stat-title">VPN Aktif</div>
            <div class="client-stat-number">{{ $vpnActive }}</div>
            <div class="client-stat-desc">Layanan VPN yang sedang aktif.</div>
        </div>

        <div class="client-stat blue">
            <div class="client-stat-icon">🌐</div>
            <div class="client-stat-title">Hosting Aktif</div>
            <div class="client-stat-number">{{ $hostingActive }}</div>
            <div class="client-stat-desc">Hosting yang sudah berjalan.</div>
        </div>

        <div class="client-stat green">
            <div class="client-stat-icon">🖥️</div>
            <div class="client-stat-title">VPS Aktif</div>
            <div class="client-stat-number">{{ $vpsActive }}</div>
            <div class="client-stat-desc">Server VPS aktif milik kamu.</div>
        </div>

        <div class="client-stat yellow">
            <div class="client-stat-icon">🧾</div>
            <div class="client-stat-title">Invoice Belum Bayar</div>
            <div class="client-stat-number">{{ $unpaidInvoices }}</div>
            <div class="client-stat-desc">Invoice yang masih perlu dibayar.</div>
        </div>
    </section>

    <section class="client-main-grid">
        <div class="client-card">
            <div class="client-card-head">
                <div>
                    <div class="client-card-title">Layanan Saya</div>
                    <div class="client-card-sub">Akses cepat ke layanan aktif kamu.</div>
                </div>
            </div>

            <div class="client-service-grid">
                <a href="{{ route('client.vpn-services') }}" class="client-service">
                    <div class="client-service-icon">🔐</div>
                    <div class="client-service-title">My VPN</div>
                    <div class="client-service-desc">
                        Detail akun VPN dan panduan koneksi.
                    </div>
                </a>

                <a href="{{ route('client.hosting-services') }}" class="client-service">
                    <div class="client-service-icon">🌐</div>
                    <div class="client-service-title">My Hosting</div>
                    <div class="client-service-desc">
                        Cek hosting, domain, dan status akun.
                    </div>
                </a>

                <a href="{{ route('client.vps-services') }}" class="client-service">
                    <div class="client-service-icon">🖥️</div>
                    <div class="client-service-title">My VPS</div>
                    <div class="client-service-desc">
                        Informasi VPS dan akses server.
                    </div>
                </a>
            </div>

            <div class="client-card-head">
                <div>
                    <div class="client-card-title">Invoice Terbaru</div>
                    <div class="client-card-sub">Pantau tagihan dan jatuh tempo.</div>
                </div>

                <a href="{{ route('client.invoices') }}" style="color:#60a5fa;font-weight:800;font-size:14px;">
                    Lihat Semua
                </a>
            </div>

            <div style="overflow-x:auto;">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Jatuh Tempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestInvoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('client.invoices') }}" style="color:#60a5fa;font-weight:900;">
                                        {{ $invoice->invoice_number ?? $invoice->number ?? 'INV-'.$invoice->id }}
                                    </a>
                                </td>
                                <td style="color:white;font-weight:900;">
                                    Rp {{ number_format($invoice->total ?? $invoice->amount ?? 0, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="client-badge {{ ($invoice->status ?? '') === 'paid' ? 'paid' : 'unpaid' }}">
                                        {{ ucfirst($invoice->status ?? '-') }}
                                    </span>
                                </td>
                                <td>
                                    {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center;padding:34px;color:#94a3b8;">
                                    Belum ada invoice terbaru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="client-side">
            <div class="client-support">
                <div style="font-size:34px;margin-bottom:12px;">💬</div>
                <h3>Butuh Bantuan?</h3>
                <p>
                    Kalau VPN tidak bisa connect, hosting bermasalah, atau invoice belum update,
                    hubungi admin/support.
                </p>

                <a href="https://wa.me/" target="_blank" class="client-wa">
                    Hubungi WhatsApp
                </a>
            </div>

            <div class="client-tips">
                <h3>Tips Koneksi VPN</h3>

                <div class="client-tip-item">
                    <div class="client-tip-num">1</div>
                    <div>
                        <div class="client-tip-title">Pakai Server VPN</div>
                        <div class="client-tip-desc">Gunakan IP publik server, bukan IP private VPN.</div>
                    </div>
                </div>

                <div class="client-tip-item">
                    <div class="client-tip-num">2</div>
                    <div>
                        <div class="client-tip-title">Tipe L2TP/IPSec</div>
                        <div class="client-tip-desc">Pastikan tipe VPN di device sesuai.</div>
                    </div>
                </div>

                <div class="client-tip-item">
                    <div class="client-tip-num">3</div>
                    <div>
                        <div class="client-tip-title">Cek Expired</div>
                        <div class="client-tip-desc">VPN expired tidak bisa digunakan.</div>
                    </div>
                </div>
            </div>

            <div class="client-status">
                <h3>Network Status</h3>
                <p>
                    <span style="display:inline-block;width:10px;height:10px;background:#22c55e;border-radius:50%;margin-right:8px;"></span>
                    Semua layanan utama berjalan normal.
                </p>
            </div>
        </aside>
    </section>

</div>

<script>
function copyText(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Berhasil dicopy');
    });
}
</script>
@endsection
