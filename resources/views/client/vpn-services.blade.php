@extends('layouts.client')
@section('title', 'My VPN')

@section('content')

@php
    $vpnData = $vpnUsers ?? $vpnServices ?? $services ?? collect();

    if ($vpnData instanceof \Illuminate\Pagination\AbstractPaginator) {
        $hasVpnService = $vpnData->total() > 0;
    } elseif (is_countable($vpnData)) {
        $hasVpnService = count($vpnData) > 0;
    } else {
        $hasVpnService = collect($vpnData)->count() > 0;
    }
@endphp

@if(!$hasVpnService)
<style>
    .hide-vpn-server-info {
        display: none !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const candidates = Array.from(document.querySelectorAll('div, section, article'));

    const matches = candidates
        .filter(el => {
            const text = (el.innerText || '').trim();
            return text.includes('Server VPN') && text.includes('IPSec Key');
        })
        .sort((a, b) => (a.innerText || '').length - (b.innerText || '').length);

    if (matches[0]) {
        matches[0].classList.add('hide-vpn-server-info');
    }
});
</script>
@endif

@php
    $vpnCollection = collect($vpnUsers ?? $vpnServices ?? $services ?? []);
    $hasVpnService = $vpnCollection->count() > 0;
@endphp
@php
    $vpns = $vpnUsers ?? $services ?? $vpnServices ?? collect();

    if (is_array($vpns)) {
        $vpns = collect($vpns);
    }

    $vpnServer = \App\Models\Setting::get('mikrotik_public_ip')
        ?? \App\Models\Setting::get('mikrotik_host')
        ?? '-';

    $vpnPsk = \App\Models\Setting::get('mikrotik_ipsec_secret')
        ?? \App\Models\Setting::get('ipsec_preshared_key')
        ?? \App\Models\Setting::get('l2tp_ipsec_secret')
        ?? '-';

    $activeCount = $vpns->where('status', 'active')->count();
    $suspendedCount = $vpns->where('status', 'suspended')->count();
    $expiredCount = $vpns->where('status', 'expired')->count();
@endphp

<style>
.vpn-wrap {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.vpn-hero {
    border-radius: 26px;
    padding: 28px;
    border: 1px solid rgba(148, 163, 184, .18);
    background:
        radial-gradient(circle at top right, rgba(124, 58, 237, .28), transparent 36%),
        radial-gradient(circle at bottom left, rgba(37, 99, 235, .22), transparent 36%),
        linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(30, 27, 75, .85));
    box-shadow: 0 24px 60px rgba(0,0,0,.25);
}

.vpn-hero-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
    gap: 24px;
    align-items: center;
}

.vpn-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: 999px;
    border: 1px solid rgba(96, 165, 250, .35);
    color: #bfdbfe;
    background: rgba(37, 99, 235, .12);
    font-size: 12px;
    font-weight: 900;
    margin-bottom: 16px;
}

.vpn-title {
    font-size: 34px;
    line-height: 1.1;
    font-weight: 950;
    color: white;
    margin: 0;
}

.vpn-desc {
    margin-top: 12px;
    color: #cbd5e1;
    font-size: 15px;
    line-height: 1.7;
    max-width: 720px;
}

.vpn-server-card {
    border-radius: 22px;
    padding: 22px;
    background: rgba(2, 6, 23, .50);
    border: 1px solid rgba(255,255,255,.10);
}

.vpn-server-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
}

.vpn-label {
    color: #94a3b8;
    font-size: 13px;
    font-weight: 800;
}

.vpn-server {
    color: white;
    font-size: 28px;
    font-weight: 950;
    margin-top: 6px;
}

.vpn-copy {
    border: none;
    background: #2563eb;
    color: white;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
}

.vpn-mini-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 12px;
    margin-top: 18px;
}

.vpn-mini {
    padding: 14px;
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,.10);
    background: rgba(255,255,255,.06);
}

.vpn-mini strong {
    display: block;
    color: white;
    margin-top: 5px;
}

.vpn-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 16px;
}

.vpn-stat {
    padding: 22px;
    min-height: 140px;
    border-radius: 22px;
    border: 1px solid rgba(148, 163, 184, .18);
    background: rgba(15, 23, 42, .78);
    box-shadow: 0 20px 45px rgba(0,0,0,.18);
}

.vpn-stat.active {
    background: linear-gradient(135deg, rgba(34,197,94,.20), rgba(15,23,42,.85));
}

.vpn-stat.suspend {
    background: linear-gradient(135deg, rgba(234,179,8,.20), rgba(15,23,42,.85));
}

.vpn-stat.expired {
    background: linear-gradient(135deg, rgba(239,68,68,.18), rgba(15,23,42,.85));
}

.vpn-stat-icon {
    font-size: 28px;
    margin-bottom: 14px;
}

.vpn-stat-title {
    color: #cbd5e1;
    font-weight: 900;
    font-size: 14px;
}

.vpn-stat-number {
    color: white;
    font-size: 34px;
    font-weight: 950;
    margin-top: 8px;
}

.vpn-card-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 18px;
}

.vpn-card {
    border-radius: 24px;
    border: 1px solid rgba(148, 163, 184, .18);
    background:
        radial-gradient(circle at top right, rgba(59,130,246,.12), transparent 36%),
        rgba(15,23,42,.80);
    box-shadow: 0 20px 45px rgba(0,0,0,.18);
    overflow: hidden;
}

.vpn-card-head {
    padding: 22px;
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: flex-start;
    border-bottom: 1px solid rgba(148, 163, 184, .12);
}

.vpn-name {
    color: white;
    font-size: 20px;
    font-weight: 950;
}

.vpn-package {
    color: #94a3b8;
    font-size: 13px;
    margin-top: 5px;
}

.vpn-badge {
    display: inline-flex;
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 950;
    text-transform: uppercase;
}

.vpn-badge.active {
    color: #86efac;
    background: rgba(34,197,94,.15);
    border: 1px solid rgba(34,197,94,.24);
}

.vpn-badge.suspended {
    color: #fde047;
    background: rgba(234,179,8,.15);
    border: 1px solid rgba(234,179,8,.24);
}

.vpn-badge.expired {
    color: #fca5a5;
    background: rgba(239,68,68,.15);
    border: 1px solid rgba(239,68,68,.24);
}

.vpn-card-body {
    padding: 22px;
}

.vpn-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 12px;
}

.vpn-info {
    padding: 14px;
    border-radius: 16px;
    background: rgba(30,41,59,.50);
    border: 1px solid rgba(148,163,184,.12);
}

.vpn-info .label {
    color: #94a3b8;
    font-size: 12px;
    font-weight: 800;
}

.vpn-info .value {
    color: white;
    font-size: 14px;
    font-weight: 900;
    margin-top: 5px;
    word-break: break-all;
}

.vpn-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
}

.vpn-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    border-radius: 13px;
    padding: 11px 14px;
    font-size: 13px;
    font-weight: 950;
}

.vpn-btn-primary {
    color: white;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
}

.vpn-btn-secondary {
    color: #e2e8f0;
    background: rgba(30,41,59,.78);
    border: 1px solid rgba(148,163,184,.16);
}

.vpn-empty {
    border-radius: 24px;
    padding: 50px 24px;
    text-align: center;
    border: 1px dashed rgba(148, 163, 184, .26);
    background: rgba(15, 23, 42, .58);
}

.vpn-empty-icon {
    font-size: 46px;
    margin-bottom: 14px;
}

.vpn-empty-title {
    color: white;
    font-size: 22px;
    font-weight: 950;
}

.vpn-empty-desc {
    color: #94a3b8;
    margin-top: 8px;
    font-size: 14px;
}

.vpn-guide {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 16px;
}

.vpn-guide-card {
    padding: 20px;
    border-radius: 20px;
    border: 1px solid rgba(148,163,184,.18);
    background: rgba(15,23,42,.72);
}

.vpn-guide-card h3 {
    color: white;
    font-size: 16px;
    font-weight: 950;
    margin: 0 0 10px 0;
}

.vpn-guide-card p {
    color: #94a3b8;
    font-size: 13px;
    line-height: 1.6;
}

@media (max-width: 1100px) {
    .vpn-hero-grid,
    .vpn-card-grid,
    .vpn-guide {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 720px) {
    .vpn-stats,
    .vpn-mini-grid,
    .vpn-info-grid {
        grid-template-columns: 1fr;
    }

    .vpn-title {
        font-size: 28px;
    }

    .vpn-server {
        font-size: 22px;
    }
}
</style>

<div class="vpn-wrap">

    @if($hasVpnService)
<section class="vpn-hero">
        <div class="vpn-hero-grid">
            <div>
                <div class="vpn-pill">🔐 VPN Services</div>

                <h1 class="vpn-title">My VPN</h1>

                <p class="vpn-desc">
                    Di halaman ini kamu bisa melihat akun VPN aktif, username, password, server VPN,
                    status layanan, dan masa expired. Gunakan detail VPN untuk konek dari Android,
                    iPhone, Windows, atau router.
                </p>
            </div>

            <div class="vpn-server-card">
                <div class="vpn-server-row">
                    <div>
                        <div class="vpn-label">Server VPN</div>
                        <div class="vpn-server">{{ $vpnServer }}</div>
                    </div>

                    <button class="vpn-copy" onclick="copyText('{{ $vpnServer }}')">
                        Copy
                    </button>
                </div>

                <div class="vpn-mini-grid">
                    <div class="vpn-mini">
                        <div class="vpn-label">VPN Type</div>
                        <strong>L2TP/IPSec</strong>
                    </div>

                    <div class="vpn-mini">
                        <div class="vpn-label">IPSec Key</div>
                        <strong>{{ $vpnPsk }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @endif

<section class="vpn-stats">
        <div class="vpn-stat active">
            <div class="vpn-stat-icon">✅</div>
            <div class="vpn-stat-title">VPN Aktif</div>
            <div class="vpn-stat-number">{{ $activeCount }}</div>
        </div>

        <div class="vpn-stat suspend">
            <div class="vpn-stat-icon">⏸️</div>
            <div class="vpn-stat-title">VPN Suspended</div>
            <div class="vpn-stat-number">{{ $suspendedCount }}</div>
        </div>

        <div class="vpn-stat expired">
            <div class="vpn-stat-icon">⏳</div>
            <div class="vpn-stat-title">VPN Expired</div>
            <div class="vpn-stat-number">{{ $expiredCount }}</div>
        </div>
    </section>

    @if($vpns->count())
        <section class="vpn-card-grid">
            @foreach($vpns as $vpn)
                @php
                    $password = $vpn->l2tp_password ?? $vpn->password ?? '-';
                    $statusClass = in_array($vpn->status, ['active','suspended','expired']) ? $vpn->status : 'suspended';
                    $detailUrl = url('/client/vpn-services/' . $vpn->id);
                @endphp

                <article class="vpn-card">
                    <div class="vpn-card-head">
                        <div>
                            <div class="vpn-name">{{ $vpn->username }}</div>
                            <div class="vpn-package">{{ $vpn->package?->name ?? 'VPN Service' }}</div>
                        </div>

                        <span class="vpn-badge {{ $statusClass }}">
                            {{ $vpn->status }}
                        </span>
                    </div>

                    <div class="vpn-card-body">
                        <div class="vpn-info-grid">
                            <div class="vpn-info">
                                <div class="label">Server VPN</div>
                                <div class="value">{{ $vpnServer }}</div>
                            </div>

                            <div class="vpn-info">
                                <div class="label">Tipe VPN</div>
                                <div class="value">L2TP/IPSec</div>
                            </div>

                            <div class="vpn-info">
                                <div class="label">Username</div>
                                <div class="value">{{ $vpn->username }}</div>
                            </div>

                            <div class="vpn-info">
                                <div class="label">Password</div>
                                <div class="value">{{ $password }}</div>
                            </div>

                            <div class="vpn-info">
                                <div class="label">IPSec Key</div>
                                <div class="value">{{ $vpnPsk }}</div>
                            </div>

                            <div class="vpn-info">
                                <div class="label">Expired</div>
                                <div class="value">{{ $vpn->expired_at?->format('d M Y') ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="vpn-actions">
                            <a href="{{ $detailUrl }}" class="vpn-btn vpn-btn-primary">
                                Detail & Panduan
                            </a>

                            <button class="vpn-btn vpn-btn-secondary"
                                    onclick="copyText('Server VPN: {{ $vpnServer }}\nTipe VPN: L2TP/IPSec\nUsername: {{ $vpn->username }}\nPassword: {{ $password }}\nIPSec Key: {{ $vpnPsk }}\nExpired: {{ $vpn->expired_at?->format('d M Y') ?? '-' }}')">
                                Copy Data VPN
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @else
        <section class="vpn-empty">
            <div class="vpn-empty-icon">🔐</div>
            <div class="vpn-empty-title">Belum ada layanan VPN</div>
            <div class="vpn-empty-desc">
                Kamu belum memiliki layanan VPN aktif. Silakan hubungi admin untuk order layanan VPN.
            </div>
        </section>
    @endif

    <section class="vpn-guide">
        <div class="vpn-guide-card">
            <h3>Android</h3>
            <p>
                Buka Settings → Network & Internet → VPN → Add VPN.
                Pilih L2TP/IPSec PSK, lalu isi Server, Username, Password, dan IPSec Key.
            </p>
        </div>

        <div class="vpn-guide-card">
            <h3>iPhone / iPad</h3>
            <p>
                Buka Settings → General → VPN & Device Management → Add VPN.
                Type pilih L2TP, isi Server, Account, Password, dan Secret.
            </p>
        </div>

        <div class="vpn-guide-card">
            <h3>Windows</h3>
            <p>
                Buka Settings → Network & Internet → VPN → Add VPN.
                VPN Type pilih L2TP/IPSec with pre-shared key.
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
    if (oldToast) {
        oldToast.remove();
    }

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
