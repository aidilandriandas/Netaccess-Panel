@extends('layouts.app')
@section('title', 'Support Tickets')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $hasTickets = Schema::hasTable('support_tickets');

    $url = function ($name, $param = null) {
        try {
            if (!Route::has($name)) return '#';
            return $param ? route($name, $param) : route($name);
        } catch (\Throwable $e) {
            return '#';
        }
    };

    $date = function ($value, $format = 'd M Y H:i') {
        if (!$value) return '-';
        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $customerName = function ($userId = null) {
        try {
            if ($userId && Schema::hasTable('users')) {
                $name = Schema::hasColumn('users', 'name')
                    ? DB::table('users')->where('id', $userId)->value('name')
                    : null;

                if ($name) return $name;

                $email = Schema::hasColumn('users', 'email')
                    ? DB::table('users')->where('id', $userId)->value('email')
                    : null;

                if ($email) return $email;
            }
        } catch (\Throwable $e) {}

        return '-';
    };

    $q = request('q');
    $statusFilter = request('status');
    $priorityFilter = request('priority');
    $categoryFilter = request('category');

    if ($hasTickets) {
        $query = DB::table('support_tickets');

        if ($q) {
            $query->where(function ($w) use ($q) {
                foreach (['ticket_number', 'subject', 'category', 'service_label'] as $column) {
                    if (Schema::hasColumn('support_tickets', $column)) {
                        $w->orWhere($column, 'like', "%{$q}%");
                    }
                }

                if (Schema::hasColumn('support_tickets', 'user_id') && Schema::hasTable('users')) {
                    $userIds = DB::table('users')
                        ->when(Schema::hasColumn('users', 'name'), fn ($u) => $u->orWhere('name', 'like', "%{$q}%"))
                        ->when(Schema::hasColumn('users', 'email'), fn ($u) => $u->orWhere('email', 'like', "%{$q}%"))
                        ->pluck('id');

                    if ($userIds->count()) {
                        $w->orWhereIn('user_id', $userIds);
                    }
                }
            });
        }

        if ($statusFilter && Schema::hasColumn('support_tickets', 'status')) {
            $query->where('status', $statusFilter);
        }

        if ($priorityFilter && Schema::hasColumn('support_tickets', 'priority')) {
            $query->where('priority', $priorityFilter);
        }

        if ($categoryFilter && Schema::hasColumn('support_tickets', 'category')) {
            $query->where('category', $categoryFilter);
        }

        $tickets = $query->orderByDesc('id')->paginate(15)->appends(request()->query());
    } else {
        $tickets = collect();
    }

    $totalTickets = $hasTickets ? DB::table('support_tickets')->count() : 0;

    $openTickets = $hasTickets && Schema::hasColumn('support_tickets', 'status')
        ? DB::table('support_tickets')->where('status', 'open')->count()
        : 0;

    $answeredTickets = $hasTickets && Schema::hasColumn('support_tickets', 'status')
        ? DB::table('support_tickets')->where('status', 'answered')->count()
        : 0;

    $closedTickets = $hasTickets && Schema::hasColumn('support_tickets', 'status')
        ? DB::table('support_tickets')->where('status', 'closed')->count()
        : 0;

    $urgentTickets = $hasTickets && Schema::hasColumn('support_tickets', 'priority')
        ? DB::table('support_tickets')->whereIn('priority', ['high', 'urgent'])->count()
        : 0;

    $statusClass = function ($status) {
        $status = strtolower((string) ($status ?: 'open'));

        return match ($status) {
            'open' => 'nt-badge open',
            'answered' => 'nt-badge answered',
            'closed' => 'nt-badge closed',
            default => 'nt-badge pending',
        };
    };

    $priorityClass = function ($priority) {
        $priority = strtolower((string) ($priority ?: 'normal'));

        return match ($priority) {
            'urgent', 'high' => 'nt-priority high',
            'medium' => 'nt-priority medium',
            'low' => 'nt-priority low',
            default => 'nt-priority normal',
        };
    };
@endphp

<style>
.nt-page{display:flex;flex-direction:column;gap:18px}
.nt-hero{position:relative;overflow:hidden;border-radius:28px;padding:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 22px 60px rgba(0,0,0,.22)}
.nt-hero:before{content:"";position:absolute;right:-80px;top:-90px;width:260px;height:260px;border-radius:999px;background:conic-gradient(from 180deg,rgba(124,58,237,.30),rgba(37,99,235,.24),rgba(14,165,233,.14));animation:ntSpin 20s linear infinite;opacity:.55}
@keyframes ntSpin{to{transform:rotate(360deg)}}
.nt-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
.nt-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:11px}
.nt-title{margin:0;color:#fff;font-size:32px;font-weight:1000;letter-spacing:-.045em}
.nt-sub{margin-top:8px;color:#cbd5e1;font-size:14px}
.nt-actions{display:flex;gap:10px;flex-wrap:wrap}
.nt-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:15px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;transition:.2s ease}
.nt-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.nt-btn.primary{color:white;background:linear-gradient(135deg,#7c3aed,#2563eb);border-color:transparent;box-shadow:0 14px 30px rgba(124,58,237,.25)}
.nt-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.nt-stat{position:relative;overflow:hidden;border-radius:23px;padding:19px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.54));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nt-stat:after{content:"";position:absolute;right:-45px;top:-50px;width:140px;height:140px;border-radius:999px;background:#3b82f6;opacity:.16}
.nt-stat.green:after{background:#22c55e}.nt-stat.yellow:after{background:#f59e0b}.nt-stat.purple:after{background:#8b5cf6}
.nt-stat-icon{width:40px;height:40px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(148,163,184,.10);font-size:18px;margin-bottom:15px}
.nt-stat-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.nt-stat-value{color:white;font-size:30px;font-weight:1000;margin-top:6px;letter-spacing:-.035em}
.nt-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.nt-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:17px 19px;border-bottom:1px solid rgba(148,163,184,.10)}
.nt-card-title{display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.nt-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.nt-filter{display:flex;gap:10px;flex-wrap:wrap}
.nt-input,.nt-select{height:42px;border-radius:14px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.68);color:white;padding:0 13px;outline:none}
.nt-filter-btn{height:42px;border:0;border-radius:14px;padding:0 15px;color:white;font-size:12px;font-weight:950;background:linear-gradient(135deg,#7c3aed,#2563eb);cursor:pointer}
.nt-table-wrap{overflow-x:auto}
.nt-table{width:100%;border-collapse:collapse}
.nt-table th,.nt-table td{padding:15px 17px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.nt-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.nt-table td{color:#dbeafe}
.nt-table tbody tr{transition:.18s ease}
.nt-table tbody tr:hover{background:rgba(37,99,235,.07)}
.nt-main{color:white;font-weight:950}
.nt-muted{color:#94a3b8;font-size:12px;margin-top:3px}
.nt-link{color:#60a5fa;text-decoration:none;font-weight:950}
.nt-badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.nt-badge.open{color:#fde68a;background:rgba(245,158,11,.13)}
.nt-badge.answered{color:#86efac;background:rgba(34,197,94,.14)}
.nt-badge.closed{color:#cbd5e1;background:rgba(100,116,139,.16)}
.nt-badge.pending{color:#bfdbfe;background:rgba(37,99,235,.12)}
.nt-priority{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.nt-priority.high{color:#fca5a5;background:rgba(239,68,68,.13)}
.nt-priority.medium{color:#fde68a;background:rgba(245,158,11,.13)}
.nt-priority.low{color:#93c5fd;background:rgba(37,99,235,.13)}
.nt-priority.normal{color:#c4b5fd;background:rgba(124,58,237,.13)}
.nt-row-actions{display:flex;gap:8px;flex-wrap:wrap}
.nt-small-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:11px;padding:8px 10px;font-size:11px;font-weight:950;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.65);color:#e5e7eb;cursor:pointer}
.nt-small-btn.blue{color:#93c5fd}.nt-small-btn.green{color:#86efac}.nt-small-btn.purple{color:#c4b5fd}
.nt-empty{padding:34px;text-align:center;color:#94a3b8}
.nt-pagination{padding:16px 19px}
@media(max-width:1100px){.nt-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.nt-stats{grid-template-columns:1fr}.nt-title{font-size:27px}.nt-filter{width:100%}.nt-input,.nt-select{width:100%}}
</style>

<div class="nt-page">

    <section class="nt-hero">
        <div class="nt-hero-inner">
            <div>
                <div class="nt-pill">🎫 Support Center</div>
                <h1 class="nt-title">Support Tickets</h1>
                <div class="nt-sub">Pantau tiket client, issue billing, VPN, hosting, VPS, dan komunikasi support.</div>
            </div>

            <div class="nt-actions">
                <a href="{{ $url('admin.tickets.create') }}" class="nt-btn primary">+ Buat Tiket</a>
                <a href="{{ $url('customers.index') }}" class="nt-btn">👥 Customers</a>
            </div>
        </div>
    </section>

    <section class="nt-stats">
        <div class="nt-stat">
            <div class="nt-stat-icon">🎫</div>
            <div class="nt-stat-label">Total Ticket</div>
            <div class="nt-stat-value">{{ $totalTickets }}</div>
        </div>

        <div class="nt-stat yellow">
            <div class="nt-stat-icon">🟡</div>
            <div class="nt-stat-label">Open</div>
            <div class="nt-stat-value">{{ $openTickets }}</div>
        </div>

        <div class="nt-stat green">
            <div class="nt-stat-icon">✅</div>
            <div class="nt-stat-label">Answered</div>
            <div class="nt-stat-value">{{ $answeredTickets }}</div>
        </div>

        <div class="nt-stat purple">
            <div class="nt-stat-icon">🔥</div>
            <div class="nt-stat-label">Urgent / High</div>
            <div class="nt-stat-value">{{ $urgentTickets }}</div>
        </div>
    </section>

    <section class="nt-card">
        <div class="nt-card-head">
            <div class="nt-card-title">
                <span class="nt-dot"></span>
                Daftar Ticket
            </div>

            <form method="GET" action="{{ $url('admin.tickets.index') }}" class="nt-filter">
                <input type="text" name="q" value="{{ request('q') }}" class="nt-input" placeholder="Cari ticket / subject / client...">

                <select name="status" class="nt-select">
                    <option value="">Semua Status</option>
                    <option value="open" @selected(request('status') === 'open')>Open</option>
                    <option value="answered" @selected(request('status') === 'answered')>Answered</option>
                    <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                </select>

                <select name="priority" class="nt-select">
                    <option value="">Semua Priority</option>
                    <option value="low" @selected(request('priority') === 'low')>Low</option>
                    <option value="normal" @selected(request('priority') === 'normal')>Normal</option>
                    <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
                    <option value="high" @selected(request('priority') === 'high')>High</option>
                    <option value="urgent" @selected(request('priority') === 'urgent')>Urgent</option>
                </select>

                <select name="category" class="nt-select">
                    <option value="">Semua Category</option>
                    <option value="billing" @selected(request('category') === 'billing')>Billing</option>
                    <option value="vpn" @selected(request('category') === 'vpn')>VPN</option>
                    <option value="hosting" @selected(request('category') === 'hosting')>Hosting</option>
                    <option value="vps" @selected(request('category') === 'vps')>VPS</option>
                    <option value="technical" @selected(request('category') === 'technical')>Technical</option>
                </select>

                <button class="nt-filter-btn" type="submit">Filter</button>

                @if(request('q') || request('status') || request('priority') || request('category'))
                    <a href="{{ $url('admin.tickets.index') }}" class="nt-small-btn">Reset</a>
                @endif
            </form>
        </div>

        <div class="nt-table-wrap">
            <table class="nt-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Last Reply</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        @php
                            $number = $ticket->ticket_number ?? 'TICKET-'.$ticket->id;
                            $subject = $ticket->subject ?? '-';
                            $status = strtolower($ticket->status ?? 'open');
                            $priority = strtolower($ticket->priority ?? 'normal');
                            $service = $ticket->service_label ?? $ticket->service_type ?? '-';
                            $userId = $ticket->user_id ?? null;
                        @endphp

                        <tr>
                            <td>
                                <a href="{{ $url('admin.tickets.show', $ticket->id) }}" class="nt-link">
                                    {{ $number }}
                                </a>
                                <div class="nt-muted">{{ $subject }}</div>
                            </td>

                            <td>
                                <div class="nt-main">{{ $customerName($userId) }}</div>
                                @if($userId)
                                    <div class="nt-muted">User ID: {{ $userId }}</div>
                                @endif
                            </td>

                            <td>{{ $service }}</td>

                            <td><span class="{{ $statusClass($status) }}">{{ $status }}</span></td>

                            <td><span class="{{ $priorityClass($priority) }}">{{ $priority }}</span></td>

                            <td>{{ $date($ticket->last_reply_at ?? $ticket->updated_at ?? $ticket->created_at ?? null) }}</td>

                            <td>
                                <div class="nt-row-actions">
                                    @if($userId && Route::has('admin.client-history.show'))
                                        <a href="{{ route('admin.client-history.show', $userId) }}" class="nt-small-btn purple">360</a>
                                    @endif

                                    <a href="{{ $url('admin.tickets.show', $ticket->id) }}" class="nt-small-btn blue">Detail</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="nt-empty">Belum ada support ticket ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($tickets, 'links'))
            <div class="nt-pagination">
                {{ $tickets->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
