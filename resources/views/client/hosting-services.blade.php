@extends('layouts.client')
@section('title', 'My Hosting')

@section('content')

@php
    $hostingData = $hostingAccounts ?? $hostingServices ?? $hostings ?? $services ?? collect();

    if ($hostingData instanceof \Illuminate\Pagination\AbstractPaginator) {
        $hasHostingService = $hostingData->total() > 0;
    } elseif (is_countable($hostingData)) {
        $hasHostingService = count($hostingData) > 0;
    } else {
        $hasHostingService = collect($hostingData)->count() > 0;
    }
@endphp

@if(!$hasHostingService)
<style>
    .hide-hosting-server-info {
        display: none !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const candidates = Array.from(document.querySelectorAll('div, section, article'));

    const matches = candidates
        .filter(el => {
            const text = (el.innerText || '').trim();
            return text.includes('Hosting Control Panel') && text.includes('Panel Type');
        })
        .sort((a, b) => (a.innerText || '').length - (b.innerText || '').length);

    if (matches[0]) {
        matches[0].classList.add('hide-hosting-server-info');
    }
});
</script>
@endif

@php
    $hostingCollection = collect($hostingAccounts ?? $hostingServices ?? $hostings ?? $services ?? []);
    $hasHostingService = $hostingCollection->count() > 0;
@endphp
@php
    $hostings = $hostingAccounts ?? $hostingServices ?? $services ?? collect();

    if (is_array($hostings)) {
        $hostings = collect($hostings);
    }

    $activeCount = $hostings->where('status', 'active')->count();
    $suspendedCount = $hostings->where('status', 'suspended')->count();
    $totalCount = $hostings->count();

    $cpanelUrl = \App\Models\Setting::get('cpanel_url')
        ?? \App\Models\Setting::get('whm_host')
        ?? '-';
@endphp

<style>
.hosting-wrap{display:flex;flex-direction:column;gap:24px}
.hosting-hero{border-radius:26px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(37,99,235,.28),transparent 35%),radial-gradient(circle at bottom left,rgba(6,182,212,.18),transparent 36%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(12,74,110,.72));box-shadow:0 24px 60px rgba(0,0,0,.25)}
.hosting-hero-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:24px;align-items:center}
.hosting-pill{display:inline-flex;padding:7px 14px;border-radius:999px;border:1px solid rgba(96,165,250,.35);color:#bfdbfe;background:rgba(37,99,235,.12);font-size:12px;font-weight:900;margin-bottom:16px}
.hosting-title{color:white;font-size:34px;line-height:1.1;font-weight:950;margin:0}
.hosting-desc{color:#cbd5e1;font-size:15px;line-height:1.7;margin-top:12px;max-width:720px}
.hosting-panel-card{border-radius:22px;padding:22px;background:rgba(2,6,23,.50);border:1px solid rgba(255,255,255,.10)}
.hosting-label{color:#94a3b8;font-size:13px;font-weight:800}
.hosting-panel-title{color:white;font-size:26px;font-weight:950;margin-top:6px;word-break:break-all}
.hosting-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:18px}
.hosting-mini{padding:14px;border-radius:15px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.06)}
.hosting-mini strong{display:block;color:white;margin-top:5px}
.hosting-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.hosting-stat{border-radius:22px;padding:22px;min-height:140px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.78);box-shadow:0 20px 45px rgba(0,0,0,.18)}
.hosting-stat.total{background:linear-gradient(135deg,rgba(37,99,235,.22),rgba(15,23,42,.84))}
.hosting-stat.active{background:linear-gradient(135deg,rgba(34,197,94,.18),rgba(15,23,42,.84))}
.hosting-stat.suspended{background:linear-gradient(135deg,rgba(234,179,8,.20),rgba(15,23,42,.84))}
.hosting-stat-icon{font-size:28px;margin-bottom:14px}
.hosting-stat-title{color:#cbd5e1;font-size:14px;font-weight:900}
.hosting-stat-number{color:white;font-size:34px;font-weight:950;margin-top:8px}
.hosting-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.hosting-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(37,99,235,.13),transparent 36%),rgba(15,23,42,.80);box-shadow:0 20px 45px rgba(0,0,0,.18);overflow:hidden}
.hosting-card-head{padding:22px;display:flex;justify-content:space-between;gap:14px;align-items:flex-start;border-bottom:1px solid rgba(148,163,184,.12)}
.hosting-domain{color:white;font-size:20px;font-weight:950;word-break:break-all}
.hosting-package{color:#94a3b8;font-size:13px;margin-top:5px}
.hosting-badge{display:inline-flex;padding:7px 11px;border-radius:999px;font-size:12px;font-weight:950;text-transform:uppercase}
.hosting-badge.active{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.hosting-badge.suspended{color:#fde047;background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.24)}
.hosting-badge.failed,.hosting-badge.terminated{color:#fca5a5;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.24)}
.hosting-card-body{padding:22px}
.hosting-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.hosting-info{padding:14px;border-radius:16px;background:rgba(30,41,59,.50);border:1px solid rgba(148,163,184,.12)}
.hosting-info .label{color:#94a3b8;font-size:12px;font-weight:800}
.hosting-info .value{color:white;font-size:14px;font-weight:900;margin-top:5px;word-break:break-all}
.hosting-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
.hosting-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:none;cursor:pointer;border-radius:13px;padding:11px 14px;font-size:13px;font-weight:950}
.hosting-btn-primary{color:white;background:linear-gradient(135deg,#2563eb,#06b6d4)}
.hosting-btn-secondary{color:#e2e8f0;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16)}
.hosting-empty{border-radius:24px;padding:50px 24px;text-align:center;border:1px dashed rgba(148,163,184,.26);background:rgba(15,23,42,.58)}
.hosting-empty-icon{font-size:46px;margin-bottom:14px}
.hosting-empty-title{color:white;font-size:22px;font-weight:950}
.hosting-empty-desc{color:#94a3b8;margin-top:8px;font-size:14px}
.hosting-guide{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.hosting-guide-card{padding:20px;border-radius:20px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.72)}
.hosting-guide-card h3{color:white;font-size:16px;font-weight:950;margin:0 0 10px 0}
.hosting-guide-card p{color:#94a3b8;font-size:13px;line-height:1.6;margin:0}
@media(max-width:1100px){.hosting-hero-grid,.hosting-grid,.hosting-guide{grid-template-columns:1fr}}
@media(max-width:720px){.hosting-stats,.hosting-mini-grid,.hosting-info-grid{grid-template-columns:1fr}.hosting-title{font-size:28px}.hosting-panel-title{font-size:22px}}
</style>

<div class="hosting-wrap">

    @if($hasHostingService)
<section class="hosting-hero">
        <div class="hosting-hero-grid">
            <div>
                <div class="hosting-pill">🌐 Hosting Services</div>

                <h1 class="hosting-title">My Hosting</h1>

                <p class="hosting-desc">
                    Di halaman ini kamu bisa melihat layanan hosting aktif, domain, username cPanel,
                    paket hosting, status layanan, dan masa expired. Gunakan tombol detail untuk
                    melihat informasi lengkap akun hosting kamu.
                </p>
            </div>

            <div class="hosting-panel-card">
                <div class="hosting-label">Hosting Control Panel</div>
                <div class="hosting-panel-title">
                    {{ $cpanelUrl }}
                </div>

                <div class="hosting-mini-grid">
                    <div class="hosting-mini">
                        <div class="hosting-label">Panel Type</div>
                        <strong>cPanel / WHM</strong>
                    </div>

                    <div class="hosting-mini">
                        <div class="hosting-label">Status</div>
                        <strong style="color:#86efac;">Operational</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @endif

<section class="hosting-stats">
        <div class="hosting-stat total">
            <div class="hosting-stat-icon">🌐</div>
            <div class="hosting-stat-title">Total Hosting</div>
            <div class="hosting-stat-number">{{ $totalCount }}</div>
        </div>

        <div class="hosting-stat active">
            <div class="hosting-stat-icon">✅</div>
            <div class="hosting-stat-title">Hosting Aktif</div>
            <div class="hosting-stat-number">{{ $activeCount }}</div>
        </div>

        <div class="hosting-stat suspended">
            <div class="hosting-stat-icon">⏸️</div>
            <div class="hosting-stat-title">Hosting Suspended</div>
            <div class="hosting-stat-number">{{ $suspendedCount }}</div>
        </div>
    </section>

    @if($hostings->count())
        <section class="hosting-grid">
            @foreach($hostings as $hosting)
                @php
                    $status = $hosting->status ?? 'active';
                    $statusClass = in_array($status, ['active','suspended','failed','terminated']) ? $status : 'active';

                    $domain = $hosting->domain
                        ?? $hosting->domain_name
                        ?? $hosting->hostname
                        ?? '-';

                    $username = $hosting->username
                        ?? $hosting->cpanel_username
                        ?? '-';

                    $package = $hosting->package?->name
                        ?? $hosting->hostingPackage?->name
                        ?? $hosting->package_name
                        ?? 'Hosting Package';

                    $detailUrl = route('client.hosting-services.show', $hosting);
                @endphp

                <article class="hosting-card">
                    <div class="hosting-card-head">
                        <div>
                            <div class="hosting-domain">{{ $domain }}</div>
                            <div class="hosting-package">{{ $package }}</div>
                        </div>

                        <span class="hosting-badge {{ $statusClass }}">
                            {{ $status }}
                        </span>
                    </div>

                    <div class="hosting-card-body">
                        <div class="hosting-info-grid">
                            <div class="hosting-info">
                                <div class="label">Domain</div>
                                <div class="value">{{ $domain }}</div>
                            </div>

                            <div class="hosting-info">
                                <div class="label">Username cPanel</div>
                                <div class="value">{{ $username }}</div>
                            </div>

                            <div class="hosting-info">
                                <div class="label">Paket</div>
                                <div class="value">{{ $package }}</div>
                            </div>

                            <div class="hosting-info">
                                <div class="label">Expired</div>
                                <div class="value">{{ $hosting->expired_at?->format('d M Y') ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="hosting-actions">
                            <a href="{{ $detailUrl }}" class="hosting-btn hosting-btn-primary">
                                Detail Hosting
                            </a>

                            <button class="hosting-btn hosting-btn-secondary"
                                    onclick="copyText('Domain: {{ $domain }}\nUsername cPanel: {{ $username }}\nPaket: {{ $package }}\nStatus: {{ $status }}\nExpired: {{ $hosting->expired_at?->format('d M Y') ?? '-' }}')">
                                Copy Info
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @else
        <section class="hosting-empty">
            <div class="hosting-empty-icon">🌐</div>
            <div class="hosting-empty-title">Belum ada layanan hosting</div>
            <div class="hosting-empty-desc">
                Kamu belum memiliki layanan hosting aktif. Silakan hubungi admin untuk order hosting.
            </div>
        </section>
    @endif

    <section class="hosting-guide">
        <div class="hosting-guide-card">
            <h3>cPanel</h3>
            <p>
                Gunakan username cPanel dari detail hosting untuk login ke control panel hosting.
            </p>
        </div>

        <div class="hosting-guide-card">
            <h3>Domain</h3>
            <p>
                Pastikan domain mengarah ke nameserver atau DNS yang diberikan admin.
            </p>
        </div>

        <div class="hosting-guide-card">
            <h3>Support</h3>
            <p>
                Jika website tidak bisa diakses atau hosting bermasalah, hubungi admin/support.
            </p>
        </div>
    </section>

</div>

<script>
function copyText(text) {
    if (!text || text === '-') {
        showCopyToast('Data kosong', false);
        return;
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopyToast('Berhasil dicopy', true);
        }).catch(function() {
            fallbackCopyText(text);
        });
        return;
    }

    fallbackCopyText(text);
}

function fallbackCopyText(text) {
    var textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.width = '1px';
    textarea.style.height = '1px';
    textarea.style.opacity = '0';
    textarea.style.zIndex = '-1';

    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    textarea.setSelectionRange(0, 999999);

    var success = false;
    try {
        success = document.execCommand('copy');
    } catch (err) {
        success = false;
    }

    document.body.removeChild(textarea);

    if (success) {
        showCopyToast('Berhasil dicopy', true);
    } else {
        showCopyToast('Gagal copy, copy manual', false);
        prompt('Copy manual:', text);
    }
}

function showCopyToast(message, success) {
    var oldToast = document.getElementById('copy-toast');
    if (oldToast) oldToast.remove();

    var toast = document.createElement('div');
    toast.id = 'copy-toast';
    toast.innerText = message;
    toast.style.position = 'fixed';
    toast.style.right = '24px';
    toast.style.bottom = '24px';
    toast.style.zIndex = '999999';
    toast.style.padding = '12px 16px';
    toast.style.borderRadius = '14px';
    toast.style.background = success ? 'rgba(34, 197, 94, .96)' : 'rgba(239, 68, 68, .96)';
    toast.style.color = 'white';
    toast.style.fontWeight = '900';
    toast.style.boxShadow = '0 18px 40px rgba(0,0,0,.35)';

    document.body.appendChild(toast);

    setTimeout(function() {
        toast.remove();
    }, 1800);
}
</script>
@endsection
