@extends('layouts.app')
@section('title', 'Client History')

@section('content')
@php
    $fmtDate = function ($value) {
        if (!$value) return '-';
        try {
            return \Carbon\Carbon::parse($value)->format('d M Y H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $fmtMoney = function ($value) {
        return 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
    };

    $totalServices = $vpnUsers->count() + $hostings->count() + $vpsServices->count();
    $openTickets = $tickets->where('status', 'open')->count();
    $unpaidInvoices = $invoices->filter(fn ($i) => ($i->status ?? '') !== 'paid')->count();
@endphp

<style>
.ch-wrap{display:flex;flex-direction:column;gap:24px}
.ch-hero{border-radius:28px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(37,99,235,.25),transparent 34%),radial-gradient(circle at bottom left,rgba(16,185,129,.20),transparent 36%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(30,41,59,.88));box-shadow:0 24px 60px rgba(0,0,0,.28)}
.ch-top{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap}
.ch-pill{display:inline-flex;padding:7px 14px;border-radius:999px;border:1px solid rgba(147,197,253,.32);color:#bfdbfe;background:rgba(37,99,235,.12);font-size:12px;font-weight:950;margin-bottom:14px}
.ch-title{color:white;font-size:34px;font-weight:950;margin:0}
.ch-sub{color:#cbd5e1;margin-top:10px;line-height:1.7}
.ch-btn{display:inline-flex;text-decoration:none;border-radius:14px;padding:12px 16px;color:#e2e8f0;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16);font-weight:950}
.ch-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.ch-stat{border-radius:22px;padding:22px;min-height:130px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.82);box-shadow:0 18px 42px rgba(0,0,0,.18)}
.ch-stat span{font-size:28px}
.ch-stat .label{color:#cbd5e1;font-size:14px;font-weight:900;margin-top:12px}
.ch-stat .num{color:white;font-size:34px;font-weight:950;margin-top:6px}
.ch-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.ch-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.82);box-shadow:0 18px 42px rgba(0,0,0,.18);overflow:hidden}
.ch-card-head{padding:20px 22px;border-bottom:1px solid rgba(148,163,184,.13)}
.ch-card-title{color:white;font-size:19px;font-weight:950}
.ch-card-sub{color:#94a3b8;font-size:13px;margin-top:4px}
.ch-table-wrap{overflow-x:auto}
.ch-table{width:100%;border-collapse:collapse}
.ch-table th,.ch-table td{padding:14px 18px;text-align:left;border-bottom:1px solid rgba(148,163,184,.12);font-size:13px}
.ch-table th{color:#94a3b8;text-transform:uppercase;font-size:11px;letter-spacing:.06em;font-weight:950}
.ch-table td{color:#cbd5e1}
.ch-link{color:#60a5fa;text-decoration:none;font-weight:950}
.ch-strong{color:white;font-weight:950}
.ch-badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:950;text-transform:uppercase}
.ch-badge.active,.ch-badge.paid,.ch-badge.answered{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.ch-badge.open,.ch-badge.pending,.ch-badge.unpaid{color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(37,99,235,.24)}
.ch-badge.closed,.ch-badge.suspended{color:#cbd5e1;background:rgba(100,116,139,.18);border:1px solid rgba(148,163,184,.20)}
.ch-empty{padding:26px;color:#94a3b8;text-align:center}

.ch-tabs{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:0}
.ch-tab-btn{border:none;cursor:pointer;border-radius:14px;padding:12px 15px;color:#cbd5e1;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.16);font-size:13px;font-weight:950}
.ch-tab-btn.active{color:white;background:linear-gradient(135deg,#2563eb,#16a34a);border-color:transparent}
.ch-tab-panel{display:none}
.ch-tab-panel.active{display:block}
.ch-full{grid-column:1/-1}

@media(max-width:1100px){.ch-grid{grid-template-columns:1fr}.ch-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:720px){.ch-title{font-size:28px}.ch-stats{grid-template-columns:1fr}}
</style>

<div class="ch-wrap">

    <section class="ch-hero">
        <div class="ch-top">
            <div>
                <div class="ch-pill">👤 Client 360</div>
                <h1 class="ch-title">{{ $user->name }}</h1>
                <div class="ch-sub">
                    {{ $user->email }}<br>
                    Semua riwayat client: layanan, invoice, order, tiket, dan email log.
                </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('admin.tickets.create') }}" class="ch-btn">+ Buat Tiket</a>
                <a href="{{ route('admin.tickets.index') }}" class="ch-btn">Kembali</a>
            </div>
        </div>
    </section>

    <section class="ch-stats">
        <div class="ch-stat">
            <span>🧩</span>
            <div class="label">Total Layanan</div>
            <div class="num">{{ $totalServices }}</div>
        </div>

        <div class="ch-stat">
            <span>🎫</span>
            <div class="label">Open Tickets</div>
            <div class="num">{{ $openTickets }}</div>
        </div>

        <div class="ch-stat">
            <span>🧾</span>
            <div class="label">Unpaid Invoice</div>
            <div class="num">{{ $unpaidInvoices }}</div>
        </div>

        <div class="ch-stat">
            <span>📦</span>
            <div class="label">Orders</div>
            <div class="num">{{ $orders->count() }}</div>
        </div>
    </section>


    <section class="ch-tabs">
        <button type="button" class="ch-tab-btn active" data-tab="overview">Overview</button>
        <button type="button" class="ch-tab-btn" data-tab="services">Services</button>
        <button type="button" class="ch-tab-btn" data-tab="invoices">Invoices</button>
        <button type="button" class="ch-tab-btn" data-tab="tickets">Tickets</button>
        <button type="button" class="ch-tab-btn" data-tab="orders">Orders</button>
        <button type="button" class="ch-tab-btn" data-tab="emails">Email Log</button>
    </section>

    <section class="ch-grid">

        <div class="ch-card ch-tab-panel active" data-panel="overview">
            <div class="ch-card-head">
                <div class="ch-card-title">Overview Services</div>
                <div class="ch-card-sub">VPN, Hosting, VPS milik client.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Service</th>
                            <th>IP/Domain</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vpnUsers as $vpn)
                            <tr>
                                <td>VPN</td>
                                <td class="ch-strong">{{ $vpn->username ?? 'VPN-'.$vpn->id }}</td>
                                <td>{{ $vpn->assigned_ip ?? $vpn->ip_address ?? '-' }}</td>
                                <td><span class="ch-badge {{ $vpn->status ?? 'active' }}">{{ $vpn->status ?? 'active' }}</span></td>
                            </tr>
                        @endforeach

                        @foreach($hostings as $hosting)
                            <tr>
                                <td>Hosting</td>
                                <td class="ch-strong">{{ $hosting->domain ?? $hosting->domain_name ?? $hosting->hostname ?? 'Hosting-'.$hosting->id }}</td>
                                <td>{{ $hosting->username ?? $hosting->cpanel_username ?? '-' }}</td>
                                <td><span class="ch-badge {{ $hosting->status ?? 'active' }}">{{ $hosting->status ?? 'active' }}</span></td>
                            </tr>
                        @endforeach

                        @foreach($vpsServices as $vps)
                            <tr>
                                <td>VPS</td>
                                <td class="ch-strong">{{ $vps->name ?? $vps->hostname ?? $vps->server_name ?? 'VPS-'.$vps->id }}</td>
                                <td>{{ $vps->ip_address ?? $vps->main_ip ?? $vps->public_ip ?? '-' }}</td>
                                <td><span class="ch-badge {{ $vps->status ?? 'active' }}">{{ $vps->status ?? 'active' }}</span></td>
                            </tr>
                        @endforeach

                        @if($totalServices === 0)
                            <tr><td colspan="4" class="ch-empty">Belum ada layanan.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ch-card ch-tab-panel active" data-panel="overview">
            <div class="ch-card-head">
                <div class="ch-card-title">Latest Tickets</div>
                <div class="ch-card-sub">Riwayat tiket client.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="ch-link">
                                        {{ $ticket->subject }}
                                    </a>
                                    <div style="color:#94a3b8;font-size:12px;">{{ $ticket->ticket_number }}</div>
                                </td>
                                <td>{{ $ticket->service_label ?? '-' }}</td>
                                <td><span class="ch-badge {{ $ticket->status }}">{{ $ticket->status }}</span></td>
                                <td>{{ $fmtDate($ticket->last_reply_at ?? $ticket->updated_at ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ch-empty">Belum ada tiket.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ch-card ch-tab-panel active" data-panel="overview">
            <div class="ch-card-head">
                <div class="ch-card-title">Latest Invoices</div>
                <div class="ch-card-sub">Riwayat invoice dan pembayaran.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ch-strong">{{ $invoice->invoice_number ?? $invoice->number ?? 'INV-'.$invoice->id }}</td>
                                <td>{{ $fmtMoney($invoice->total ?? $invoice->amount ?? 0) }}</td>
                                <td><span class="ch-badge {{ $invoice->status ?? 'unpaid' }}">{{ $invoice->status ?? 'unpaid' }}</span></td>
                                <td>{{ $fmtDate($invoice->due_date ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ch-empty">Belum ada invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ch-card ch-tab-panel active" data-panel="overview">
            <div class="ch-card-head">
                <div class="ch-card-title">Latest Orders</div>
                <div class="ch-card-sub">Riwayat order jika tabel orders tersedia.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ch-strong">#{{ $order->id }}</td>
                                <td>{{ $order->service_type ?? $order->type ?? $order->package_name ?? '-' }}</td>
                                <td><span class="ch-badge {{ $order->status ?? 'pending' }}">{{ $order->status ?? 'pending' }}</span></td>
                                <td>{{ $fmtDate($order->created_at ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ch-empty">Belum ada order / tabel orders belum ada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ch-card ch-tab-panel active ch-full" data-panel="overview">
            <div class="ch-card-head">
                <div class="ch-card-title">Latest Email Log</div>
                <div class="ch-card-sub">Akan tampil kalau nanti ada tabel email_logs.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emails as $email)
                            <tr>
                                <td class="ch-strong">{{ $email->subject ?? '-' }}</td>
                                <td>{{ $email->to ?? $email->email ?? $user->email }}</td>
                                <td><span class="ch-badge {{ $email->status ?? 'sent' }}">{{ $email->status ?? 'sent' }}</span></td>
                                <td>{{ $fmtDate($email->created_at ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ch-empty">Belum ada email log / tabel email_logs belum ada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>



    <section class="ch-grid">

        <div class="ch-card ch-tab-panel ch-full" data-panel="services">
            <div class="ch-card-head">
                <div class="ch-card-title">All Services</div>
                <div class="ch-card-sub">Semua layanan VPN, Hosting, dan VPS milik client.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Service</th>
                            <th>IP/Domain</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vpnUsers as $vpn)
                            <tr>
                                <td>VPN</td>
                                <td class="ch-strong">{{ $vpn->username ?? 'VPN-'.$vpn->id }}</td>
                                <td>{{ $vpn->assigned_ip ?? $vpn->ip_address ?? '-' }}</td>
                                <td><span class="ch-badge {{ $vpn->status ?? 'active' }}">{{ $vpn->status ?? 'active' }}</span></td>
                            </tr>
                        @endforeach

                        @foreach($hostings as $hosting)
                            <tr>
                                <td>Hosting</td>
                                <td class="ch-strong">{{ $hosting->domain ?? $hosting->domain_name ?? $hosting->hostname ?? 'Hosting-'.$hosting->id }}</td>
                                <td>{{ $hosting->username ?? $hosting->cpanel_username ?? '-' }}</td>
                                <td><span class="ch-badge {{ $hosting->status ?? 'active' }}">{{ $hosting->status ?? 'active' }}</span></td>
                            </tr>
                        @endforeach

                        @foreach($vpsServices as $vps)
                            <tr>
                                <td>VPS</td>
                                <td class="ch-strong">{{ $vps->name ?? $vps->hostname ?? $vps->server_name ?? 'VPS-'.$vps->id }}</td>
                                <td>{{ $vps->ip_address ?? $vps->main_ip ?? $vps->public_ip ?? '-' }}</td>
                                <td><span class="ch-badge {{ $vps->status ?? 'active' }}">{{ $vps->status ?? 'active' }}</span></td>
                            </tr>
                        @endforeach

                        @if($totalServices === 0)
                            <tr><td colspan="4" class="ch-empty">Belum ada layanan.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ch-card ch-tab-panel ch-full" data-panel="invoices">
            <div class="ch-card-head">
                <div class="ch-card-title">All Invoices</div>
                <div class="ch-card-sub">Semua invoice dan pembayaran client.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="ch-strong">{{ $invoice->invoice_number ?? $invoice->number ?? 'INV-'.$invoice->id }}</td>
                                <td>{{ $fmtMoney($invoice->total ?? $invoice->amount ?? 0) }}</td>
                                <td><span class="ch-badge {{ $invoice->status ?? 'unpaid' }}">{{ $invoice->status ?? 'unpaid' }}</span></td>
                                <td>{{ $fmtDate($invoice->due_date ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ch-empty">Belum ada invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ch-card ch-tab-panel ch-full" data-panel="tickets">
            <div class="ch-card-head">
                <div class="ch-card-title">All Tickets</div>
                <div class="ch-card-sub">Semua riwayat tiket client.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="ch-link">
                                        {{ $ticket->subject }}
                                    </a>
                                    <div style="color:#94a3b8;font-size:12px;">{{ $ticket->ticket_number }}</div>
                                </td>
                                <td>{{ $ticket->service_label ?? '-' }}</td>
                                <td><span class="ch-badge {{ $ticket->status }}">{{ $ticket->status }}</span></td>
                                <td>{{ $fmtDate($ticket->last_reply_at ?? $ticket->updated_at ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ch-empty">Belum ada tiket.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ch-card ch-tab-panel ch-full" data-panel="orders">
            <div class="ch-card-head">
                <div class="ch-card-title">All Orders</div>
                <div class="ch-card-sub">Semua riwayat order client.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ch-strong">#{{ $order->id }}</td>
                                <td>{{ $order->service_type ?? $order->type ?? $order->package_name ?? '-' }}</td>
                                <td><span class="ch-badge {{ $order->status ?? 'pending' }}">{{ $order->status ?? 'pending' }}</span></td>
                                <td>{{ $fmtDate($order->created_at ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ch-empty">Belum ada order / tabel orders belum ada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ch-card ch-tab-panel ch-full" data-panel="emails">
            <div class="ch-card-head">
                <div class="ch-card-title">Email Log</div>
                <div class="ch-card-sub">Riwayat email client jika tabel email_logs tersedia.</div>
            </div>

            <div class="ch-table-wrap">
                <table class="ch-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emails as $email)
                            <tr>
                                <td class="ch-strong">{{ $email->subject ?? '-' }}</td>
                                <td>{{ $email->to ?? $email->email ?? $user->email }}</td>
                                <td><span class="ch-badge {{ $email->status ?? 'sent' }}">{{ $email->status ?? 'sent' }}</span></td>
                                <td>{{ $fmtDate($email->created_at ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="ch-empty">Belum ada email log / tabel email_logs belum ada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.ch-tab-btn');
    const panels = document.querySelectorAll('.ch-tab-panel');

    function activateTab(tab) {
        buttons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tab);
        });

        panels.forEach(panel => {
            panel.classList.toggle('active', panel.dataset.panel === tab);
        });

        localStorage.setItem('client360_active_tab', tab);
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            activateTab(this.dataset.tab);
        });
    });

    const saved = localStorage.getItem('client360_active_tab') || 'overview';
    activateTab(saved);
});
</script>

    </section>
</div>
@endsection
