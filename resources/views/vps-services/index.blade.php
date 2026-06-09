@extends('layouts.app')
@section('title', 'VPS Services')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $hasVps = Schema::hasTable('vps_services');

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

    if ($hasVps) {
        $query = DB::table('vps_services');

        if ($q) {
            $query->where(function ($w) use ($q) {
                foreach (['name', 'hostname', 'server_name', 'ip_address', 'main_ip', 'public_ip', 'os', 'operating_system'] as $column) {
                    if (Schema::hasColumn('vps_services', $column)) {
                        $w->orWhere($column, 'like', "%{$q}%");
                    }
                }

                if (Schema::hasColumn('vps_services', 'customer_id') && Schema::hasTable('customers')) {
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

        if ($statusFilter && Schema::hasColumn('vps_services', 'status')) {
            $query->where('status', $statusFilter);
        }

        $vpsServices = $query->orderByDesc('id')->paginate(15)->appends(request()->query());
    } else {
        $vpsServices = collect();
    }

    $totalVps = $hasVps ? DB::table('vps_services')->count() : 0;

    $activeVps = $hasVps && Schema::hasColumn('vps_services', 'status')
        ? DB::table('vps_services')->where('status', 'active')->count()
        : $totalVps;

    $suspendedVps = $hasVps && Schema::hasColumn('vps_services', 'status')
        ? DB::table('vps_services')->whereIn('status', ['suspended', 'disabled', 'stopped'])->count()
        : 0;

    $expiredSoon = $hasVps && Schema::hasColumn('vps_services', 'expired_at')
        ? DB::table('vps_services')
            ->whereDate('expired_at', '>=', now())
            ->whereDate('expired_at', '<=', now()->addDays(7))
            ->count()
        : 0;

    $totalCpu = 0;
    $totalRam = 0;

    if ($hasVps) {
        foreach (['cpu', 'vcpu', 'cpu_core', 'cores'] as $col) {
            if (Schema::hasColumn('vps_services', $col)) {
                $totalCpu = DB::table('vps_services')->sum($col);
                break;
            }
        }

        foreach (['ram', 'memory'] as $col) {
            if (Schema::hasColumn('vps_services', $col)) {
                $totalRam = DB::table('vps_services')->sum($col);
                break;
            }
        }
    }

    $statusClass = function ($status) {
        $status = strtolower((string) ($status ?: 'active'));

        return match ($status) {
            'active', 'running' => 'ns-badge active',
            'suspended', 'disabled', 'stopped' => 'ns-badge suspended',
            'expired' => 'ns-badge expired',
            default => 'ns-badge pending',
        };
    };

    $createRoute = $firstRoute(['vps-services.create', 'vps.create']);
    $showRoute = $firstRoute(['vps-services.show', 'vps.show']);
    $editRoute = $firstRoute(['vps-services.edit', 'vps.edit']);
    $suspendRoute = $firstRoute(['vps-services.suspend', 'vps.suspend']);
    $unsuspendRoute = $firstRoute(['vps-services.unsuspend', 'vps.unsuspend']);
    $terminateRoute = $firstRoute(['vps-services.terminate', 'vps.terminate']);
@endphp

<style>
.ns-page{display:flex;flex-direction:column;gap:18px}
.ns-hero{position:relative;overflow:hidden;border-radius:28px;padding:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 22px 60px rgba(0,0,0,.22)}
.ns-hero:before{content:"";position:absolute;right:-80px;top:-90px;width:260px;height:260px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.30),rgba(245,158,11,.24),rgba(124,58,237,.18));animation:nsSpin 20s linear infinite;opacity:.55}
@keyframes nsSpin{to{transform:rotate(360deg)}}
.ns-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
.ns-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:11px}
.ns-title{margin:0;color:#fff;font-size:32px;font-weight:1000;letter-spacing:-.045em}
.ns-sub{margin-top:8px;color:#cbd5e1;font-size:14px}
.ns-actions{display:flex;gap:10px;flex-wrap:wrap}
.ns-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:15px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;transition:.2s ease}
.ns-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.ns-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#f59e0b);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.ns-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.ns-stat{position:relative;overflow:hidden;border-radius:23px;padding:19px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.54));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.ns-stat:after{content:"";position:absolute;right:-45px;top:-50px;width:140px;height:140px;border-radius:999px;background:#3b82f6;opacity:.16}
.ns-stat.green:after{background:#22c55e}.ns-stat.yellow:after{background:#f59e0b}.ns-stat.purple:after{background:#8b5cf6}
.ns-stat-icon{width:40px;height:40px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(148,163,184,.10);font-size:18px;margin-bottom:15px}
.ns-stat-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.ns-stat-value{color:white;font-size:30px;font-weight:1000;margin-top:6px;letter-spacing:-.035em}
.ns-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:16px}
.ns-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.ns-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:17px 19px;border-bottom:1px solid rgba(148,163,184,.10)}
.ns-card-title{display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.ns-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.ns-filter{display:flex;gap:10px;flex-wrap:wrap}
.ns-input,.ns-select{height:42px;border-radius:14px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.68);color:white;padding:0 13px;outline:none}
.ns-filter-btn{height:42px;border:0;border-radius:14px;padding:0 15px;color:white;font-size:12px;font-weight:950;background:linear-gradient(135deg,#2563eb,#f59e0b);cursor:pointer}
.ns-table-wrap{overflow-x:auto}
.ns-table{width:100%;border-collapse:collapse}
.ns-table th,.ns-table td{padding:15px 17px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.ns-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.ns-table td{color:#dbeafe}
.ns-table tbody tr{transition:.18s ease}
.ns-table tbody tr:hover{background:rgba(37,99,235,.07)}
.ns-main{color:white;font-weight:950}
.ns-muted{color:#94a3b8;font-size:12px;margin-top:2px}
.ns-link{color:#60a5fa;text-decoration:none;font-weight:950}
.ns-badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.ns-badge.active{color:#86efac;background:rgba(34,197,94,.14)}
.ns-badge.suspended,.ns-badge.expired{color:#fca5a5;background:rgba(239,68,68,.13)}
.ns-badge.pending{color:#fde68a;background:rgba(245,158,11,.13)}
.ns-row-actions{display:flex;gap:8px;flex-wrap:wrap}
.ns-small-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:11px;padding:8px 10px;font-size:11px;font-weight:950;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.65);color:#e5e7eb;cursor:pointer}
.ns-small-btn.blue{color:#93c5fd}.ns-small-btn.green{color:#86efac}.ns-small-btn.purple{color:#c4b5fd}.ns-small-btn.yellow{color:#fde68a}.ns-small-btn.red{color:#fca5a5}
.ns-form-inline{display:inline-flex;margin:0}
.ns-empty{padding:34px;text-align:center;color:#94a3b8}
.ns-pagination{padding:16px 19px}
.ns-info{padding:17px}
.ns-info-item{padding:14px;border-radius:18px;border:1px solid rgba(148,163,184,.12);background:rgba(2,6,23,.24);margin-bottom:11px}
.ns-info-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.ns-info-value{color:white;font-size:15px;font-weight:950;margin-top:6px;word-break:break-all}
.ns-copy{margin-top:9px;border:0;border-radius:11px;padding:8px 10px;background:rgba(37,99,235,.18);color:#bfdbfe;font-size:11px;font-weight:950;cursor:pointer}
.ns-side-link{display:flex;align-items:center;justify-content:space-between;text-decoration:none;padding:13px 14px;border-radius:16px;border:1px solid rgba(148,163,184,.12);background:rgba(2,6,23,.24);color:#e5e7eb;font-weight:950;font-size:12px;margin-bottom:10px}
.ns-side-link:hover{border-color:rgba(96,165,250,.32);transform:translateY(-1px)}
@media(max-width:1200px){.ns-grid{grid-template-columns:1fr}.ns-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.ns-stats{grid-template-columns:1fr}.ns-title{font-size:27px}.ns-filter{width:100%}.ns-input,.ns-select{width:100%}}
</style>

<div class="ns-page">

    <section class="ns-hero">
        <div class="ns-hero-inner">
            <div>
                <div class="ns-pill">🖥️ VPS Cloud</div>
                <h1 class="ns-title">VPS Services</h1>
                <div class="ns-sub">Kelola layanan VPS, IP publik, akses SSH, expired, dan status server client.</div>
            </div>

            <div class="ns-actions">
                @if($createRoute)
                    <a href="{{ route($createRoute) }}" class="ns-btn primary">+ Tambah VPS</a>
                @endif
                <a href="{{ $url('packages.index') }}" class="ns-btn">📦 Packages</a>
                <a href="{{ $url('admin.tickets.create') }}" class="ns-btn">🎫 Tiket</a>
            </div>
        </div>
    </section>

    <section class="ns-stats">
        <div class="ns-stat">
            <div class="ns-stat-icon">🖥️</div>
            <div class="ns-stat-label">Total VPS</div>
            <div class="ns-stat-value">{{ $totalVps }}</div>
        </div>

        <div class="ns-stat green">
            <div class="ns-stat-icon">✅</div>
            <div class="ns-stat-label">Active</div>
            <div class="ns-stat-value">{{ $activeVps }}</div>
        </div>

        <div class="ns-stat yellow">
            <div class="ns-stat-icon">⏳</div>
            <div class="ns-stat-label">Expired 7 Hari</div>
            <div class="ns-stat-value">{{ $expiredSoon }}</div>
        </div>

        <div class="ns-stat purple">
            <div class="ns-stat-icon">⛔</div>
            <div class="ns-stat-label">Suspended</div>
            <div class="ns-stat-value">{{ $suspendedVps }}</div>
        </div>
    </section>

    <section class="ns-grid">

        <div class="ns-card">
            <div class="ns-card-head">
                <div class="ns-card-title">
                    <span class="ns-dot"></span>
                    Daftar VPS
                </div>

                <form method="GET" action="{{ $url('vps-services.index') }}" class="ns-filter">
                    <input type="text" name="q" value="{{ request('q') }}" class="ns-input" placeholder="Cari hostname / IP / customer...">

                    <select name="status" class="ns-select">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                    </select>

                    <button class="ns-filter-btn" type="submit">Filter</button>

                    @if(request('q') || request('status'))
                        <a href="{{ $url('vps-services.index') }}" class="ns-small-btn">Reset</a>
                    @endif
                </form>
            </div>

            <div class="ns-table-wrap">
                <table class="ns-table">
                    <thead>
                        <tr>
                            <th>VPS</th>
                            <th>Customer</th>
                            <th>Resource</th>
                            <th>Status</th>
                            <th>Expired</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vpsServices as $vps)
                            @php
                                $name = $vps->name ?? $vps->hostname ?? $vps->server_name ?? 'vps-'.$vps->id;
                                $ip = $vps->ip_address ?? $vps->main_ip ?? $vps->public_ip ?? '-';
                                $os = $vps->os ?? $vps->operating_system ?? $vps->template ?? '-';
                                $cpu = $vps->cpu ?? $vps->vcpu ?? $vps->cpu_core ?? $vps->cores ?? '-';
                                $ram = $vps->ram ?? $vps->memory ?? '-';
                                $disk = $vps->disk ?? $vps->storage ?? '-';
                                $status = strtolower($vps->status ?? 'active');
                                $customerId = $vps->customer_id ?? null;
                                $userId = $vps->user_id ?? null;
                                $resolvedUserId = $userId ?: $userIdFromCustomer($customerId);
                            @endphp

                            <tr>
                                <td>
                                    @if($showRoute)
                                        <a href="{{ route($showRoute, $vps->id) }}" class="ns-link">{{ $name }}</a>
                                    @else
                                        <div class="ns-main">{{ $name }}</div>
                                    @endif
                                    <div class="ns-muted">{{ $ip }} • {{ $os }}</div>
                                </td>

                                <td>
                                    <div class="ns-main">{{ $customerName($customerId, $userId) }}</div>
                                    @if($customerId)
                                        <div class="ns-muted">Customer ID: {{ $customerId }}</div>
                                    @endif
                                </td>

                                <td>
                                    <div class="ns-main">{{ $cpu }} CPU / {{ $ram }} RAM</div>
                                    <div class="ns-muted">{{ $disk }} Disk</div>
                                </td>

                                <td>
                                    <span class="{{ $statusClass($status) }}">{{ $status }}</span>
                                </td>

                                <td>{{ $date($vps->expired_at ?? $vps->expires_at ?? null) }}</td>

                                <td>
                                    <div class="ns-row-actions">
                                        @if($resolvedUserId && Route::has('admin.client-history.show'))
                                            <a href="{{ route('admin.client-history.show', $resolvedUserId) }}" class="ns-small-btn purple">360</a>
                                        @elseif($customerId && Route::has('admin.client-history.customer'))
                                            <a href="{{ route('admin.client-history.customer', $customerId) }}" class="ns-small-btn purple">360</a>
                                        @endif

                                        @if($showRoute)
                                            <a href="{{ route($showRoute, $vps->id) }}" class="ns-small-btn blue">Detail</a>
                                        @endif

                                        @if($editRoute)
                                            <a href="{{ route($editRoute, $vps->id) }}" class="ns-small-btn">Edit</a>
                                        @endif

                                        @if($status === 'active' && $suspendRoute)
                                            <form action="{{ route($suspendRoute, $vps->id) }}" method="POST" class="ns-form-inline">
                                                @csrf
                                                <button type="submit" class="ns-small-btn yellow" onclick="return confirm('Suspend VPS ini?')">Suspend</button>
                                            </form>
                                        @elseif(in_array($status, ['suspended', 'disabled', 'stopped']) && $unsuspendRoute)
                                            <form action="{{ route($unsuspendRoute, $vps->id) }}" method="POST" class="ns-form-inline">
                                                @csrf
                                                <button type="submit" class="ns-small-btn green" onclick="return confirm('Aktifkan kembali VPS ini?')">Unsuspend</button>
                                            </form>
                                        @endif

                                        @if($terminateRoute)
                                            <form action="{{ route($terminateRoute, $vps->id) }}" method="POST" class="ns-form-inline">
                                                @csrf
                                                <button type="submit" class="ns-small-btn red" onclick="return confirm('Terminate VPS ini?')">Terminate</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="ns-empty">Belum ada VPS service ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($vpsServices, 'links'))
                <div class="ns-pagination">
                    {{ $vpsServices->links() }}
                </div>
            @endif
        </div>

        <aside class="ns-card">
            <div class="ns-card-head">
                <div class="ns-card-title">
                    <span class="ns-dot" style="background:#f59e0b;"></span>
                    VPS Summary
                </div>
            </div>

            <div class="ns-info">
                <div class="ns-info-item">
                    <div class="ns-info-label">Total vCPU</div>
                    <div class="ns-info-value">{{ $totalCpu ?: '-' }}</div>
                </div>

                <div class="ns-info-item">
                    <div class="ns-info-label">Total RAM</div>
                    <div class="ns-info-value">{{ $totalRam ?: '-' }}</div>
                </div>

                <div class="ns-info-item">
                    <div class="ns-info-label">Default Login</div>
                    <div class="ns-info-value">SSH root@IP</div>
                    <button type="button" class="ns-copy" data-copy="ssh root@IP_PUBLIC">Copy SSH Format</button>
                </div>

                <a href="{{ $url('packages.index') }}" class="ns-side-link">
                    <span>Kelola Paket VPS</span>
                    <span>↗</span>
                </a>

                <a href="{{ $url('admin.tickets.index') }}" class="ns-side-link">
                    <span>Support Tickets</span>
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
