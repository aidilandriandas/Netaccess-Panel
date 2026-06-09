@extends('layouts.app')
@section('title', 'Admin Users')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $hasUsers = Schema::hasTable('users');

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

    $q = request('q');
    $roleFilter = request('role');

    if ($hasUsers) {
        $query = DB::table('users');

        if ($q) {
            $query->where(function ($w) use ($q) {
                if (Schema::hasColumn('users', 'name')) {
                    $w->orWhere('name', 'like', "%{$q}%");
                }

                if (Schema::hasColumn('users', 'email')) {
                    $w->orWhere('email', 'like', "%{$q}%");
                }

                if (Schema::hasColumn('users', 'role')) {
                    $w->orWhere('role', 'like', "%{$q}%");
                }
            });
        }

        if ($roleFilter && Schema::hasColumn('users', 'role')) {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderByDesc('id')->paginate(15)->appends(request()->query());
    } else {
        $users = collect();
    }

    $totalUsers = $hasUsers ? DB::table('users')->count() : 0;

    $adminUsers = $hasUsers && Schema::hasColumn('users', 'role')
        ? DB::table('users')->whereIn('role', ['admin', 'superadmin', 'staff'])->count()
        : 0;

    $clientUsers = $hasUsers && Schema::hasColumn('users', 'role')
        ? DB::table('users')->where('role', 'client')->count()
        : max($totalUsers - $adminUsers, 0);

    $verifiedUsers = $hasUsers && Schema::hasColumn('users', 'email_verified_at')
        ? DB::table('users')->whereNotNull('email_verified_at')->count()
        : 0;

    $createRoute = $firstRoute(['admin-users.create', 'admin.users.create', 'users.create']);
    $showRoute = $firstRoute(['admin-users.show', 'admin.users.show', 'users.show']);
    $editRoute = $firstRoute(['admin-users.edit', 'admin.users.edit', 'users.edit']);
    $destroyRoute = $firstRoute(['admin-users.destroy', 'admin.users.destroy', 'users.destroy']);

    $date = function ($value) {
        if (!$value) return '-';
        try {
            return Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $roleClass = function ($role) {
        $role = strtolower((string) ($role ?: 'client'));

        return match ($role) {
            'admin', 'superadmin' => 'au-role admin',
            'staff' => 'au-role staff',
            'client', 'customer' => 'au-role client',
            default => 'au-role other',
        };
    };
@endphp

<style>
.au-page{display:flex;flex-direction:column;gap:16px}
.au-hero{position:relative;overflow:hidden;border-radius:26px;padding:22px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 20px 55px rgba(0,0,0,.22)}
.au-hero:before{content:"";position:absolute;right:-90px;top:-110px;width:280px;height:280px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.34),rgba(124,58,237,.26),rgba(14,165,233,.13));animation:auSpin 20s linear infinite;opacity:.55}
@keyframes auSpin{to{transform:rotate(360deg)}}
.au-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.au-pill{display:inline-flex;padding:7px 12px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:10px}
.au-title{margin:0;color:#fff;font-size:31px;font-weight:1000;letter-spacing:-.045em}
.au-sub{margin-top:7px;color:#cbd5e1;font-size:13px}
.au-actions{display:flex;gap:10px;flex-wrap:wrap}
.au-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:0;border-radius:14px;min-height:40px;padding:0 14px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;cursor:pointer;transition:.2s ease}
.au-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.au-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.au-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.au-stat{position:relative;overflow:hidden;border-radius:22px;padding:17px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.74),rgba(15,23,42,.52));box-shadow:0 16px 38px rgba(0,0,0,.16)}
.au-stat:after{content:"";position:absolute;right:-45px;top:-45px;width:130px;height:130px;border-radius:999px;background:#3b82f6;opacity:.16}
.au-stat.green:after{background:#22c55e}.au-stat.yellow:after{background:#f59e0b}.au-stat.purple:after{background:#8b5cf6}
.au-stat-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.au-stat-value{color:white;font-size:29px;font-weight:1000;margin-top:5px}
.au-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.au-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:15px 17px;border-bottom:1px solid rgba(148,163,184,.10)}
.au-head-title{display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.au-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.au-filter{display:flex;gap:10px;flex-wrap:wrap}
.au-input,.au-select{height:40px;border-radius:14px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.68);color:white;padding:0 13px;outline:none}
.au-filter-btn{height:40px;border:0;border-radius:14px;padding:0 14px;color:white;font-size:12px;font-weight:950;background:linear-gradient(135deg,#2563eb,#7c3aed);cursor:pointer}
.au-table-wrap{overflow-x:auto}
.au-table{width:100%;border-collapse:collapse}
.au-table th,.au-table td{padding:14px 16px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.au-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.au-table td{color:#dbeafe}
.au-table tbody tr:hover{background:rgba(37,99,235,.07)}
.au-user{display:flex;align-items:center;gap:11px}
.au-avatar{width:38px;height:38px;border-radius:15px;display:flex;align-items:center;justify-content:center;color:white;font-weight:1000;background:linear-gradient(135deg,rgba(37,99,235,.35),rgba(124,58,237,.25))}
.au-name{color:white;font-weight:950}
.au-email{color:#94a3b8;font-size:12px;margin-top:2px}
.au-role{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.au-role.admin{color:#c4b5fd;background:rgba(124,58,237,.13)}
.au-role.staff{color:#93c5fd;background:rgba(37,99,235,.13)}
.au-role.client{color:#86efac;background:rgba(34,197,94,.13)}
.au-role.other{color:#fde68a;background:rgba(245,158,11,.13)}
.au-small{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:11px;padding:8px 10px;font-size:11px;font-weight:950;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.65);color:#e5e7eb;cursor:pointer}
.au-small.blue{color:#93c5fd}.au-small.red{color:#fca5a5}.au-small.purple{color:#c4b5fd}
.au-row-actions{display:flex;gap:8px;flex-wrap:wrap}
.au-empty{padding:32px;text-align:center;color:#94a3b8}
.au-pagination{padding:15px 17px}
@media(max-width:1000px){.au-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.au-stats{grid-template-columns:1fr}.au-filter{width:100%}.au-input,.au-select{width:100%}.au-title{font-size:27px}}
</style>

<div class="au-page">

    <section class="au-hero">
        <div class="au-hero-inner">
            <div>
                <div class="au-pill">👤 Access Control</div>
                <h1 class="au-title">Admin Users</h1>
                <div class="au-sub">Kelola akun admin, staff, dan client login dari satu halaman.</div>
            </div>

            <div class="au-actions">
                @if($createRoute)
                    <a href="{{ route($createRoute) }}" class="au-btn primary">+ Tambah User</a>
                @endif
                <a href="{{ $url('settings.index') }}" class="au-btn">⚙️ Settings</a>
            </div>
        </div>
    </section>

    <section class="au-stats">
        <div class="au-stat">
            <div class="au-stat-label">Total User</div>
            <div class="au-stat-value">{{ $totalUsers }}</div>
        </div>

        <div class="au-stat purple">
            <div class="au-stat-label">Admin / Staff</div>
            <div class="au-stat-value">{{ $adminUsers }}</div>
        </div>

        <div class="au-stat green">
            <div class="au-stat-label">Client</div>
            <div class="au-stat-value">{{ $clientUsers }}</div>
        </div>

        <div class="au-stat yellow">
            <div class="au-stat-label">Verified</div>
            <div class="au-stat-value">{{ $verifiedUsers }}</div>
        </div>
    </section>

    <section class="au-card">
        <div class="au-head">
            <div class="au-head-title">
                <span class="au-dot"></span>
                User List
            </div>

            <form method="GET" action="{{ $url('admin-users.index') }}" class="au-filter">
                <input type="text" name="q" value="{{ request('q') }}" class="au-input" placeholder="Cari nama / email...">

                <select name="role" class="au-select">
                    <option value="">Semua Role</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="staff" @selected(request('role') === 'staff')>Staff</option>
                    <option value="client" @selected(request('role') === 'client')>Client</option>
                </select>

                <button class="au-filter-btn" type="submit">Filter</button>

                @if(request('q') || request('role'))
                    <a href="{{ $url('admin-users.index') }}" class="au-small">Reset</a>
                @endif
            </form>
        </div>

        <div class="au-table-wrap">
            <table class="au-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Verified</th>
                        <th>Joined</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        @php
                            $name = $user->name ?? 'User-'.$user->id;
                            $email = $user->email ?? '-';
                            $role = $user->role ?? 'client';
                            $verified = !empty($user->email_verified_at);
                        @endphp

                        <tr>
                            <td>
                                <div class="au-user">
                                    <div class="au-avatar">{{ strtoupper(substr($name, 0, 1)) }}</div>
                                    <div>
                                        <div class="au-name">{{ $name }}</div>
                                        <div class="au-email">{{ $email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td><span class="{{ $roleClass($role) }}">{{ $role }}</span></td>

                            <td>{{ $verified ? 'Yes' : 'No' }}</td>

                            <td>{{ $date($user->created_at ?? null) }}</td>

                            <td>
                                <div class="au-row-actions">
                                    @if(Route::has('admin.client-history.show'))
                                        <a href="{{ route('admin.client-history.show', $user->id) }}" class="au-small purple">360</a>
                                    @endif

                                    @if($showRoute)
                                        <a href="{{ route($showRoute, $user->id) }}" class="au-small blue">Detail</a>
                                    @endif

                                    @if($editRoute)
                                        <a href="{{ route($editRoute, $user->id) }}" class="au-small">Edit</a>
                                    @endif

                                    @if($destroyRoute)
                                        <form action="{{ route($destroyRoute, $user->id) }}" method="POST" style="display:inline-flex;margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="au-small red" onclick="return confirm('Hapus user ini?')">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="au-empty">Belum ada user ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($users, 'links'))
            <div class="au-pagination">
                {{ $users->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
