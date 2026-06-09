@extends('layouts.client')
@section('title', 'Detail Hosting')

@section('content')
@php
    $hosting = $hostingAccount ?? $hostingService ?? $hosting ?? null;

    $domain = $hosting->domain
        ?? $hosting->domain_name
        ?? $hosting->hostname
        ?? '-';

    $username = $hosting->username
        ?? $hosting->cpanel_username
        ?? '-';

    $password = $hosting->password
        ?? $hosting->cpanel_password
        ?? $hosting->hosting_password
        ?? '-';

    $package = $hosting->package?->name
        ?? $hosting->hostingPackage?->name
        ?? $hosting->package_name
        ?? 'Hosting Package';

    $status = $hosting->status ?? 'active';
    $statusClass = in_array($status, ['active','suspended','failed','terminated']) ? $status : 'active';

    $disk = $hosting->disk_limit
        ?? $hosting->disk
        ?? $hosting->quota
        ?? '-';

    $bandwidth = $hosting->bandwidth_limit
        ?? $hosting->bandwidth
        ?? '-';

    $whmHost = \App\Models\Setting::get('whm_host')
        ?? \App\Models\Setting::get('cpanel_url')
        ?? '';

    $cleanHost = $whmHost;

    $cleanHost = str_replace('https://', '', $cleanHost);
    $cleanHost = str_replace('http://', '', $cleanHost);
    $cleanHost = trim($cleanHost, '/');

    $cpanelLoginUrl = $cleanHost
        ? 'https://' . $cleanHost . ':2083'
        : 'https://' . $domain . ':2083';

    $whmLoginUrl = $cleanHost
        ? 'https://' . $cleanHost . ':2087'
        : '#';

    $webmailUrl = $domain !== '-'
        ? 'https://' . $domain . ':2096'
        : '#';

    $expired = $hosting->expired_at?->format('d M Y') ?? '-';
    $created = $hosting->created_at?->format('d M Y') ?? '-';

    $copyText = "Detail Hosting\nDomain: {$domain}\nLogin cPanel: {$cpanelLoginUrl}\nUsername: {$username}\nPassword: {$password}\nPackage: {$package}\nStatus: {$status}\nExpired: {$expired}";
@endphp

<style>
.host-detail-wrap{display:flex;flex-direction:column;gap:24px}
.host-detail-hero{border-radius:28px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(37,99,235,.28),transparent 35%),radial-gradient(circle at bottom left,rgba(6,182,212,.18),transparent 36%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(12,74,110,.72));box-shadow:0 24px 60px rgba(0,0,0,.25)}
.host-detail-top{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap}
.host-pill{display:inline-flex;padding:7px 14px;border-radius:999px;border:1px solid rgba(96,165,250,.35);color:#bfdbfe;background:rgba(37,99,235,.12);font-size:12px;font-weight:900;margin-bottom:14px}
.host-title{color:white;font-size:34px;font-weight:950;margin:0;word-break:break-all}
.host-desc{color:#cbd5e1;font-size:15px;line-height:1.7;margin-top:10px}
.host-badge{display:inline-flex;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:950;text-transform:uppercase}
.host-badge.active{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.host-badge.suspended{color:#fde047;background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.24)}
.host-badge.failed,.host-badge.terminated{color:#fca5a5;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.24)}
.host-grid{display:grid;grid-template-columns:1.25fr .75fr;gap:20px}
.host-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.80);box-shadow:0 20px 45px rgba(0,0,0,.18);padding:24px}
.host-card h2{color:white;font-size:20px;font-weight:950;margin:0 0 18px 0}
.host-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.host-info{padding:16px;border-radius:17px;background:rgba(30,41,59,.50);border:1px solid rgba(148,163,184,.12)}
.host-label{color:#94a3b8;font-size:12px;font-weight:850}
.host-value{color:white;font-size:15px;font-weight:950;margin-top:7px;word-break:break-all}
.host-actions{display:grid;grid-template-columns:1fr;gap:12px}
.host-btn{display:inline-flex;align-items:center;justify-content:center;text-align:center;text-decoration:none;border:none;cursor:pointer;border-radius:15px;padding:13px 16px;font-size:14px;font-weight:950}
.host-btn-primary{color:white;background:linear-gradient(135deg,#2563eb,#06b6d4)}
.host-btn-green{color:white;background:#16a34a}
.host-btn-dark{color:#e2e8f0;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16)}
.host-login-box{border-radius:20px;padding:18px;background:rgba(2,6,23,.45);border:1px solid rgba(255,255,255,.10);margin-bottom:16px}
.host-login-title{color:white;font-size:17px;font-weight:950}
.host-login-sub{color:#94a3b8;font-size:13px;margin-top:6px;line-height:1.6}
.host-guide{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.host-guide-card{padding:20px;border-radius:20px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.72)}
.host-guide-card h3{color:white;font-size:16px;font-weight:950;margin:0 0 10px 0}
.host-guide-card p{color:#94a3b8;font-size:13px;line-height:1.6;margin:0}
@media(max-width:1000px){.host-grid,.host-guide{grid-template-columns:1fr}}
@media(max-width:720px){.host-title{font-size:27px}.host-info-grid{grid-template-columns:1fr}}
</style>

<div class="host-detail-wrap">

    <section class="host-detail-hero">
        <div class="host-detail-top">
            <div>
                <div class="host-pill">🌐 Hosting Detail</div>
                <h1 class="host-title">{{ $domain }}</h1>
                <p class="host-desc">
                    Detail akun hosting, akses login cPanel, informasi paket, status, dan masa aktif layanan.
                </p>
            </div>

            <span class="host-badge {{ $statusClass }}">
                {{ $status }}
            </span>
        </div>
    </section>

    <section class="host-grid">
        <div class="host-card">
            <h2>Informasi Hosting</h2>

            <div class="host-info-grid">
                <div class="host-info">
                    <div class="host-label">Domain</div>
                    <div class="host-value">{{ $domain }}</div>
                </div>

                <div class="host-info">
                    <div class="host-label">Package</div>
                    <div class="host-value">{{ $package }}</div>
                </div>

                <div class="host-info">
                    <div class="host-label">Username cPanel</div>
                    <div class="host-value">{{ $username }}</div>
                </div>

                <div class="host-info">
                    <div class="host-label">Password cPanel</div>
                    <div class="host-value">{{ $password }}</div>
                </div>

                <div class="host-info">
                    <div class="host-label">Disk</div>
                    <div class="host-value">{{ $disk }} MB</div>
                </div>

                <div class="host-info">
                    <div class="host-label">Bandwidth</div>
                    <div class="host-value">{{ $bandwidth }} MB</div>
                </div>

                <div class="host-info">
                    <div class="host-label">Mulai</div>
                    <div class="host-value">{{ $created }}</div>
                </div>

                <div class="host-info">
                    <div class="host-label">Expired</div>
                    <div class="host-value">{{ $expired }}</div>
                </div>
            </div>
        </div>

        <div class="host-card">
            <h2>Akses Login</h2>

            <div class="host-login-box">
                <div class="host-login-title">cPanel Login</div>
                <div class="host-login-sub">
                    Gunakan username dan password hosting untuk masuk ke cPanel.
                </div>
            </div>

            <div class="host-actions">
                <a href="{{ $cpanelLoginUrl }}" target="_blank" class="host-btn host-btn-primary">
                    Login ke cPanel
                </a>

                <a href="{{ $webmailUrl }}" target="_blank" class="host-btn host-btn-green">
                    Login Webmail
                </a>

                @if($whmLoginUrl !== '#')
                    <a href="{{ $whmLoginUrl }}" target="_blank" class="host-btn host-btn-dark">
                        Buka WHM
                    </a>
                @endif

                <button type="button" class="host-btn host-btn-dark" onclick='copyText(@json($copyText))'>
                    Copy Info Login
                </button>

                <a href="{{ route('client.hosting-services') }}" class="host-btn host-btn-dark">
                    Kembali
                </a>
            </div>
        </div>
    </section>

    <section class="host-guide">
        <div class="host-guide-card">
            <h3>Cara Login cPanel</h3>
            <p>
                Klik tombol Login ke cPanel, lalu masukkan username dan password cPanel yang tertera di halaman ini.
            </p>
        </div>

        <div class="host-guide-card">
            <h3>Webmail</h3>
            <p>
                Webmail digunakan untuk membuka email domain. Login menggunakan akun email yang dibuat dari cPanel.
            </p>
        </div>

        <div class="host-guide-card">
            <h3>Keamanan</h3>
            <p>
                Jangan bagikan username dan password hosting kepada orang lain. Ganti password jika merasa bocor.
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
