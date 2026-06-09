@extends('layouts.app')
@section('title', 'Customers')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;

    $hasCustomers = Schema::hasTable('customers');

    $url = function ($name, $param = null) {
        try {
            if (!Route::has($name)) return '#';
            return $param ? route($name, $param) : route($name);
        } catch (\Throwable $e) {
            return '#';
        }
    };

    $q = request('q');
    $statusFilter = request('status');

    $query = $hasCustomers ? DB::table('customers') : null;

    if ($query) {
        if ($q) {
            $query->where(function ($w) use ($q) {
                if (Schema::hasColumn('customers', 'name')) {
                    $w->orWhere('name', 'like', "%{$q}%");
                }

                if (Schema::hasColumn('customers', 'email')) {
                    $w->orWhere('email', 'like', "%{$q}%");
                }

                if (Schema::hasColumn('customers', 'phone')) {
                    $w->orWhere('phone', 'like', "%{$q}%");
                }
            });
        }

        if ($statusFilter && Schema::hasColumn('customers', 'status')) {
            $query->where('status', $statusFilter);
        }

        $customerRows = $query->orderByDesc('id')->paginate(15)->appends(request()->query());
    } else {
        $customerRows = collect();
    }

    $totalCustomers = $hasCustomers ? DB::table('customers')->count() : 0;
    $activeCustomers = $hasCustomers && Schema::hasColumn('customers', 'status')
        ? DB::table('customers')->where('status', 'active')->count()
        : $totalCustomers;

    $suspendedCustomers = $hasCustomers && Schema::hasColumn('customers', 'status')
        ? DB::table('customers')->where('status', 'suspended')->count()
        : 0;

    $personalCustomers = $hasCustomers && Schema::hasColumn('customers', 'type')
        ? DB::table('customers')->where('type', 'personal')->count()
        : 0;

    $statusBadge = function ($status) {
        $status = strtolower((string) ($status ?: 'active'));

        return match ($status) {
            'active' => 'nc-badge active',
            'suspended' => 'nc-badge suspended',
            'expired' => 'nc-badge expired',
            default => 'nc-badge pending',
        };
    };
@endphp

<style>
.nc-page{display:flex;flex-direction:column;gap:18px}
.nc-hero{position:relative;overflow:hidden;border-radius:28px;padding:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 22px 60px rgba(0,0,0,.22)}
.nc-hero:before{content:"";position:absolute;right:-80px;top:-90px;width:260px;height:260px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.30),rgba(124,58,237,.28),rgba(14,165,233,.14));animation:ncSpin 20s linear infinite;opacity:.55}
@keyframes ncSpin{to{transform:rotate(360deg)}}
.nc-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
.nc-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:11px}
.nc-title{margin:0;color:#fff;font-size:32px;font-weight:1000;letter-spacing:-.045em}
.nc-sub{margin-top:8px;color:#cbd5e1;font-size:14px}
.nc-actions{display:flex;gap:10px;flex-wrap:wrap}
.nc-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:15px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;transition:.2s ease}
.nc-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.nc-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.nc-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.nc-stat{position:relative;overflow:hidden;border-radius:23px;padding:19px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.54));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nc-stat:after{content:"";position:absolute;right:-45px;top:-50px;width:140px;height:140px;border-radius:999px;background:#3b82f6;opacity:.16}
.nc-stat.green:after{background:#22c55e}.nc-stat.yellow:after{background:#f59e0b}.nc-stat.purple:after{background:#8b5cf6}
.nc-stat-icon{width:40px;height:40px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(148,163,184,.10);font-size:18px;margin-bottom:15px}
.nc-stat-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.nc-stat-value{color:white;font-size:30px;font-weight:1000;margin-top:6px;letter-spacing:-.035em}
.nc-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nc-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:17px 19px;border-bottom:1px solid rgba(148,163,184,.10)}
.nc-card-title{display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.nc-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.nc-filter{display:flex;gap:10px;flex-wrap:wrap}
.nc-input,.nc-select{height:42px;border-radius:14px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.68);color:white;padding:0 13px;outline:none}
.nc-filter-btn{height:42px;border:0;border-radius:14px;padding:0 15px;color:white;font-size:12px;font-weight:950;background:linear-gradient(135deg,#2563eb,#7c3aed);cursor:pointer}
.nc-table-wrap{overflow-x:auto}
.nc-table{width:100%;border-collapse:collapse}
.nc-table th,.nc-table td{padding:15px 17px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.nc-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.nc-table td{color:#dbeafe}
.nc-table tbody tr{transition:.18s ease}
.nc-table tbody tr:hover{background:rgba(37,99,235,.07)}
.nc-user{display:flex;align-items:center;gap:12px}
.nc-avatar{width:38px;height:38px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(37,99,235,.28),rgba(124,58,237,.20));color:white;font-weight:1000}
.nc-name{color:white;font-weight:950}
.nc-email{color:#94a3b8;font-size:12px;margin-top:2px}
.nc-badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.nc-badge.active{color:#86efac;background:rgba(34,197,94,.14)}
.nc-badge.suspended,.nc-badge.expired{color:#fca5a5;background:rgba(239,68,68,.13)}
.nc-badge.pending{color:#fde68a;background:rgba(245,158,11,.13)}
.nc-row-actions{display:flex;gap:8px;flex-wrap:wrap}
.nc-small-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:11px;padding:8px 10px;font-size:11px;font-weight:950;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.65);color:#e5e7eb}
.nc-small-btn.blue{color:#93c5fd}.nc-small-btn.green{color:#86efac}.nc-small-btn.purple{color:#c4b5fd}
.nc-empty{padding:34px;text-align:center;color:#94a3b8}
.nc-pagination{padding:16px 19px}
@media(max-width:1100px){.nc-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.nc-stats{grid-template-columns:1fr}.nc-title{font-size:27px}.nc-filter{width:100%}.nc-input,.nc-select{width:100%}}
</style>

<div class="nc-page">

    <section class="nc-hero">
        <div class="nc-hero-inner">
            <div>
                <div class="nc-pill">👥 Customer Center</div>
                <h1 class="nc-title">Customers</h1>
                <div class="nc-sub">Kelola data client, akses Client 360, invoice, tiket, dan layanan dari satu halaman.</div>
            </div>

            <div class="nc-actions">
                <a href="{{ $url('customers.create') }}" class="nc-btn primary">+ Tambah Customer</a>
                <a href="{{ $url('admin.tickets.create') }}" class="nc-btn">🎫 Buat Tiket</a>
            </div>
        </div>
    </section>

    <section class="nc-stats">
        <div class="nc-stat">
            <div class="nc-stat-icon">👥</div>
            <div class="nc-stat-label">Total Customer</div>
            <div class="nc-stat-value">{{ $totalCustomers }}</div>
        </div>

        <div class="nc-stat green">
            <div class="nc-stat-icon">✅</div>
            <div class="nc-stat-label">Customer Aktif</div>
            <div class="nc-stat-value">{{ $activeCustomers }}</div>
        </div>

        <div class="nc-stat yellow">
            <div class="nc-stat-icon">👤</div>
            <div class="nc-stat-label">Personal</div>
            <div class="nc-stat-value">{{ $personalCustomers }}</div>
        </div>

        <div class="nc-stat purple">
            <div class="nc-stat-icon">⛔</div>
            <div class="nc-stat-label">Suspended</div>
            <div class="nc-stat-value">{{ $suspendedCustomers }}</div>
        </div>
    </section>

    <section class="nc-card">
        <div class="nc-card-head">
            <div class="nc-card-title">
                <span class="nc-dot"></span>
                Daftar Customer
            </div>

            <form method="GET" action="{{ $url('customers.index') }}" class="nc-filter">
                <input type="text" name="q" value="{{ request('q') }}" class="nc-input" placeholder="Cari nama, email, phone...">

                <select name="status" class="nc-select">
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                    <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                </select>

                <button class="nc-filter-btn" type="submit">Filter</button>

                @if(request('q') || request('status'))
                    <a href="{{ $url('customers.index') }}" class="nc-small-btn">Reset</a>
                @endif
            </form>
        </div>

        <div class="nc-table-wrap">
            <table class="nc-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerRows as $customer)
                        @php
                            $name = $customer->name ?? 'Customer-'.$customer->id;
                            $email = $customer->email ?? '-';
                            $phone = $customer->phone ?? '-';
                            $type = $customer->type ?? '-';
                            $status = $customer->status ?? 'active';

                            $userId = null;
                            if (Schema::hasColumn('customers', 'user_id') && !empty($customer->user_id)) {
                                $userId = $customer->user_id;
                            } elseif ($email !== '-' && Schema::hasTable('users')) {
                                $userId = DB::table('users')->where('email', $email)->value('id');
                            }

                            $wa = preg_replace('/[^0-9]/', '', $phone);
                            if (str_starts_with($wa, '0')) {
                                $wa = '62' . substr($wa, 1);
                            }
                        @endphp

                        <tr>
                            <td>
                                <div class="nc-user">
                                    <div class="nc-avatar">{{ strtoupper(substr($name, 0, 1)) }}</div>
                                    <div>
                                        <div class="nc-name">{{ $name }}</div>
                                        <div class="nc-email">{{ $email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $phone }}</td>
                            <td>{{ ucfirst($type) }}</td>
                            <td><span class="{{ $statusBadge($status) }}">{{ $status }}</span></td>
                            <td>{{ !empty($customer->created_at) ? \Carbon\Carbon::parse($customer->created_at)->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="nc-row-actions">
                                    @if($userId && Route::has('admin.client-history.show'))
                                        <a href="{{ route('admin.client-history.show', $userId) }}" class="nc-small-btn purple">360</a>
                                    @elseif(Route::has('admin.client-history.customer'))
                                        <a href="{{ route('admin.client-history.customer', $customer->id) }}" class="nc-small-btn purple">360</a>
                                    @endif

                                    <a href="{{ $url('customers.show', $customer->id) }}" class="nc-small-btn blue">Detail</a>

                                    @if(Route::has('customers.edit'))
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="nc-small-btn">Edit</a>
                                    @endif

                                    @if($wa)
                                        <a href="https://wa.me/{{ $wa }}" target="_blank" class="nc-small-btn green">WA</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="nc-empty">
                                Belum ada customer ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($customerRows, 'links'))
            <div class="nc-pagination">
                {{ $customerRows->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
