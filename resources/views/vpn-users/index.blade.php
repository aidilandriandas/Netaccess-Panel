@extends('layouts.app')
@section('title', 'VPN Users')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $hasVpn = Schema::hasTable('vpn_users');

    $url = function ($name, $param = null) {
        try {
            if (!Route::has($name)) return '#';
            return $param ? route($name, $param) : route($name);
        } catch (\Throwable $e) {
            return '#';
        }
    };

    $date = function ($value, $format = 'd M Y') {
        if (!$value) return '-';
        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $customerName = function ($customerId = null, $userId = null) {
        try {
            if ($customerId && Schema::hasTable('customers')) {
                if (Schema::hasColumn('customers', 'name')) {
                    $name = DB::table('customers')->where('id', $customerId)->value('name');
                    if ($name) return $name;
                }

                if (Schema::hasColumn('customers', 'email')) {
                    $email = DB::table('customers')->where('id', $customerId)->value('email');
                    if ($email) return $email;
                }
            }

            if ($userId && Schema::hasTable('users')) {
                if (Schema::hasColumn('users', 'name')) {
                    $name = DB::table('users')->where('id', $userId)->value('name');
                    if ($name) return $name;
                }

                if (Schema::hasColumn('users', 'email')) {
                    $email = DB::table('users')->where('id', $userId)->value('email');
                    if ($email) return $email;
                }
            }
        } catch (\Throwable $e) {}

        return '-';
    };

    $userIdFromCustomer = function ($customerId = null, $email = null) {
        try {
            if ($customerId && Schema::hasTable('customers')) {
                if (Schema::hasColumn('customers', 'user_id')) {
                    $id = DB::table('customers')->where('id', $customerId)->value('user_id');
                    if ($id) return $id;
                }

                if (!$email && Schema::hasColumn('customers', 'email')) {
                    $email = DB::table('customers')->where('id', $customerId)->value('email');
                }
            }

            if ($email && Schema::hasTable('users')) {
                return DB::table('users')->where('email', $email)->value('id');
            }
        } catch (\Throwable $e) {}

        return null;
    };

    $q = request('q');
    $statusFilter = request('status');

    if ($hasVpn) {
        $query = DB::table('vpn_users');

        if ($q) {
            $query->where(function ($w) use ($q) {
                if (Schema::hasColumn('vpn_users', 'username')) {
                    $w->orWhere('username', 'like', "%{$q}%");
                }

                if (Schema::hasColumn('vpn_users', 'remote_address')) {
                    $w->orWhere('remote_address', 'like', "%{$q}%");
                }

                if (Schema::hasColumn('vpn_users', 'ip_address')) {
                    $w->orWhere('ip_address', 'like', "%{$q}%");
                }

                if (Schema::hasColumn('vpn_users', 'customer_id') && Schema::hasTable('customers')) {
                    $customerIds = DB::table('customers')
                        ->when(Schema::hasColumn('customers', 'name'), fn ($c) => $c->orWhere('name', 'like', "%{$q}%"))
                        ->when(Schema::hasColumn('customers', 'email'), fn ($c) => $c->orWhere('email', 'like', "%{$q}%"))
                        ->pluck('id');

                    if ($customerIds->count()) {
                        $w->orWhereIn('customer_id', $customerIds);
                    }
                }
            });
        }

        if ($statusFilter && Schema::hasColumn('vpn_users', 'status')) {
            $query->where('status', $statusFilter);
        }

        $vpnUsers = $query->orderByDesc('id')->paginate(15)->appends(request()->query());
    } else {
        $vpnUsers = collect();
    }

    $totalVpn = $hasVpn ? DB::table('vpn_users')->count() : 0;

    $activeVpn = $hasVpn && Schema::hasColumn('vpn_users', 'status')
        ? DB::table('vpn_users')->where('status', 'active')->count()
        : $totalVpn;

    $suspendedVpn = $hasVpn && Schema::hasColumn('vpn_users', 'status')
        ? DB::table('vpn_users')->whereIn('status', ['suspended', 'disabled'])->count()
        : 0;

    $expiredSoon = $hasVpn && Schema::hasColumn('vpn_users', 'expired_at')
        ? DB::table('vpn_users')
            ->whereDate('expired_at', '>=', now())
            ->whereDate('expired_at', '<=', now()->addDays(7))
            ->count()
        : 0;

    $serverIp = null;
    $ipsecKey = null;

    try {
        if (Schema::hasTable('settings')) {
            $serverIp = DB::table('settings')->whereIn('key', ['mikrotik_public_ip', 'mikrotik_host'])->value('value');
            $ipsecKey = DB::table('settings')->whereIn('key', ['mikrotik_ipsec_secret', 'ipsec_preshared_key', 'l2tp_ipsec_secret'])->value('value');
        }
    } catch (\Throwable $e) {}

    $serverIp = $serverIp ?: '206.237.97.253';
    $ipsecKey = $ipsecKey ?: '12345678';

    $statusClass = function ($status) {
        $status = strtolower((string) ($status ?: 'active'));

        return match ($status) {
            'active' => 'nv-badge active',
            'suspended', 'disabled' => 'nv-badge suspended',
            'expired' => 'nv-badge expired',
            default => 'nv-badge pending',
        };
    };

    $firstRoute = function (array $names) {
        foreach ($names as $name) {
            if (Route::has($name)) return $name;
        }
        return null;
    };

    $createRoute = $firstRoute(['vpn-users.create', 'vpn.create']);
    $showRoute = $firstRoute(['vpn-users.show', 'vpn.show']);
    $editRoute = $firstRoute(['vpn-users.edit', 'vpn.edit']);
    $suspendRoute = $firstRoute(['vpn-users.suspend', 'vpn.suspend']);
    $unsuspendRoute = $firstRoute(['vpn-users.unsuspend', 'vpn.unsuspend']);
    $terminateRoute = $firstRoute(['vpn-users.terminate', 'vpn.terminate']);
@endphp

<style>
.nv-page{display:flex;flex-direction:column;gap:18px}
.nv-hero{position:relative;overflow:hidden;border-radius:28px;padding:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 22px 60px rgba(0,0,0,.22)}
.nv-hero:before{content:"";position:absolute;right:-80px;top:-90px;width:260px;height:260px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.30),rgba(124,58,237,.28),rgba(14,165,233,.14));animation:nvSpin 20s linear infinite;opacity:.55}
@keyframes nvSpin{to{transform:rotate(360deg)}}
.nv-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
.nv-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:11px}
.nv-title{margin:0;color:#fff;font-size:32px;font-weight:1000;letter-spacing:-.045em}
.nv-sub{margin-top:8px;color:#cbd5e1;font-size:14px}
.nv-actions{display:flex;gap:10px;flex-wrap:wrap}
.nv-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:15px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;transition:.2s ease}
.nv-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.nv-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.nv-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.nv-stat{position:relative;overflow:hidden;border-radius:23px;padding:19px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.54));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nv-stat:after{content:"";position:absolute;right:-45px;top:-50px;width:140px;height:140px;border-radius:999px;background:#3b82f6;opacity:.16}
.nv-stat.green:after{background:#22c55e}.nv-stat.yellow:after{background:#f59e0b}.nv-stat.purple:after{background:#8b5cf6}
.nv-stat-icon{width:40px;height:40px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(148,163,184,.10);font-size:18px;margin-bottom:15px}
.nv-stat-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.nv-stat-value{color:white;font-size:30px;font-weight:1000;margin-top:6px;letter-spacing:-.035em}
.nv-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:16px}
.nv-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nv-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:17px 19px;border-bottom:1px solid rgba(148,163,184,.10)}
.nv-card-title{display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.nv-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.nv-filter{display:flex;gap:10px;flex-wrap:wrap}
.nv-input,.nv-select{height:42px;border-radius:14px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.68);color:white;padding:0 13px;outline:none}
.nv-filter-btn{height:42px;border:0;border-radius:14px;padding:0 15px;color:white;font-size:12px;font-weight:950;background:linear-gradient(135deg,#2563eb,#7c3aed);cursor:pointer}
.nv-table-wrap{overflow-x:auto}
.nv-table{width:100%;border-collapse:collapse}
.nv-table th,.nv-table td{padding:15px 17px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.nv-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.nv-table td{color:#dbeafe}
.nv-table tbody tr{transition:.18s ease}
.nv-table tbody tr:hover{background:rgba(37,99,235,.07)}
.nv-main{color:white;font-weight:950}
.nv-muted{color:#94a3b8;font-size:12px;margin-top:2px}
.nv-link{color:#60a5fa;text-decoration:none;font-weight:950}
.nv-badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.nv-badge.active{color:#86efac;background:rgba(34,197,94,.14)}
.nv-badge.suspended,.nv-badge.expired{color:#fca5a5;background:rgba(239,68,68,.13)}
.nv-badge.pending{color:#fde68a;background:rgba(245,158,11,.13)}
.nv-row-actions{display:flex;gap:8px;flex-wrap:wrap}
.nv-small-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:11px;padding:8px 10px;font-size:11px;font-weight:950;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.65);color:#e5e7eb;cursor:pointer}
.nv-small-btn.blue{color:#93c5fd}.nv-small-btn.green{color:#86efac}.nv-small-btn.purple{color:#c4b5fd}.nv-small-btn.yellow{color:#fde68a}.nv-small-btn.red{color:#fca5a5}
.nv-form-inline{display:inline-flex;margin:0}
.nv-empty{padding:34px;text-align:center;color:#94a3b8}
.nv-pagination{padding:16px 19px}
.nv-info{padding:17px}
.nv-info-item{padding:14px;border-radius:18px;border:1px solid rgba(148,163,184,.12);background:rgba(2,6,23,.24);margin-bottom:11px}
.nv-info-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.nv-info-value{color:white;font-size:15px;font-weight:950;margin-top:6px;word-break:break-all}
.nv-copy{margin-top:9px;border:0;border-radius:11px;padding:8px 10px;background:rgba(37,99,235,.18);color:#bfdbfe;font-size:11px;font-weight:950;cursor:pointer}
@media(max-width:1200px){.nv-grid{grid-template-columns:1fr}.nv-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.nv-stats{grid-template-columns:1fr}.nv-title{font-size:27px}.nv-filter{width:100%}.nv-input,.nv-select{width:100%}}
</style>

<div class="nv-page">

    <section class="nv-hero">
        <div class="nv-hero-inner">
            <div>
                <div class="nv-pill">🛡️ L2TP MikroTik</div>
                <h1 class="nv-title">VPN Users</h1>
                <div class="nv-sub">Kelola akun L2TP client, status layanan, expired, dan sinkronisasi ke MikroTik.</div>
            </div>

            <div class="nv-actions">
                @if($createRoute)
                    <a href="{{ route($createRoute) }}" class="nv-btn primary">+ Tambah VPN</a>
                @endif
                <a href="{{ $url('packages.index') }}" class="nv-btn">📦 Packages</a>
                <a href="{{ $url('admin.tickets.create') }}" class="nv-btn">🎫 Tiket</a>
            </div>
        </div>
    </section>

    <section class="nv-stats">
        <div class="nv-stat">
            <div class="nv-stat-icon">🛡️</div>
            <div class="nv-stat-label">Total VPN</div>
            <div class="nv-stat-value">{{ $totalVpn }}</div>
        </div>

        <div class="nv-stat green">
            <div class="nv-stat-icon">✅</div>
            <div class="nv-stat-label">Active</div>
            <div class="nv-stat-value">{{ $activeVpn }}</div>
        </div>

        <div class="nv-stat yellow">
            <div class="nv-stat-icon">⏳</div>
            <div class="nv-stat-label">Expired 7 Hari</div>
            <div class="nv-stat-value">{{ $expiredSoon }}</div>
        </div>

        <div class="nv-stat purple">
            <div class="nv-stat-icon">⛔</div>
            <div class="nv-stat-label">Suspended</div>
            <div class="nv-stat-value">{{ $suspendedVpn }}</div>
        </div>
    </section>

    <section class="nv-grid">

        <div class="nv-card">
            <div class="nv-card-head">
                <div class="nv-card-title">
                    <span class="nv-dot"></span>
                    Daftar VPN User
                </div>

                <form method="GET" action="{{ $url('vpn-users.index') }}" class="nv-filter">
                    <input type="text" name="q" value="{{ request('q') }}" class="nv-input" placeholder="Cari username / customer / IP...">

                    <select name="status" class="nv-select">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    </select>

                    <button class="nv-filter-btn" type="submit">Filter</button>

                    @if(request('q') || request('status'))
                        <a href="{{ $url('vpn-users.index') }}" class="nv-small-btn">Reset</a>
                    @endif
                </form>
            </div>

            <div class="nv-table-wrap">
                <table class="nv-table">
                    <thead>
                        <tr>
                            <th>VPN User</th>
                            <th>Customer</th>
                            <th>IP</th>
                            <th>Status</th>
                            <th>Expired</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vpnUsers as $vpn)
                            @php
                                $username = $vpn->username ?? 'vpn-'.$vpn->id;
                                $password = $vpn->password ?? $vpn->plain_password ?? $vpn->vpn_password ?? '-';
                                $status = strtolower($vpn->status ?? 'active');
                                $customerId = $vpn->customer_id ?? null;
                                $userId = $vpn->user_id ?? null;
                                $resolvedUserId = $userId ?: $userIdFromCustomer($customerId);
                                $ip = $vpn->remote_address ?? $vpn->ip_address ?? $vpn->local_address ?? '-';
                            @endphp

                            <tr>
                                <td>
                                    @if($showRoute)
                                        <a href="{{ route($showRoute, $vpn->id) }}" class="nv-link">{{ $username }}</a>
                                    @else
                                        <div class="nv-main">{{ $username }}</div>
                                    @endif
                                    <div class="nv-muted">L2TP/IPSec</div>
                                </td>

                                <td>
                                    <div class="nv-main">{{ $customerName($customerId, $userId) }}</div>
                                    @if($customerId)
                                        <div class="nv-muted">Customer ID: {{ $customerId }}</div>
                                    @endif
                                </td>

                                <td>{{ $ip }}</td>

                                <td>
                                    <span class="{{ $statusClass($status) }}">{{ $status }}</span>
                                </td>

                                <td>{{ $date($vpn->expired_at ?? $vpn->expires_at ?? null) }}</td>

                                <td>
                                    <div class="nv-row-actions">
                                        @if($resolvedUserId && Route::has('admin.client-history.show'))
                                            <a href="{{ route('admin.client-history.show', $resolvedUserId) }}" class="nv-small-btn purple">360</a>
                                        @elseif($customerId && Route::has('admin.client-history.customer'))
                                            <a href="{{ route('admin.client-history.customer', $customerId) }}" class="nv-small-btn purple">360</a>
                                        @endif

                                        @if($showRoute)
                                            <a href="{{ route($showRoute, $vpn->id) }}" class="nv-small-btn blue">Detail</a>
                                        @endif

                                        @if($editRoute)
                                            <a href="{{ route($editRoute, $vpn->id) }}" class="nv-small-btn">Edit</a>
                                        @endif

                                        @if($status === 'active' && $suspendRoute)
                                            <form action="{{ route($suspendRoute, $vpn->id) }}" method="POST" class="nv-form-inline">
                                                @csrf
                                                <button type="submit" class="nv-small-btn yellow" onclick="return confirm('Suspend VPN user ini?')">Suspend</button>
                                            </form>
                                        @elseif(in_array($status, ['suspended', 'disabled']) && $unsuspendRoute)
                                            <form action="{{ route($unsuspendRoute, $vpn->id) }}" method="POST" class="nv-form-inline">
                                                @csrf
                                                <button type="submit" class="nv-small-btn green" onclick="return confirm('Aktifkan kembali VPN user ini?')">Unsuspend</button>
                                            </form>
                                        @endif

                                        @if($terminateRoute)
                                            <form action="{{ route($terminateRoute, $vpn->id) }}" method="POST" class="nv-form-inline">
                                                @csrf
                                                <button type="submit" class="nv-small-btn red" onclick="return confirm('Terminate VPN user ini dari panel dan MikroTik?')">Terminate</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="nv-empty">Belum ada VPN user ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($vpnUsers, 'links'))
                <div class="nv-pagination">
                    {{ $vpnUsers->links() }}
                </div>
            @endif
        </div>

        <aside class="nv-card">
            <div class="nv-card-head">
                <div class="nv-card-title">
                    <span class="nv-dot" style="background:#22c55e;"></span>
                    Server Info
                </div>
            </div>

            <div class="nv-info">
                <div class="nv-info-item">
                    <div class="nv-info-label">Server VPN</div>
                    <div class="nv-info-value">{{ $serverIp }}</div>
                    <button type="button" class="nv-copy" data-copy="{{ $serverIp }}">Copy IP</button>
                </div>

                <div class="nv-info-item">
                    <div class="nv-info-label">Protocol</div>
                    <div class="nv-info-value">L2TP/IPSec</div>
                </div>

                <div class="nv-info-item">
                    <div class="nv-info-label">IPSec Key</div>
                    <div class="nv-info-value">{{ $ipsecKey }}</div>
                    <button type="button" class="nv-copy" data-copy="{{ $ipsecKey }}">Copy Key</button>
                </div>

                <div class="nv-info-item">
                    <div class="nv-info-label">Client Range</div>
                    <div class="nv-info-value">192.168.100.0/24</div>
                </div>
            </div>
        </aside>

    </section>
</div>

<script>
document.addEventListener('click', function(e){
    const btn = e.target.closest('[data-copy]');
    if(!btn) return;

    const text = btn.getAttribute('data-copy') || '';

    function done(){
        const old = btn.textContent;
        btn.textContent = 'Copied';
        setTimeout(() => btn.textContent = old, 900);
    }

    if(navigator.clipboard && window.isSecureContext){
        navigator.clipboard.writeText(text).then(done).catch(() => fallback());
    } else {
        fallback();
    }

    function fallback(){
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try { document.execCommand('copy'); } catch(e) {}
        document.body.removeChild(ta);
        done();
    }
});
</script>
@endsection
