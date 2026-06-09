@extends('layouts.app')
@section('title', 'Packages')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $rawData = $packages ?? collect();

    if (is_object($rawData) && method_exists($rawData, 'items')) {
        $data = collect($rawData->items());
    } else {
        $data = collect($rawData);
    }

    $groups = [
        'vpn' => [
            'title' => 'VPN / L2TP',
            'icon' => '🛡️',
            'desc' => 'Paket L2TP MikroTik',
            'color' => '#3b82f6',
        ],
        'hosting' => [
            'title' => 'Hosting',
            'icon' => '🌐',
            'desc' => 'Paket web hosting',
            'color' => '#10b981',
        ],
        'vps' => [
            'title' => 'VPS',
            'icon' => '🖥️',
            'desc' => 'Paket virtual server',
            'color' => '#8b5cf6',
        ],
        'other' => [
            'title' => 'Other',
            'icon' => '📦',
            'desc' => 'Monitoring, backup, dan layanan lain',
            'color' => '#f59e0b',
        ],
    ];

    $getType = function ($item) {
        return strtolower(trim($item->service_type ?? 'other'));
    };

    $isInGroup = function ($item, $group) use ($getType) {
        $type = $getType($item);

        if ($group === 'vpn') {
            return in_array($type, ['vpn', 'l2tp'], true);
        }

        if ($group === 'hosting') {
            return in_array($type, ['hosting', 'host', 'webhosting', 'web-hosting', 'cpanel', 'whm'], true);
        }

        if ($group === 'vps') {
            return in_array($type, ['vps', 'server', 'virtual-server'], true);
        }

        return !in_array($type, [
            'vpn',
            'l2tp',
            'hosting',
            'host',
            'webhosting',
            'web-hosting',
            'cpanel',
            'whm',
            'vps',
            'server',
            'virtual-server',
        ], true);
    };

    $countByType = [];
    foreach ($groups as $id => $group) {
        $countByType[$id] = $data->filter(fn ($item) => $isInGroup($item, $id))->count();
    }

    $routeOrHash = function ($name, $param = null) {
        try {
            if (!Route::has($name)) return '#';
            return $param ? route($name, $param) : route($name);
        } catch (\Throwable $e) {
            return '#';
        }
    };

    $formatPrice = function ($value) {
        return 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
    };
@endphp

<style>
.px-page{display:flex;flex-direction:column;gap:14px}
.px-hero{position:relative;overflow:hidden;border-radius:24px;padding:20px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 20px 55px rgba(0,0,0,.22)}
.px-hero:before{content:"";position:absolute;right:-90px;top:-110px;width:280px;height:280px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.34),rgba(124,58,237,.26),rgba(14,165,233,.13));animation:pxSpin 20s linear infinite;opacity:.55}
@keyframes pxSpin{to{transform:rotate(360deg)}}
.px-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.px-pill{display:inline-flex;padding:6px 11px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:10px}
.px-title{margin:0;color:#fff;font-size:28px;font-weight:1000;letter-spacing:-.045em}
.px-sub{margin-top:7px;color:#cbd5e1;font-size:12px}
.px-actions{display:flex;gap:9px;flex-wrap:wrap}
.px-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:0;border-radius:13px;min-height:36px;padding:0 13px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:11px;font-weight:950;cursor:pointer;transition:.2s ease}
.px-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.px-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.px-overview{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.px-mini{position:relative;overflow:hidden;border-radius:20px;padding:14px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.74),rgba(15,23,42,.52));box-shadow:0 16px 38px rgba(0,0,0,.16)}
.px-mini:after{content:"";position:absolute;right:-45px;top:-45px;width:130px;height:130px;border-radius:999px;background:var(--px-color);opacity:.18}
.px-mini-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
.px-mini-icon{width:36px;height:36px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:rgba(148,163,184,.10);font-size:16px}
.px-mini-count{color:white;font-size:21px;font-weight:1000}
.px-mini-title{color:white;font-size:13px;font-weight:1000;margin-top:10px}
.px-mini-desc{color:#94a3b8;font-size:11px;margin-top:4px;font-weight:800}
.px-card{overflow:hidden;border-radius:23px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.px-tabs{display:flex;gap:8px;padding:13px;border-bottom:1px solid rgba(148,163,184,.10);overflow-x:auto}
.px-tab{border:1px solid rgba(148,163,184,.14);background:rgba(2,6,23,.24);color:#cbd5e1;border-radius:13px;padding:9px 12px;font-size:11px;font-weight:950;cursor:pointer;white-space:nowrap}
.px-tab.active{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent}
.px-panel{display:none;padding:15px}
.px-panel.active{display:block}
.px-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.px-item{border-radius:18px;border:1px solid rgba(148,163,184,.13);background:rgba(2,6,23,.24);padding:13px}
.px-item-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.px-name{color:white;font-size:14px;font-weight:1000}
.px-type{display:inline-flex;margin-top:7px;padding:4px 8px;border-radius:999px;background:rgba(96,165,250,.13);color:#bfdbfe;font-size:10px;font-weight:950;text-transform:uppercase;letter-spacing:.06em}
.px-price{color:white;font-size:16px;font-weight:1000;text-align:right;white-space:nowrap}
.px-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.px-badge{display:inline-flex;padding:5px 8px;border-radius:999px;background:rgba(148,163,184,.10);color:#cbd5e1;font-size:10px;font-weight:850}
.px-desc{margin-top:11px;color:#94a3b8;font-size:12px;line-height:1.55}
.px-item-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:12px;flex-wrap:wrap}
.px-empty{border-radius:18px;border:1px dashed rgba(148,163,184,.22);padding:24px;text-align:center;color:#94a3b8;font-size:13px;font-weight:850;background:rgba(2,6,23,.18)}
@media(max-width:1100px){.px-overview{grid-template-columns:repeat(2,minmax(0,1fr))}.px-list{grid-template-columns:1fr}}
@media(max-width:640px){.px-overview{grid-template-columns:1fr}.px-title{font-size:24px}}
</style>

<div class="px-page">

    <section class="px-hero">
        <div class="px-hero-inner">
            <div>
                <div class="px-pill">📦 Compact Packages</div>
                <h1 class="px-title">Packages / Layanan</h1>
                <div class="px-sub">Semua produk dijadikan satu: VPN L2TP, Hosting, VPS, dan layanan lainnya.</div>
            </div>

            <div class="px-actions">
                <a href="{{ $routeOrHash('dashboard') }}" class="px-btn">← Dashboard</a>
                <a href="{{ $routeOrHash('packages.create') }}" class="px-btn primary">+ Tambah Paket</a>
            </div>
        </div>
    </section>

    <section class="px-overview">
        @foreach($groups as $id => $group)
            <div class="px-mini" style="--px-color:{{ $group['color'] }}">
                <div class="px-mini-top">
                    <div class="px-mini-icon">{{ $group['icon'] }}</div>
                    <div class="px-mini-count">{{ $countByType[$id] ?? 0 }}</div>
                </div>
                <div class="px-mini-title">{{ $group['title'] }}</div>
                <div class="px-mini-desc">{{ $group['desc'] }}</div>
            </div>
        @endforeach
    </section>

    <section class="px-card">
        <div class="px-tabs">
            @foreach($groups as $id => $group)
                <button type="button" class="px-tab {{ $loop->first ? 'active' : '' }}" data-tab="{{ $id }}">
                    {{ $group['icon'] }} {{ $group['title'] }}
                </button>
            @endforeach
        </div>

        @foreach($groups as $id => $group)
            @php
                $items = $data->filter(fn ($item) => $isInGroup($item, $id));
            @endphp

            <div class="px-panel {{ $loop->first ? 'active' : '' }}" data-panel="{{ $id }}">
                @if($items->count())
                    <div class="px-list">
                        @foreach($items as $item)
                            @php
                                $name = $item->name ?? 'Untitled Package';
                                $type = strtolower($item->service_type ?? 'other');
                                $price = $item->price ?? 0;
                                $duration = isset($item->duration_days) ? $item->duration_days.' hari' : 'Bulanan';
                                $status = ($item->is_active ?? true) ? 'active' : 'inactive';
                                $desc = $item->description ?? null;
                            @endphp

                            <article class="px-item">
                                <div class="px-item-top">
                                    <div>
                                        <div class="px-name">{{ $name }}</div>
                                        <div class="px-type">{{ strtoupper($type) }}</div>
                                    </div>
                                    <div class="px-price">{{ $formatPrice($price) }}</div>
                                </div>

                                <div class="px-meta">
                                    <span class="px-badge">⏱️ {{ $duration }}</span>
                                    <span class="px-badge">● {{ $status }}</span>
                                    @if(isset($item->max_users) && $item->max_users)
                                        <span class="px-badge">👥 {{ $item->max_users }} user</span>
                                    @endif
                                </div>

                                @if($desc)
                                    <div class="px-desc">{{ $desc }}</div>
                                @endif

                                <div class="px-item-actions">
                                    <a class="px-btn" href="{{ $routeOrHash('packages.edit', $item->id) }}">Edit</a>

                                    <form method="POST" action="{{ $routeOrHash('packages.destroy', $item->id) }}" onsubmit="return confirm('Hapus paket ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-btn" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="px-empty">
                        Belum ada paket untuk kategori {{ $group['title'] }}.
                    </div>
                @endif
            </div>
        @endforeach
    </section>

</div>

<script>
document.addEventListener('click', function(e){
    const tab = e.target.closest('.px-tab');
    if(!tab) return;

    const id = tab.dataset.tab;

    document.querySelectorAll('.px-tab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.px-panel').forEach(el => el.classList.remove('active'));

    tab.classList.add('active');

    const panel = document.querySelector('.px-panel[data-panel="' + id + '"]');
    if(panel) panel.classList.add('active');

    localStorage.setItem('netaccess_packages_tab', id);
});

document.addEventListener('DOMContentLoaded', function(){
    const last = localStorage.getItem('netaccess_packages_tab');
    if(!last) return;

    const tab = document.querySelector('.px-tab[data-tab="' + last + '"]');
    if(tab) tab.click();
});
</script>
@endsection
