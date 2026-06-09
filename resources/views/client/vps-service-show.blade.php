@extends('layouts.client')
@section('title', 'Detail VPS')

@section('content')
@php
    $vps = $vpsService ?? $vpsAccount ?? $vps ?? null;

    $fmt = function ($value) {
        if (!$value) return '-';
        try {
            return $value instanceof \Carbon\CarbonInterface
                ? $value->format('d M Y')
                : \Carbon\Carbon::parse($value)->format('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $name = $vps->name
        ?? $vps->hostname
        ?? $vps->server_name
        ?? 'VPS-'.$vps->id;

    $ip = $vps->ip_address
        ?? $vps->main_ip
        ?? $vps->public_ip
        ?? '-';

    $username = $vps->username
        ?? $vps->ssh_username
        ?? $vps->root_username
        ?? 'root';

    $password = $vps->password
        ?? $vps->root_password
        ?? $vps->vps_password
        ?? '-';

    $os = $vps->os
        ?? $vps->operating_system
        ?? $vps->template
        ?? '-';

    $package = $vps->package?->name
        ?? $vps->vpsPackage?->name
        ?? $vps->package_name
        ?? 'VPS Package';

    $cpu = $vps->cpu
        ?? $vps->vcpu
        ?? $vps->cpu_core
        ?? $vps->cores
        ?? '-';

    $ram = $vps->ram
        ?? $vps->memory
        ?? '-';

    $disk = $vps->disk
        ?? $vps->storage
        ?? '-';

    $status = $vps->status ?? 'active';
    $statusClass = in_array($status, ['active','pending','suspended','expired','terminated','failed']) ? $status : 'active';

    $created = $fmt($vps->created_at ?? null);
    $expired = $fmt($vps->expired_at ?? null);


    $copyText = "Detail VPS\nName: {$name}\nIP Address: {$ip}\nUsername: {$username}\nPassword: {$password}\nOS: {$os}\nPackage: {$package}\nCPU: {$cpu}\nRAM: {$ram}\nDisk: {$disk}\nStatus: {$status}\nExpired: {$expired}";
@endphp

<style>
.vps-detail-wrap{display:flex;flex-direction:column;gap:24px}
.vps-detail-hero{border-radius:28px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(124,58,237,.28),transparent 35%),radial-gradient(circle at bottom left,rgba(16,185,129,.18),transparent 36%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(49,46,129,.72));box-shadow:0 24px 60px rgba(0,0,0,.25)}
.vps-detail-top{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap}
.vps-pill{display:inline-flex;padding:7px 14px;border-radius:999px;border:1px solid rgba(196,181,253,.35);color:#ddd6fe;background:rgba(124,58,237,.12);font-size:12px;font-weight:900;margin-bottom:14px}
.vps-title{color:white;font-size:34px;font-weight:950;margin:0;word-break:break-all}
.vps-desc{color:#cbd5e1;font-size:15px;line-height:1.7;margin-top:10px}
.vps-badge{display:inline-flex;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:950;text-transform:uppercase}
.vps-badge.active{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.vps-badge.pending{color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(37,99,235,.24)}
.vps-badge.suspended{color:#fde047;background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.24)}
.vps-badge.expired,.vps-badge.terminated,.vps-badge.failed{color:#fca5a5;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.24)}
.vps-grid{display:grid;grid-template-columns:1.25fr .75fr;gap:20px}
.vps-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.80);box-shadow:0 20px 45px rgba(0,0,0,.18);padding:24px}
.vps-card h2{color:white;font-size:20px;font-weight:950;margin:0 0 18px 0}
.vps-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.vps-info{padding:16px;border-radius:17px;background:rgba(30,41,59,.50);border:1px solid rgba(148,163,184,.12)}
.vps-label{color:#94a3b8;font-size:12px;font-weight:850}
.vps-value{color:white;font-size:15px;font-weight:950;margin-top:7px;word-break:break-all}
.vps-actions{display:grid;grid-template-columns:1fr;gap:12px}
.vps-btn{display:inline-flex;align-items:center;justify-content:center;text-align:center;text-decoration:none;border:none;cursor:pointer;border-radius:15px;padding:13px 16px;font-size:14px;font-weight:950}
.vps-btn-primary{color:white;background:linear-gradient(135deg,#7c3aed,#2563eb)}
.vps-btn-green{color:white;background:#16a34a}
.vps-btn-dark{color:#e2e8f0;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16)}
.vps-login-box{border-radius:20px;padding:18px;background:rgba(2,6,23,.45);border:1px solid rgba(255,255,255,.10);margin-bottom:16px}
.vps-login-title{color:white;font-size:17px;font-weight:950}
.vps-login-sub{color:#94a3b8;font-size:13px;margin-top:6px;line-height:1.6}
.vps-guide{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.vps-guide-card{padding:20px;border-radius:20px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.72)}
.vps-guide-card h3{color:white;font-size:16px;font-weight:950;margin:0 0 10px 0}
.vps-guide-card p{color:#94a3b8;font-size:13px;line-height:1.6;margin:0}
@media(max-width:1000px){.vps-grid,.vps-guide{grid-template-columns:1fr}}
@media(max-width:720px){.vps-title{font-size:27px}.vps-info-grid{grid-template-columns:1fr}}
</style>

<div class="vps-detail-wrap">

    <section class="vps-detail-hero">
        <div class="vps-detail-top">
            <div>
                <div class="vps-pill">🖥️ VPS Detail</div>
                <h1 class="vps-title">{{ $name }}</h1>
                <p class="vps-desc">
                    Detail akses VPS, IP server, spesifikasi, sistem operasi, dan masa aktif layanan.
                </p>
            </div>

            <span class="vps-badge {{ $statusClass }}">
                {{ $status }}
            </span>
        </div>
    </section>

    <section class="vps-grid">
        <div class="vps-card">
            <h2>Informasi VPS</h2>

            <div class="vps-info-grid">
                <div class="vps-info">
                    <div class="vps-label">IP Address</div>
                    <div class="vps-value">{{ $ip }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">Username</div>
                    <div class="vps-value">{{ $username }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">Password</div>
                    <div class="vps-value">{{ $password }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">Operating System</div>
                    <div class="vps-value">{{ $os }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">Package</div>
                    <div class="vps-value">{{ $package }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">CPU</div>
                    <div class="vps-value">{{ $cpu }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">RAM</div>
                    <div class="vps-value">{{ $ram }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">Disk</div>
                    <div class="vps-value">{{ $disk }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">Mulai</div>
                    <div class="vps-value">{{ $created }}</div>
                </div>

                <div class="vps-info">
                    <div class="vps-label">Expired</div>
                    <div class="vps-value">{{ $expired }}</div>
                </div>
            </div>
        </div>

        <div class="vps-card">
            <h2>Akses & Support</h2>

            <div class="vps-login-box">
                <div class="vps-login-title">SSH Login</div>
                <div class="vps-login-sub">
                    Linux VPS: gunakan SSH ke IP address dengan username dan password di halaman ini.
                    Windows VPS: gunakan Remote Desktop / RDP.
                </div>
            </div>

            <div class="vps-actions">
                <button type="button" class="vps-btn vps-btn-primary" onclick='copyText(@json($copyText))'>
                    Copy Info Login
                </button>

                <button type="button" class="vps-btn vps-btn-dark" onclick="copyText('ssh {{ $username }}@{{ $ip }}')">
                    Copy Command SSH
                </button>


                <a href="{{ route('client.vps-services') }}" class="vps-btn vps-btn-dark">
                    Kembali
                </a>
            </div>
        </div>
    </section>

    <section class="vps-guide">
        <div class="vps-guide-card">
            <h3>Linux VPS</h3>
            <p>
                Login menggunakan SSH. Contoh: ssh root@IP-VPS. Setelah login, segera update sistem dan ganti password.
            </p>
        </div>

        <div class="vps-guide-card">
            <h3>Windows VPS</h3>
            <p>
                Login menggunakan Remote Desktop Connection / RDP ke IP VPS dengan username dan password yang tersedia.
            </p>
        </div>

        <div class="vps-guide-card">
            <h3>Tiket Support</h3>
            <p>
                Jika VPS tidak bisa login, IP tidak aktif, atau butuh reinstall OS, gunakan menu Support Tickets di sidebar.
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
