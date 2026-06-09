@extends('layouts.app')
@section('title', 'Invoices')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $hasInvoices = Schema::hasTable('invoices');

    $url = function ($name, $param = null) {
        try {
            if (!Route::has($name)) return '#';
            return $param ? route($name, $param) : route($name);
        } catch (\Throwable $e) {
            return '#';
        }
    };

    $routeFirst = function (array $names) {
        foreach ($names as $name) {
            if (Route::has($name)) return $name;
        }
        return null;
    };

    $money = fn ($value) => 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');

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

    $amountCol = $hasInvoices && Schema::hasColumn('invoices', 'total')
        ? 'total'
        : (($hasInvoices && Schema::hasColumn('invoices', 'amount')) ? 'amount' : null);

    $invoiceNoCol = $hasInvoices && Schema::hasColumn('invoices', 'invoice_number')
        ? 'invoice_number'
        : (($hasInvoices && Schema::hasColumn('invoices', 'number')) ? 'number' : null);

    $q = request('q');
    $statusFilter = request('status');

    if ($hasInvoices) {
        $query = DB::table('invoices');

        if ($q) {
            $query->where(function ($w) use ($q, $invoiceNoCol) {
                if ($invoiceNoCol) {
                    $w->orWhere($invoiceNoCol, 'like', "%{$q}%");
                }

                if (Schema::hasColumn('invoices', 'notes')) {
                    $w->orWhere('notes', 'like', "%{$q}%");
                }

                if (Schema::hasColumn('invoices', 'customer_id') && Schema::hasTable('customers')) {
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

        if ($statusFilter && Schema::hasColumn('invoices', 'status')) {
            $query->where('status', $statusFilter);
        }

        $invoices = $query->orderByDesc('id')->paginate(15)->appends(request()->query());
    } else {
        $invoices = collect();
    }

    $totalInvoices = $hasInvoices ? DB::table('invoices')->count() : 0;

    $paidInvoices = $hasInvoices && Schema::hasColumn('invoices', 'status')
        ? DB::table('invoices')->where('status', 'paid')->count()
        : 0;

    $unpaidInvoices = $hasInvoices && Schema::hasColumn('invoices', 'status')
        ? DB::table('invoices')->whereIn('status', ['unpaid', 'pending'])->count()
        : 0;

    $revenueThisMonth = 0;
    if ($hasInvoices && $amountCol && Schema::hasColumn('invoices', 'status')) {
        $revenueThisMonth = DB::table('invoices')
            ->where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum($amountCol);
    }

    $markPaidRoute = $routeFirst([
        'invoices.markPaid',
        'invoices.mark-paid',
        'invoices.paid',
        'invoices.pay',
        'invoices.mark-as-paid',
    ]);

    $statusClass = function ($status) {
        $status = strtolower((string) ($status ?: 'unpaid'));

        return match ($status) {
            'paid' => 'ni-badge paid',
            'unpaid' => 'ni-badge unpaid',
            'pending' => 'ni-badge pending',
            'cancelled', 'canceled' => 'ni-badge cancelled',
            default => 'ni-badge pending',
        };
    };
@endphp

<style>
.ni-page{display:flex;flex-direction:column;gap:18px}
.ni-hero{position:relative;overflow:hidden;border-radius:28px;padding:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 22px 60px rgba(0,0,0,.22)}
.ni-hero:before{content:"";position:absolute;right:-80px;top:-90px;width:260px;height:260px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.30),rgba(124,58,237,.28),rgba(14,165,233,.14));animation:niSpin 20s linear infinite;opacity:.55}
@keyframes niSpin{to{transform:rotate(360deg)}}
.ni-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
.ni-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:11px}
.ni-title{margin:0;color:#fff;font-size:32px;font-weight:1000;letter-spacing:-.045em}
.ni-sub{margin-top:8px;color:#cbd5e1;font-size:14px}
.ni-actions{display:flex;gap:10px;flex-wrap:wrap}
.ni-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:15px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;transition:.2s ease}
.ni-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.ni-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.ni-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.ni-stat{position:relative;overflow:hidden;border-radius:23px;padding:19px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.54));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.ni-stat:after{content:"";position:absolute;right:-45px;top:-50px;width:140px;height:140px;border-radius:999px;background:#3b82f6;opacity:.16}
.ni-stat.green:after{background:#22c55e}.ni-stat.yellow:after{background:#f59e0b}.ni-stat.purple:after{background:#8b5cf6}
.ni-stat-icon{width:40px;height:40px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(148,163,184,.10);font-size:18px;margin-bottom:15px}
.ni-stat-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.ni-stat-value{color:white;font-size:28px;font-weight:1000;margin-top:6px;letter-spacing:-.035em}
.ni-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.ni-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:17px 19px;border-bottom:1px solid rgba(148,163,184,.10)}
.ni-card-title{display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.ni-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.ni-filter{display:flex;gap:10px;flex-wrap:wrap}
.ni-input,.ni-select{height:42px;border-radius:14px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.68);color:white;padding:0 13px;outline:none}
.ni-filter-btn{height:42px;border:0;border-radius:14px;padding:0 15px;color:white;font-size:12px;font-weight:950;background:linear-gradient(135deg,#2563eb,#7c3aed);cursor:pointer}
.ni-table-wrap{overflow-x:auto}
.ni-table{width:100%;border-collapse:collapse}
.ni-table th,.ni-table td{padding:15px 17px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.ni-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.ni-table td{color:#dbeafe}
.ni-table tbody tr{transition:.18s ease}
.ni-table tbody tr:hover{background:rgba(37,99,235,.07)}
.ni-main{color:white;font-weight:950}
.ni-muted{color:#94a3b8;font-size:12px;margin-top:2px}
.ni-link{color:#60a5fa;text-decoration:none;font-weight:950}
.ni-badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.ni-badge.paid{color:#86efac;background:rgba(34,197,94,.14)}
.ni-badge.unpaid,.ni-badge.pending{color:#fde68a;background:rgba(245,158,11,.13)}
.ni-badge.cancelled{color:#cbd5e1;background:rgba(100,116,139,.16)}
.ni-row-actions{display:flex;gap:8px;flex-wrap:wrap}
.ni-small-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:11px;padding:8px 10px;font-size:11px;font-weight:950;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.65);color:#e5e7eb}
.ni-small-btn.blue{color:#93c5fd}.ni-small-btn.green{color:#86efac}.ni-small-btn.purple{color:#c4b5fd}.ni-small-btn.yellow{color:#fde68a}
.ni-pay-form{display:inline-flex;margin:0}
.ni-empty{padding:34px;text-align:center;color:#94a3b8}
.ni-pagination{padding:16px 19px}
@media(max-width:1100px){.ni-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.ni-stats{grid-template-columns:1fr}.ni-title{font-size:27px}.ni-filter{width:100%}.ni-input,.ni-select{width:100%}}
</style>

<div class="ni-page">

    <section class="ni-hero">
        <div class="ni-hero-inner">
            <div>
                <div class="ni-pill">🧾 Billing Center</div>
                <h1 class="ni-title">Invoices</h1>
                <div class="ni-sub">Pantau tagihan, pembayaran, provisioning layanan, dan akses Client 360.</div>
            </div>

            <div class="ni-actions">
                <a href="{{ $url('invoices.create') }}" class="ni-btn primary">+ Buat Invoice</a>
                <a href="{{ $url('customers.index') }}" class="ni-btn">👥 Customers</a>
            </div>
        </div>
    </section>

    <section class="ni-stats">
        <div class="ni-stat">
            <div class="ni-stat-icon">🧾</div>
            <div class="ni-stat-label">Total Invoice</div>
            <div class="ni-stat-value">{{ $totalInvoices }}</div>
        </div>

        <div class="ni-stat green">
            <div class="ni-stat-icon">✅</div>
            <div class="ni-stat-label">Paid</div>
            <div class="ni-stat-value">{{ $paidInvoices }}</div>
        </div>

        <div class="ni-stat yellow">
            <div class="ni-stat-icon">⏳</div>
            <div class="ni-stat-label">Unpaid</div>
            <div class="ni-stat-value">{{ $unpaidInvoices }}</div>
        </div>

        <div class="ni-stat purple">
            <div class="ni-stat-icon">💰</div>
            <div class="ni-stat-label">Revenue Bulan Ini</div>
            <div class="ni-stat-value" style="font-size:22px;">{{ $money($revenueThisMonth) }}</div>
        </div>
    </section>

    <section class="ni-card">
        <div class="ni-card-head">
            <div class="ni-card-title">
                <span class="ni-dot"></span>
                Daftar Invoice
            </div>

            <form method="GET" action="{{ $url('invoices.index') }}" class="ni-filter">
                <input type="text" name="q" value="{{ request('q') }}" class="ni-input" placeholder="Cari invoice / customer...">

                <select name="status" class="ni-select">
                    <option value="">Semua Status</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>

                <button class="ni-filter-btn" type="submit">Filter</button>

                @if(request('q') || request('status'))
                    <a href="{{ $url('invoices.index') }}" class="ni-small-btn">Reset</a>
                @endif
            </form>
        </div>

        <div class="ni-table-wrap">
            <table class="ni-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        @php
                            $number = $invoiceNoCol ? ($invoice->{$invoiceNoCol} ?? 'INV-'.$invoice->id) : 'INV-'.$invoice->id;
                            $total = $amountCol ? ($invoice->{$amountCol} ?? 0) : 0;
                            $status = strtolower($invoice->status ?? 'unpaid');
                            $customerId = $invoice->customer_id ?? null;
                            $userId = $invoice->user_id ?? null;
                            $resolvedUserId = $userId ?: $userIdFromCustomer($customerId);
                        @endphp

                        <tr>
                            <td>
                                <a href="{{ $url('invoices.show', $invoice->id) }}" class="ni-link">
                                    {{ $number }}
                                </a>
                                <div class="ni-muted">#{{ $invoice->id }}</div>
                            </td>

                            <td>
                                <div class="ni-main">{{ $customerName($customerId, $userId) }}</div>
                                @if($customerId)
                                    <div class="ni-muted">Customer ID: {{ $customerId }}</div>
                                @endif
                            </td>

                            <td style="font-weight:950;color:white;">{{ $money($total) }}</td>

                            <td>
                                <span class="{{ $statusClass($status) }}">{{ $status }}</span>
                            </td>

                            <td>{{ $date($invoice->due_date ?? null) }}</td>

                            <td>{{ $date($invoice->created_at ?? null) }}</td>

                            <td>
                                <div class="ni-row-actions">
                                    @if($resolvedUserId && Route::has('admin.client-history.show'))
                                        <a href="{{ route('admin.client-history.show', $resolvedUserId) }}" class="ni-small-btn purple">360</a>
                                    @elseif($customerId && Route::has('admin.client-history.customer'))
                                        <a href="{{ route('admin.client-history.customer', $customerId) }}" class="ni-small-btn purple">360</a>
                                    @endif

                                    <a href="{{ $url('invoices.show', $invoice->id) }}" class="ni-small-btn blue">Detail</a>

                                    @if($status !== 'paid' && $markPaidRoute)
                                        <form action="{{ route($markPaidRoute, $invoice->id) }}" method="POST" class="ni-pay-form">
                                            @csrf
                                            <button type="submit" class="ni-small-btn green" onclick="return confirm('Tandai invoice ini sebagai paid dan proses layanan?')">
                                                Paid
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ni-empty">Belum ada invoice ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($invoices, 'links'))
            <div class="ni-pagination">
                {{ $invoices->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
