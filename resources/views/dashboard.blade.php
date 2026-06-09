@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;

    $countTable = fn($table) => Schema::hasTable($table) ? DB::table($table)->count() : 0;

    $customers = $countTable('customers');
    $vpnUsers = $countTable('vpn_users');
    $hostingAccounts = $countTable('hosting_accounts');
    $invoices = $countTable('invoices');
    $tickets = $countTable('support_tickets');

    $activeVpn = Schema::hasTable('vpn_users') && Schema::hasColumn('vpn_users', 'status')
        ? DB::table('vpn_users')->where('status', 'active')->count()
        : $vpnUsers;

    $activeHosting = Schema::hasTable('hosting_accounts') && Schema::hasColumn('hosting_accounts', 'status')
        ? DB::table('hosting_accounts')->where('status', 'active')->count()
        : $hostingAccounts;

    $unpaidInvoices = Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'status')
        ? DB::table('invoices')->whereIn('status', ['unpaid','pending'])->count()
        : 0;

    $openTickets = Schema::hasTable('support_tickets') && Schema::hasColumn('support_tickets', 'status')
        ? DB::table('support_tickets')->whereIn('status', ['open','pending','answered'])->count()
        : 0;

    $latestInvoices = Schema::hasTable('invoices')
        ? DB::table('invoices')
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->select('invoices.*', 'customers.name as customer_name')
            ->orderByDesc('invoices.id')
            ->limit(5)
            ->get()
        : collect();

    $latestTickets = collect();

    if (Schema::hasTable('support_tickets')) {
        $ticketQuery = DB::table('support_tickets');

        if (Schema::hasColumn('support_tickets', 'customer_id') && Schema::hasTable('customers')) {
            $ticketQuery
                ->leftJoin('customers', 'customers.id', '=', 'support_tickets.customer_id')
                ->select('support_tickets.*', 'customers.name as customer_name');
        } elseif (Schema::hasColumn('support_tickets', 'user_id') && Schema::hasTable('users')) {
            $ticketQuery
                ->leftJoin('users', 'users.id', '=', 'support_tickets.user_id')
                ->select('support_tickets.*', 'users.name as customer_name');
        } else {
            $ticketQuery->select('support_tickets.*');
        }

        $latestTickets = $ticketQuery
            ->orderByDesc('support_tickets.id')
            ->limit(5)
            ->get();
    }

    $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

    $url = function($name){
        try { return Route::has($name) ? route($name) : '#'; }
        catch(Throwable $e){ return '#'; }
    };
@endphp

<style>
.nd-page{display:flex;flex-direction:column;gap:18px}
.nd-hero{position:relative;overflow:hidden;border-radius:28px;padding:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 22px 60px rgba(0,0,0,.24)}
.nd-hero:before{content:"";position:absolute;right:-90px;top:-120px;width:310px;height:310px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.34),rgba(124,58,237,.30),rgba(245,158,11,.16),transparent);animation:ndSpin 22s linear infinite;opacity:.58}
.nd-hero:after{content:"";position:absolute;left:20%;top:30px;width:55%;height:2px;background:linear-gradient(90deg,transparent,rgba(56,189,248,.52),rgba(168,85,247,.58),transparent);filter:blur(.4px);opacity:.65}
@keyframes ndSpin{to{transform:rotate(360deg)}}
.nd-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.nd-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;color:#fef3c7;background:rgba(245,158,11,.14);border:1px solid rgba(245,158,11,.22);font-size:11px;font-weight:950;margin-bottom:11px}
.nd-title{margin:0;color:#fff;font-size:34px;font-weight:1000;letter-spacing:-.05em}
.nd-sub{margin-top:8px;color:#cbd5e1;font-size:14px}
.nd-actions{display:flex;gap:10px;flex-wrap:wrap}
.nd-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:15px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;transition:.2s ease}
.nd-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.nd-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.nd-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px}
.nd-stat{position:relative;overflow:hidden;border-radius:24px;padding:18px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.54));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nd-stat:before{content:"";position:absolute;right:-48px;top:-48px;width:135px;height:135px;border-radius:999px;background:var(--glow);opacity:.18}
.nd-stat-icon{width:42px;height:42px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:19px;background:rgba(148,163,184,.10);margin-bottom:13px}
.nd-stat-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.nd-stat-value{color:#fff;font-size:31px;font-weight:1000;margin-top:4px}
.nd-stat-foot{color:#94a3b8;font-size:12px;margin-top:8px;font-weight:800}
.nd-stat-foot.good{color:#86efac}.nd-stat-foot.warn{color:#fbbf24}.nd-stat-foot.bad{color:#fca5a5}
.nd-spark{display:flex;align-items:end;gap:4px;height:28px;margin-top:12px}
.nd-spark i{display:block;width:100%;border-radius:99px;background:var(--glow);opacity:.75}
.nd-main-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(320px,.8fr);gap:16px}
.nd-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nd-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 18px;border-bottom:1px solid rgba(148,163,184,.10)}
.nd-card-title{display:flex;align-items:center;gap:9px;color:white;font-size:15px;font-weight:1000}
.nd-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.nd-body{padding:18px}
.nd-chart{height:315px;border-radius:22px;background:linear-gradient(180deg,rgba(2,6,23,.22),rgba(2,6,23,.10));border:1px solid rgba(148,163,184,.08);padding:18px;position:relative;overflow:hidden}
.nd-chart-grid{position:absolute;inset:18px;background:repeating-linear-gradient(to top,rgba(148,163,184,.09) 0 1px,transparent 1px 48px),repeating-linear-gradient(to right,rgba(148,163,184,.06) 0 1px,transparent 1px 78px);opacity:.8}
.nd-line{position:absolute;left:24px;right:24px;height:110px;border-radius:50%;filter:drop-shadow(0 0 12px currentColor);opacity:.9}
.nd-line.blue{bottom:78px;color:#38bdf8;border-top:4px solid #38bdf8;transform:skewY(-8deg)}
.nd-line.purple{bottom:54px;color:#a855f7;border-top:3px solid #a855f7;transform:skewY(5deg);opacity:.75}
.nd-chart-fill{position:absolute;left:24px;right:24px;bottom:36px;height:150px;background:linear-gradient(180deg,rgba(56,189,248,.22),transparent);clip-path:polygon(0 70%,10% 58%,22% 63%,34% 28%,45% 42%,58% 64%,72% 38%,84% 55%,100% 45%,100% 100%,0 100%)}
.nd-chart-labels{position:absolute;left:24px;right:24px;bottom:14px;display:flex;justify-content:space-between;color:#64748b;font-size:11px;font-weight:800}
.nd-legend{display:flex;gap:16px;color:#cbd5e1;font-size:12px;font-weight:900;margin-bottom:12px}
.nd-legend span{display:flex;align-items:center;gap:7px}
.nd-legend i{width:8px;height:8px;border-radius:999px;display:inline-block}
.nd-strip{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:14px}
.nd-strip-item{border-radius:18px;padding:14px;background:rgba(15,23,42,.58);border:1px solid rgba(148,163,184,.10)}
.nd-strip-value{color:white;font-size:19px;font-weight:1000}
.nd-strip-label{color:#94a3b8;font-size:11px;margin-top:4px;font-weight:850}
.nd-server-list{display:flex;flex-direction:column;gap:12px}
.nd-server{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center;padding:12px;border-radius:18px;background:rgba(15,23,42,.48);border:1px solid rgba(148,163,184,.10)}
.nd-server-name{color:white;font-size:13px;font-weight:1000}
.nd-server-sub{color:#94a3b8;font-size:11px;margin-top:2px}
.nd-status{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:1000;border-radius:999px;padding:6px 9px}
.nd-status.green{color:#86efac;background:rgba(34,197,94,.12)}
.nd-status.yellow{color:#fde68a;background:rgba(245,158,11,.12)}
.nd-status.red{color:#fecaca;background:rgba(239,68,68,.12)}
.nd-status i{width:6px;height:6px;border-radius:999px;background:currentColor}
.nd-panels{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.nd-table-wrap{overflow-x:auto}
.nd-table{width:100%;border-collapse:collapse}
.nd-table th,.nd-table td{padding:14px 16px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.nd-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.nd-table td{color:#dbeafe}
.nd-main-text{color:white;font-weight:950}
.nd-muted{color:#94a3b8;font-size:12px;margin-top:2px}
.nd-map{height:260px;border-radius:22px;background:radial-gradient(circle at 20% 45%,rgba(168,85,247,.35),transparent 3%),radial-gradient(circle at 42% 35%,rgba(168,85,247,.40),transparent 3%),radial-gradient(circle at 72% 55%,rgba(168,85,247,.38),transparent 3%),radial-gradient(circle at 62% 30%,rgba(56,189,248,.24),transparent 3%),linear-gradient(135deg,rgba(30,41,59,.46),rgba(15,23,42,.32));border:1px solid rgba(148,163,184,.10);position:relative;overflow:hidden}
.nd-map:before{content:"";position:absolute;inset:36px;background:rgba(96,165,250,.16);clip-path:polygon(6% 35%,22% 18%,38% 28%,48% 18%,62% 26%,78% 18%,93% 34%,84% 50%,70% 45%,63% 60%,48% 48%,35% 58%,22% 48%,10% 58%);filter:blur(.2px)}
.nd-map:after{content:"";position:absolute;inset:0;background:radial-gradient(circle at 50% 45%,transparent 0 45%,rgba(2,6,23,.22) 70%)}
.nd-footer{color:#64748b;font-size:12px;text-align:center;padding:8px}
@media(max-width:1280px){.nd-stats{grid-template-columns:repeat(3,minmax(0,1fr))}.nd-main-grid{grid-template-columns:1fr}.nd-panels{grid-template-columns:1fr}}
@media(max-width:760px){.nd-stats,.nd-strip{grid-template-columns:1fr}.nd-title{font-size:28px}.nd-hero{padding:20px}}
</style>

<div class="nd-page">

    <section class="nd-hero">
        <div class="nd-hero-inner">
            <div>
                <div class="nd-pill">👑 NetAccess Admin Panel</div>
                <h1 class="nd-title">Monitoring</h1>
                <div class="nd-sub">Ringkasan layanan VPN, Hosting, invoice, order, dan aktivitas sistem.</div>
            </div>

            <div class="nd-actions">
                <a href="{{ $url('unified-orders.create') }}" class="nd-btn primary">+ Create Order</a>
                <a href="{{ $url('admin.full-backups.index') }}" class="nd-btn">Backup</a>
            </div>
        </div>
    </section>

    <section class="nd-stats">
        <div class="nd-stat" style="--glow:#a855f7">
            <div class="nd-stat-icon">👥</div>
            <div class="nd-stat-label">Customers</div>
            <div class="nd-stat-value">{{ $customers }}</div>
            <div class="nd-stat-foot good">▲ Client database</div>
            <div class="nd-spark"><i style="height:30%"></i><i style="height:45%"></i><i style="height:36%"></i><i style="height:55%"></i><i style="height:42%"></i><i style="height:65%"></i></div>
        </div>

        <div class="nd-stat" style="--glow:#38bdf8">
            <div class="nd-stat-icon">🛡️</div>
            <div class="nd-stat-label">Active VPN</div>
            <div class="nd-stat-value">{{ $activeVpn }}</div>
            <div class="nd-stat-foot good">▲ L2TP MikroTik</div>
            <div class="nd-spark"><i style="height:42%"></i><i style="height:36%"></i><i style="height:62%"></i><i style="height:48%"></i><i style="height:70%"></i><i style="height:58%"></i></div>
        </div>

        <div class="nd-stat" style="--glow:#22c55e">
            <div class="nd-stat-icon">🌐</div>
            <div class="nd-stat-label">Active Hosting</div>
            <div class="nd-stat-value">{{ $activeHosting }}</div>
            <div class="nd-stat-foot good">▲ WHM / cPanel</div>
            <div class="nd-spark"><i style="height:22%"></i><i style="height:36%"></i><i style="height:52%"></i><i style="height:43%"></i><i style="height:72%"></i><i style="height:63%"></i></div>
        </div>

        <div class="nd-stat" style="--glow:#f59e0b">
            <div class="nd-stat-icon">🧾</div>
            <div class="nd-stat-label">Unpaid Invoice</div>
            <div class="nd-stat-value">{{ $unpaidInvoices }}</div>
            <div class="nd-stat-foot warn">Need follow up</div>
            <div class="nd-spark"><i style="height:52%"></i><i style="height:38%"></i><i style="height:46%"></i><i style="height:65%"></i><i style="height:40%"></i><i style="height:50%"></i></div>
        </div>

        <div class="nd-stat" style="--glow:#ef4444">
            <div class="nd-stat-icon">🎫</div>
            <div class="nd-stat-label">Open Tickets</div>
            <div class="nd-stat-value">{{ $openTickets }}</div>
            <div class="nd-stat-foot {{ $openTickets ? 'bad' : 'good' }}">{{ $openTickets ? 'Action required' : 'All clear' }}</div>
            <div class="nd-spark"><i style="height:25%"></i><i style="height:50%"></i><i style="height:34%"></i><i style="height:72%"></i><i style="height:41%"></i><i style="height:30%"></i></div>
        </div>
    </section>

    <section class="nd-main-grid">
        <div class="nd-card">
            <div class="nd-card-head">
                <div class="nd-card-title"><span class="nd-dot"></span> Network Traffic Overview</div>
                <a href="{{ $url('vpn-users.index') }}" class="nd-btn">Overview</a>
            </div>

            <div class="nd-body">
                <div class="nd-legend">
                    <span><i style="background:#38bdf8"></i> Download</span>
                    <span><i style="background:#a855f7"></i> Upload</span>
                </div>

                <div class="nd-chart">
                    <div class="nd-chart-grid"></div>
                    <div class="nd-chart-fill"></div>
                    <div class="nd-line blue"></div>
                    <div class="nd-line purple"></div>
                    <div class="nd-chart-labels">
                        <span>00:00</span><span>03:00</span><span>06:00</span><span>09:00</span><span>12:00</span><span>15:00</span><span>18:00</span><span>21:00</span><span>24:00</span>
                    </div>
                </div>

                <div class="nd-strip">
                    <div class="nd-strip-item"><div class="nd-strip-value">12.45 TB</div><div class="nd-strip-label">Total Download</div></div>
                    <div class="nd-strip-item"><div class="nd-strip-value">3.68 TB</div><div class="nd-strip-label">Total Upload</div></div>
                    <div class="nd-strip-item"><div class="nd-strip-value">16.13 TB</div><div class="nd-strip-label">Total Transfer</div></div>
                    <div class="nd-strip-item"><div class="nd-strip-value">8.6%</div><div class="nd-strip-label">Transfer Growth</div></div>
                </div>
            </div>
        </div>

        <div class="nd-card">
            <div class="nd-card-head">
                <div class="nd-card-title"><span class="nd-dot" style="background:#22c55e"></span> Server Status</div>
                <a href="{{ $url('server-status.index') }}" class="nd-btn">View all</a>
            </div>

            <div class="nd-body">
                <div class="nd-server-list">
                    <div class="nd-server">
                        <div><div class="nd-server-name">MikroTik Gateway</div><div class="nd-server-sub">L2TP API / IPSec</div></div>
                        <span class="nd-status green"><i></i> Online</span>
                    </div>
                    <div class="nd-server">
                        <div><div class="nd-server-name">WHM / cPanel</div><div class="nd-server-sub">Hosting automation</div></div>
                        <span class="nd-status yellow"><i></i> Check</span>
                    </div>
                    <div class="nd-server">
                        <div><div class="nd-server-name">Laravel App</div><div class="nd-server-sub">NetAccess Panel</div></div>
                        <span class="nd-status green"><i></i> Online</span>
                    </div>
                    <div class="nd-server">
                        <div><div class="nd-server-name">Payment Gateway</div><div class="nd-server-sub">Midtrans / Xendit soon</div></div>
                        <span class="nd-status yellow"><i></i> Soon</span>
                    </div>
                    <div class="nd-server">
                        <div><div class="nd-server-name">Backup System</div><div class="nd-server-sub">Full web + database</div></div>
                        <span class="nd-status green"><i></i> Ready</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="nd-panels">
        <div class="nd-card">
            <div class="nd-card-head">
                <div class="nd-card-title"><span class="nd-dot" style="background:#a855f7"></span> Active Connections</div>
            </div>
            <div class="nd-body">
                <div class="nd-map"></div>
                <div class="nd-strip" style="grid-template-columns:repeat(3,1fr)">
                    <div class="nd-strip-item"><div class="nd-strip-value">{{ $activeVpn }}</div><div class="nd-strip-label">VPN Active</div></div>
                    <div class="nd-strip-item"><div class="nd-strip-value">{{ $activeHosting }}</div><div class="nd-strip-label">Hosting Active</div></div>
                    <div class="nd-strip-item"><div class="nd-strip-value">{{ $invoices }}</div><div class="nd-strip-label">Invoices</div></div>
                </div>
            </div>
        </div>

        <div class="nd-card">
            <div class="nd-card-head">
                <div class="nd-card-title"><span class="nd-dot" style="background:#f59e0b"></span> Recent Tickets</div>
                <a href="{{ $url('admin.tickets.index') }}" class="nd-btn">View all</a>
            </div>

            <div class="nd-table-wrap">
                <table class="nd-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Client</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestTickets as $ticket)
                            <tr>
                                <td>
                                    <div class="nd-main-text">{{ $ticket->subject ?? 'Ticket #'.$ticket->id }}</div>
                                    <div class="nd-muted">{{ $ticket->category ?? 'support' }}</div>
                                </td>
                                <td>{{ $ticket->customer_name ?? '-' }}</td>
                                <td><span class="nd-status yellow"><i></i>{{ $ticket->status ?? 'open' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:28px">Belum ada ticket.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="nd-card">
        <div class="nd-card-head">
            <div class="nd-card-title"><span class="nd-dot" style="background:#38bdf8"></span> Recent Invoices</div>
            <a href="{{ $url('invoices.index') }}" class="nd-btn">View all</a>
        </div>

        <div class="nd-table-wrap">
            <table class="nd-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestInvoices as $invoice)
                        <tr>
                            <td><div class="nd-main-text">{{ $invoice->invoice_number ?? 'INV-'.$invoice->id }}</div></td>
                            <td>{{ $invoice->customer_name ?? '-' }}</td>
                            <td>{{ $money($invoice->total ?? 0) }}</td>
                            <td>
                                @php $st = strtolower($invoice->status ?? 'unpaid'); @endphp
                                <span class="nd-status {{ $st === 'paid' ? 'green' : ($st === 'overdue' ? 'red' : 'yellow') }}"><i></i>{{ $st }}</span>
                            </td>
                            <td>{{ !empty($invoice->due_date) ? date('d M Y', strtotime($invoice->due_date)) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:30px">Belum ada invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="nd-footer">
        © {{ date('Y') }} NetAccess. Designed for performance, hosting, VPN, and automation.
    </div>

</div>
@endsection
