@extends('layouts.app')
@section('title', 'Hosting Accounts')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $hasHosting = Schema::hasTable('hosting_accounts');

    $url = function ($name, $param = null) {
        try {
            if (!Route::has($name)) return '#';
            return $param ? route($name, $param) : route($name);
        } catch (\Throwable $e) {
            return '#';
        }
    };

    $firstRoute = function (array $names) {
        foreach ($names as $name) {
            if (Route::has($name)) return $name;
        }
        return null;
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

    if ($hasHosting) {
        $query = DB::table('hosting_accounts');

        if ($q) {
            $query->where(function ($w) use ($q) {
                foreach (['domain', 'domain_name', 'hostname', 'username', 'cpanel_username'] as $column) {
                    if (Schema::hasColumn('hosting_accounts', $column)) {
                        $w->orWhere($column, 'like', "%{$q}%");
                    }
                }

                if (Schema::hasColumn('hosting_accounts', 'customer_id') && Schema::hasTable('customers')) {
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

        if ($statusFilter && Schema::hasColumn('hosting_accounts', 'status')) {
            $query->where('status', $statusFilter);
        }

        $hostingAccounts = $query->orderByDesc('id')->paginate(15)->appends(request()->query());
    } else {
        $hostingAccounts = collect();
    }

    $totalHosting = $hasHosting ? DB::table('hosting_accounts')->count() : 0;

    $activeHosting = $hasHosting && Schema::hasColumn('hosting_accounts', 'status')
        ? DB::table('hosting_accounts')->where('status', 'active')->count()
        : $totalHosting;

    $suspendedHosting = $hasHosting && Schema::hasColumn('hosting_accounts', 'status')
        ? DB::table('hosting_accounts')->whereIn('status', ['suspended', 'disabled'])->count()
        : 0;

    $expiredSoon = $hasHosting && Schema::hasColumn('hosting_accounts', 'expired_at')
        ? DB::table('hosting_accounts')
            ->whereDate('expired_at', '>=', now())
            ->whereDate('expired_at', '<=', now()->addDays(7))
            ->count()
        : 0;

    $whmHost = null;
    try {
        if (Schema::hasTable('settings')) {
            $whmHost = DB::table('settings')->whereIn('key', ['whm_host', 'cpanel_url', 'hosting_host'])->value('value');
        }
    } catch (\Throwable $e) {}

    $whmHost = $whmHost ?: 'whm.andriandas.my.id';
    $cleanHost = preg_replace('#^https?://#', '', rtrim($whmHost, '/'));
    $cpanelUrl = 'https://' . $cleanHost . ':2083';
    $whmUrl = 'https://' . $cleanHost . ':2087';

    $statusClass = function ($status) {
        $status = strtolower((string) ($status ?: 'active'));

        return match ($status) {
            'active' => 'nh-badge active',
            'suspended', 'disabled' => 'nh-badge suspended',
            'expired' => 'nh-badge expired',
            default => 'nh-badge pending',
        };
    };

    $createRoute = $firstRoute(['hosting-accounts.create', 'hosting.create']);
    $showRoute = $firstRoute(['hosting-accounts.show', 'hosting.show']);
    $editRoute = $firstRoute(['hosting-accounts.edit', 'hosting.edit']);
    $suspendRoute = $firstRoute(['hosting-accounts.suspend', 'hosting.suspend']);
    $unsuspendRoute = $firstRoute(['hosting-accounts.unsuspend', 'hosting.unsuspend']);
    $terminateRoute = $firstRoute(['hosting-accounts.terminate', 'hosting.terminate']);

    $editRoute = $editRoute ?? $firstRoute(['hosting-accounts.edit', 'admin.hosting-accounts.edit', 'hosting.accounts.edit']);
    $destroyRoute = $destroyRoute ?? $firstRoute(['hosting-accounts.destroy', 'admin.hosting-accounts.destroy', 'hosting.accounts.destroy']);

@endphp

<style>
.nh-page{display:flex;flex-direction:column;gap:18px}
.nh-hero{position:relative;overflow:hidden;border-radius:28px;padding:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 22px 60px rgba(0,0,0,.22)}
.nh-hero:before{content:"";position:absolute;right:-80px;top:-90px;width:260px;height:260px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.30),rgba(16,185,129,.26),rgba(14,165,233,.14));animation:nhSpin 20s linear infinite;opacity:.55}
@keyframes nhSpin{to{transform:rotate(360deg)}}
.nh-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
.nh-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:11px}
.nh-title{margin:0;color:#fff;font-size:32px;font-weight:1000;letter-spacing:-.045em}
.nh-sub{margin-top:8px;color:#cbd5e1;font-size:14px}
.nh-actions{display:flex;gap:10px;flex-wrap:wrap}
.nh-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:15px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;transition:.2s ease}
.nh-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.nh-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#10b981);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.nh-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.nh-stat{position:relative;overflow:hidden;border-radius:23px;padding:19px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.54));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nh-stat:after{content:"";position:absolute;right:-45px;top:-50px;width:140px;height:140px;border-radius:999px;background:#3b82f6;opacity:.16}
.nh-stat.green:after{background:#22c55e}.nh-stat.yellow:after{background:#f59e0b}.nh-stat.purple:after{background:#8b5cf6}
.nh-stat-icon{width:40px;height:40px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(148,163,184,.10);font-size:18px;margin-bottom:15px}
.nh-stat-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.nh-stat-value{color:white;font-size:30px;font-weight:1000;margin-top:6px;letter-spacing:-.035em}
.nh-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:16px}
.nh-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nh-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:17px 19px;border-bottom:1px solid rgba(148,163,184,.10)}
.nh-card-title{display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.nh-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.nh-filter{display:flex;gap:10px;flex-wrap:wrap}
.nh-input,.nh-select{height:42px;border-radius:14px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.68);color:white;padding:0 13px;outline:none}
.nh-filter-btn{height:42px;border:0;border-radius:14px;padding:0 15px;color:white;font-size:12px;font-weight:950;background:linear-gradient(135deg,#2563eb,#10b981);cursor:pointer}
.nh-table-wrap{overflow-x:auto}
.nh-table{width:100%;border-collapse:collapse}
.nh-table th,.nh-table td{padding:15px 17px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.nh-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.nh-table td{color:#dbeafe}
.nh-table tbody tr{transition:.18s ease}
.nh-table tbody tr:hover{background:rgba(37,99,235,.07)}
.nh-main{color:white;font-weight:950}
.nh-muted{color:#94a3b8;font-size:12px;margin-top:2px}
.nh-link{color:#60a5fa;text-decoration:none;font-weight:950}
.nh-badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.nh-badge.active{color:#86efac;background:rgba(34,197,94,.14)}
.nh-badge.suspended,.nh-badge.expired{color:#fca5a5;background:rgba(239,68,68,.13)}
.nh-badge.pending{color:#fde68a;background:rgba(245,158,11,.13)}
.nh-row-actions{display:flex;gap:8px;flex-wrap:wrap}
.nh-small-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:11px;padding:8px 10px;font-size:11px;font-weight:950;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.65);color:#e5e7eb;cursor:pointer}
.nh-small-btn.blue{color:#93c5fd}.nh-small-btn.green{color:#86efac}.nh-small-btn.purple{color:#c4b5fd}.nh-small-btn.yellow{color:#fde68a}.nh-small-btn.red{color:#fca5a5}
.nh-form-inline{display:inline-flex;margin:0}
.nh-empty{padding:34px;text-align:center;color:#94a3b8}
.nh-pagination{padding:16px 19px}
.nh-info{padding:17px}
.nh-info-item{padding:14px;border-radius:18px;border:1px solid rgba(148,163,184,.12);background:rgba(2,6,23,.24);margin-bottom:11px}
.nh-info-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.nh-info-value{color:white;font-size:15px;font-weight:950;margin-top:6px;word-break:break-all}
.nh-copy{margin-top:9px;border:0;border-radius:11px;padding:8px 10px;background:rgba(37,99,235,.18);color:#bfdbfe;font-size:11px;font-weight:950;cursor:pointer}
.nh-side-link{display:flex;align-items:center;justify-content:space-between;text-decoration:none;padding:13px 14px;border-radius:16px;border:1px solid rgba(148,163,184,.12);background:rgba(2,6,23,.24);color:#e5e7eb;font-weight:950;font-size:12px;margin-bottom:10px}
.nh-side-link:hover{border-color:rgba(96,165,250,.32);transform:translateY(-1px)}
@media(max-width:1200px){.nh-grid{grid-template-columns:1fr}.nh-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.nh-stats{grid-template-columns:1fr}.nh-title{font-size:27px}.nh-filter{width:100%}.nh-input,.nh-select{width:100%}}
</style>

<div class="nh-page">

    <section class="nh-hero">
        <div class="nh-hero-inner">
            <div>
                <div class="nh-pill">🌐 cPanel / WHM</div>
                <h1 class="nh-title">Hosting Accounts</h1>
                <div class="nh-sub">Kelola akun hosting, domain, cPanel, expired, dan provisioning via WHM.</div>
            </div>

            <div class="nh-actions">
                @if($createRoute)
                    <a href="{{ route($createRoute) }}" class="nh-btn primary">+ Tambah Hosting</a>
                @endif
                <a href="{{ $url('packages.index') }}" class="nh-btn">📦 Packages</a>
                <a href="{{ $url('admin.tickets.create') }}" class="nh-btn">🎫 Tiket</a>
            </div>
        </div>
    </section>

    <section class="nh-stats">
        <div class="nh-stat">
            <div class="nh-stat-icon">🌐</div>
            <div class="nh-stat-label">Total Hosting</div>
            <div class="nh-stat-value">{{ $totalHosting }}</div>
        </div>

        <div class="nh-stat green">
            <div class="nh-stat-icon">✅</div>
            <div class="nh-stat-label">Active</div>
            <div class="nh-stat-value">{{ $activeHosting }}</div>
        </div>

        <div class="nh-stat yellow">
            <div class="nh-stat-icon">⏳</div>
            <div class="nh-stat-label">Expired 7 Hari</div>
            <div class="nh-stat-value">{{ $expiredSoon }}</div>
        </div>

        <div class="nh-stat purple">
            <div class="nh-stat-icon">⛔</div>
            <div class="nh-stat-label">Suspended</div>
            <div class="nh-stat-value">{{ $suspendedHosting }}</div>
        </div>
    </section>

    <section class="nh-grid">

        <div class="nh-card">
            <div class="nh-card-head">
                <div class="nh-card-title">
                    <span class="nh-dot"></span>
                    Daftar Hosting
                </div>

                <form method="GET" action="{{ $url('hosting-accounts.index') }}" class="nh-filter">
                    <input type="text" name="q" value="{{ request('q') }}" class="nh-input" placeholder="Cari domain / username / customer...">

                    <select name="status" class="nh-select">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    </select>

                    <button class="nh-filter-btn" type="submit">Filter</button>

                    @if(request('q') || request('status'))
                        <a href="{{ $url('hosting-accounts.index') }}" class="nh-small-btn">Reset</a>
                    @endif
                </form>
            </div>

            <div class="nh-table-wrap">
                <table class="nh-table">
                    <thead>
                        <tr>
                            <th>Hosting</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Status</th>
                            <th>Expired</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hostingAccounts as $hosting)
                            @php
                                $domain = $hosting->domain ?? $hosting->domain_name ?? $hosting->hostname ?? 'hosting-'.$hosting->id;
                                $username = $hosting->username ?? $hosting->cpanel_username ?? '-';
                                $package = $hosting->package_name ?? $hosting->package ?? $hosting->plan ?? '-';
                                $status = strtolower($hosting->status ?? 'active');
                                $customerId = $hosting->customer_id ?? null;
                                $userId = $hosting->user_id ?? null;
                                $resolvedUserId = $userId ?: $userIdFromCustomer($customerId);
                            @endphp

                            <tr>
                                <td>
                                    @if($showRoute)
                                        <a href="{{ route($showRoute, $hosting->id) }}" class="nh-link">{{ $domain }}</a>
                                    @else
                                        <div class="nh-main">{{ $domain }}</div>
                                    @endif
                                    <div class="nh-muted">User: {{ $username }}</div>
                                </td>

                                <td>
                                    <div class="nh-main">{{ $customerName($customerId, $userId) }}</div>
                                    @if($customerId)
                                        <div class="nh-muted">Customer ID: {{ $customerId }}</div>
                                    @endif
                                </td>

                                <td>{{ $package }}</td>

                                <td>
                                    <span class="{{ $statusClass($status) }}">{{ $status }}</span>
                                </td>

                                <td>{{ $date($hosting->expired_at ?? $hosting->expires_at ?? null) }}</td>

                                <td>
                                    <div class="nh-row-actions">
                                        @if($resolvedUserId && Route::has('admin.client-history.show'))
                                            <a href="{{ route('admin.client-history.show', $resolvedUserId) }}" class="nh-small-btn purple">360</a>
                                        @elseif($customerId && Route::has('admin.client-history.customer'))
                                            <a href="{{ route('admin.client-history.customer', $customerId) }}" class="nh-small-btn purple">360</a>
                                        @endif

                                        @if($showRoute)
                                            <a href="{{ route($showRoute, $hosting->id) }}" class="nh-small-btn blue">Detail</a>
                                        @endif

                                        @if($editRoute)
                                            <a href="{{ route($editRoute, $hosting->id) }}" class="nh-small-btn">Edit</a>
                                        @endif

                                        @if($status === 'active' && $suspendRoute)
                                            <form action="{{ route($suspendRoute, $hosting->id) }}" method="POST" class="nh-form-inline">
                                                @csrf
                                                <button type="submit" class="nh-small-btn yellow" onclick="return confirm('Suspend hosting ini?')">Suspend</button>
                                            </form>
                                        @elseif(in_array($status, ['suspended', 'disabled']) && $unsuspendRoute)
                                            <form action="{{ route($unsuspendRoute, $hosting->id) }}" method="POST" class="nh-form-inline">
                                                @csrf
                                                <button type="submit" class="nh-small-btn green" onclick="return confirm('Aktifkan kembali hosting ini?')">Unsuspend</button>
                                            </form>
                                        @endif

                                        
                                            @if($destroyRoute)
                                                <form action="{{ route($destroyRoute, $row->id ?? $account->id ?? $hosting->id) }}" method="POST" style="display:inline-flex;margin:0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ha-small red" onclick="return confirm('Hapus hosting account ini?')">Hapus</button>
                                                </form>
                                            @endif

                                            @if($terminateRoute)
                                            <form action="{{ route($terminateRoute, $hosting->id) }}" method="POST" class="nh-form-inline">
                                                @csrf
                                                <button type="submit" class="nh-small-btn red" onclick="return confirm('Terminate hosting ini dari panel dan WHM?')">Terminate</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="nh-empty">Belum ada hosting account ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($hostingAccounts, 'links'))
                <div class="nh-pagination">
                    {{ $hostingAccounts->links() }}
                </div>
            @endif
        </div>

        <aside class="nh-card">
            <div class="nh-card-head">
                <div class="nh-card-title">
                    <span class="nh-dot" style="background:#22c55e;"></span>
                    Hosting Control
                </div>
            </div>

            <div class="nh-info">
                <div class="nh-info-item">
                    <div class="nh-info-label">WHM Host</div>
                    <div class="nh-info-value">{{ $cleanHost }}</div>
                    <button type="button" class="nh-copy" data-copy="{{ $cleanHost }}">Copy Host</button>
                </div>

                <div class="nh-info-item">
                    <div class="nh-info-label">cPanel URL</div>
                    <div class="nh-info-value">{{ $cpanelUrl }}</div>
                    <button type="button" class="nh-copy" data-copy="{{ $cpanelUrl }}">Copy cPanel</button>
                </div>

                <a href="{{ $cpanelUrl }}" target="_blank" class="nh-side-link">
                    <span>Open cPanel</span>
                    <span>↗</span>
                </a>

                <a href="{{ $whmUrl }}" target="_blank" class="nh-side-link">
                    <span>Open WHM</span>
                    <span>↗</span>
                </a>
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
