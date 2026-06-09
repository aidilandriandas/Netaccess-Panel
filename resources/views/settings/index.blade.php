@extends('layouts.app')
@section('title', 'Settings')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;

    $hasSettings = Schema::hasTable('settings');

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

    $updateRoute = $firstRoute([
        'settings.update',
        'settings.store',
        'system.settings.update',
        'system.settings.store',
        'admin.settings.update',
        'admin.settings.store',
    ]);

    $getSetting = function ($key, $default = '') use ($hasSettings) {
        try {
            if (!$hasSettings) return $default;
            return DB::table('settings')->where('key', $key)->value('value') ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    };

    $groups = [
        'mikrotik' => [
            'title' => 'MikroTik',
            'icon' => '🛡️',
            'desc' => 'L2TP, API, IPSec',
            'color' => '#3b82f6',
            'fields' => [
                'mikrotik_host' => 'API Host',
                'mikrotik_public_ip' => 'Public VPN IP',
                'mikrotik_user' => 'Username',
                'mikrotik_password' => 'Password',
                'mikrotik_api_port' => 'API Port',
                'mikrotik_ipsec_secret' => 'IPSec Key',
            ],
        ],
        'whm' => [
            'title' => 'WHM / cPanel',
            'icon' => '🌐',
            'desc' => 'Hosting automation',
            'color' => '#10b981',
            'fields' => [
                'whm_host' => 'WHM Host',
                'whm_username' => 'WHM Username',
                'whm_token' => 'WHM Token',
                'cpanel_url' => 'cPanel URL',
            ],
        ],
        'system' => [
            'title' => 'System',
            'icon' => '⚙️',
            'desc' => 'Brand & payment',
            'color' => '#8b5cf6',
            'fields' => [
                'site_name' => 'Site Name',
                'company_name' => 'Company Name',
                'admin_email' => 'Admin Email',
                'payment_info' => 'Payment Info',
            ],
        ],
    ];

    $knownKeys = collect($groups)->flatMap(fn ($g) => array_keys($g['fields']))->values()->all();

    $otherSettings = $hasSettings
        ? DB::table('settings')->whereNotIn('key', $knownKeys)->orderBy('key')->get()
        : collect();
@endphp

<style>
.sx-page{display:flex;flex-direction:column;gap:16px}
.sx-hero{position:relative;overflow:hidden;border-radius:26px;padding:22px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 20px 55px rgba(0,0,0,.22)}
.sx-hero:before{content:"";position:absolute;right:-90px;top:-110px;width:280px;height:280px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.34),rgba(124,58,237,.26),rgba(14,165,233,.13));animation:sxSpin 20s linear infinite;opacity:.55}
@keyframes sxSpin{to{transform:rotate(360deg)}}
.sx-hero-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.sx-pill{display:inline-flex;padding:7px 12px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:10px}
.sx-title{margin:0;color:#fff;font-size:31px;font-weight:1000;letter-spacing:-.045em}
.sx-sub{margin-top:7px;color:#cbd5e1;font-size:13px}
.sx-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:0;border-radius:14px;min-height:40px;padding:0 14px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;cursor:pointer;transition:.2s ease}
.sx-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.sx-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent;box-shadow:0 14px 30px rgba(37,99,235,.25)}
.sx-overview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.sx-mini{position:relative;overflow:hidden;border-radius:22px;padding:17px;border:1px solid rgba(148,163,184,.15);background:linear-gradient(180deg,rgba(15,23,42,.74),rgba(15,23,42,.52));box-shadow:0 16px 38px rgba(0,0,0,.16)}
.sx-mini:after{content:"";position:absolute;right:-45px;top:-45px;width:130px;height:130px;border-radius:999px;background:var(--sx-color);opacity:.18}
.sx-mini-icon{width:39px;height:39px;border-radius:15px;display:flex;align-items:center;justify-content:center;background:rgba(148,163,184,.10);font-size:18px;margin-bottom:12px}
.sx-mini-title{color:white;font-size:15px;font-weight:1000}
.sx-mini-desc{color:#94a3b8;font-size:12px;margin-top:4px;font-weight:800}
.sx-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.sx-tabs{display:flex;gap:8px;padding:14px;border-bottom:1px solid rgba(148,163,184,.10);overflow-x:auto}
.sx-tab{border:1px solid rgba(148,163,184,.14);background:rgba(2,6,23,.24);color:#cbd5e1;border-radius:14px;padding:10px 13px;font-size:12px;font-weight:950;cursor:pointer;white-space:nowrap}
.sx-tab.active{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent}
.sx-panel{display:none;padding:18px}
.sx-panel.active{display:block}
.sx-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.sx-field label{display:block;color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em;margin-bottom:8px}
.sx-control{width:100%;height:43px;border-radius:15px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.72);color:white;padding:0 13px;outline:none}
textarea.sx-control{height:98px;padding:13px;resize:vertical}
.sx-control:focus{border-color:rgba(96,165,250,.68);box-shadow:0 0 0 4px rgba(37,99,235,.15)}
.sx-help{color:#64748b;font-size:11px;margin-top:6px}
.sx-save{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;padding:14px 18px;border-top:1px solid rgba(148,163,184,.10)}
.sx-warning{border-radius:16px;border:1px solid rgba(245,158,11,.22);background:rgba(245,158,11,.10);color:#fde68a;padding:13px;font-size:13px;font-weight:850}
@media(max-width:1000px){.sx-overview,.sx-grid{grid-template-columns:1fr}}
</style>

<div class="sx-page">

    <section class="sx-hero">
        <div class="sx-hero-inner">
            <div>
                <div class="sx-pill">⚙️ Compact Settings</div>
                <h1 class="sx-title">Settings</h1>
                <div class="sx-sub">Konfigurasi dibuat ringkas pakai tab, biar gak panjang ke bawah.</div>
            </div>

            <a href="{{ $url('dashboard') }}" class="sx-btn">← Dashboard</a>
        </div>
    </section>

    <section class="sx-overview">
        @foreach($groups as $id => $group)
            <div class="sx-mini" style="--sx-color:{{ $group['color'] }}">
                <div class="sx-mini-icon">{{ $group['icon'] }}</div>
                <div class="sx-mini-title">{{ $group['title'] }}</div>
                <div class="sx-mini-desc">{{ $group['desc'] }}</div>
            </div>
        @endforeach
    </section>

    @if(!$updateRoute)
        <div class="sx-warning">
            Route simpan settings belum ketemu. Form tetap tampil, tapi tombol simpan diarahkan ke #.
        </div>
    @endif

    <form method="POST" action="{{ route('settings') }}">
        @csrf

        <section class="sx-card">
            <div class="sx-tabs">
                @foreach($groups as $id => $group)
                    <button type="button" class="sx-tab {{ $loop->first ? 'active' : '' }}" data-tab="{{ $id }}">
                        {{ $group['icon'] }} {{ $group['title'] }}
                    </button>
                @endforeach

                @if($otherSettings->count())
                    <button type="button" class="sx-tab" data-tab="other">📁 Other</button>
                @endif
            </div>

            @foreach($groups as $id => $group)
                <div class="sx-panel {{ $loop->first ? 'active' : '' }}" data-panel="{{ $id }}">
                    <div class="sx-grid">
                        @foreach($group['fields'] as $key => $label)
                            <div class="sx-field">
                                <label>{{ $label }}</label>

                                @if($key === 'payment_info')
                                    <textarea class="sx-control" name="settings[{{ $key }}]" placeholder="{{ $key }}">{{ old('settings.'.$key, $getSetting($key)) }}</textarea>
                                @else
                                    <input
                                        class="sx-control"
                                        type="{{ str_contains($key, 'password') || str_contains($key, 'secret') || str_contains($key, 'token') ? 'password' : 'text' }}"
                                        name="settings[{{ $key }}]"
                                        value="{{ old('settings.'.$key, $getSetting($key)) }}"
                                        placeholder="{{ $key }}"
                                    >
                                @endif

                                <div class="sx-help">{{ $key }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if($otherSettings->count())
                <div class="sx-panel" data-panel="other">
                    <div class="sx-grid">
                        @foreach($otherSettings as $row)
                            <div class="sx-field">
                                <label>{{ $row->key }}</label>
                                <input class="sx-control" type="text" name="settings[{{ $row->key }}]" value="{{ old('settings.'.$row->key, $row->value ?? '') }}">
                                <div class="sx-help">Other setting</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="sx-save">
                <button type="submit" class="sx-btn primary">Simpan Settings</button>
            </div>
        </section>
    </form>

</div>

<script>
document.addEventListener('click', function(e){
    const tab = e.target.closest('.sx-tab');
    if(!tab) return;

    const id = tab.dataset.tab;

    document.querySelectorAll('.sx-tab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.sx-panel').forEach(el => el.classList.remove('active'));

    tab.classList.add('active');
    const panel = document.querySelector('.sx-panel[data-panel="' + id + '"]');
    if(panel) panel.classList.add('active');

    localStorage.setItem('netaccess_settings_tab', id);
});

document.addEventListener('DOMContentLoaded', function(){
    const last = localStorage.getItem('netaccess_settings_tab');
    if(!last) return;

    const tab = document.querySelector('.sx-tab[data-tab="' + last + '"]');
    if(tab) tab.click();
});
</script>
@endsection
