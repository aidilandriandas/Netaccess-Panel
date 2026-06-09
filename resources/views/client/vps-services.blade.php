@extends('layouts.client')
@section('title', 'My VPS')

@section('content')
@php
    $vpsRows = $vpsServices ?? $vpsAccounts ?? $services ?? collect();

    if (is_array($vpsRows)) {
        $vpsRows = collect($vpsRows);
    }

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

    $activeCount = $vpsRows->where('status', 'active')->count();
    $suspendedCount = $vpsRows->where('status', 'suspended')->count();
    $totalCount = $vpsRows->count();

@endphp

<style>
.vps-wrap{display:flex;flex-direction:column;gap:24px}
.vps-hero{border-radius:26px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(124,58,237,.28),transparent 35%),radial-gradient(circle at bottom left,rgba(16,185,129,.18),transparent 36%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(49,46,129,.72));box-shadow:0 24px 60px rgba(0,0,0,.25)}
.vps-hero-grid{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr);gap:24px;align-items:center}
.vps-pill{display:inline-flex;padding:7px 14px;border-radius:999px;border:1px solid rgba(196,181,253,.35);color:#ddd6fe;background:rgba(124,58,237,.12);font-size:12px;font-weight:900;margin-bottom:16px}
.vps-title{color:white;font-size:34px;line-height:1.1;font-weight:950;margin:0}
.vps-desc{color:#cbd5e1;font-size:15px;line-height:1.7;margin-top:12px;max-width:720px}
.vps-panel-card{border-radius:22px;padding:22px;background:rgba(2,6,23,.50);border:1px solid rgba(255,255,255,.10)}
.vps-label{color:#94a3b8;font-size:13px;font-weight:800}
.vps-panel-title{color:white;font-size:26px;font-weight:950;margin-top:6px}
.vps-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:18px}
.vps-mini{padding:14px;border-radius:15px;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.06)}
.vps-mini strong{display:block;color:white;margin-top:5px}
.vps-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.vps-stat{border-radius:22px;padding:22px;min-height:140px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.78);box-shadow:0 20px 45px rgba(0,0,0,.18)}
.vps-stat.total{background:linear-gradient(135deg,rgba(124,58,237,.22),rgba(15,23,42,.84))}
.vps-stat.active{background:linear-gradient(135deg,rgba(34,197,94,.18),rgba(15,23,42,.84))}
.vps-stat.suspended{background:linear-gradient(135deg,rgba(234,179,8,.20),rgba(15,23,42,.84))}
.vps-stat-icon{font-size:28px;margin-bottom:14px}
.vps-stat-title{color:#cbd5e1;font-size:14px;font-weight:900}
.vps-stat-number{color:white;font-size:34px;font-weight:950;margin-top:8px}
.vps-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.vps-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(124,58,237,.13),transparent 36%),rgba(15,23,42,.80);box-shadow:0 20px 45px rgba(0,0,0,.18);overflow:hidden}
.vps-card-head{padding:22px;display:flex;justify-content:space-between;gap:14px;align-items:flex-start;border-bottom:1px solid rgba(148,163,184,.12)}
.vps-name{color:white;font-size:20px;font-weight:950;word-break:break-all}
.vps-package{color:#94a3b8;font-size:13px;margin-top:5px}
.vps-badge{display:inline-flex;padding:7px 11px;border-radius:999px;font-size:12px;font-weight:950;text-transform:uppercase}
.vps-badge.active{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.vps-badge.pending{color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(37,99,235,.24)}
.vps-badge.suspended{color:#fde047;background:rgba(234,179,8,.15);border:1px solid rgba(234,179,8,.24)}
.vps-badge.expired,.vps-badge.terminated,.vps-badge.failed{color:#fca5a5;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.24)}
.vps-card-body{padding:22px}
.vps-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.vps-info{padding:14px;border-radius:16px;background:rgba(30,41,59,.50);border:1px solid rgba(148,163,184,.12)}
.vps-info .label{color:#94a3b8;font-size:12px;font-weight:800}
.vps-info .value{color:white;font-size:14px;font-weight:900;margin-top:5px;word-break:break-all}
.vps-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
.vps-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:none;cursor:pointer;border-radius:13px;padding:11px 14px;font-size:13px;font-weight:950}
.vps-btn-primary{color:white;background:linear-gradient(135deg,#7c3aed,#2563eb)}
.vps-btn-secondary{color:#e2e8f0;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16)}
.vps-empty{border-radius:24px;padding:50px 24px;text-align:center;border:1px dashed rgba(148,163,184,.26);background:rgba(15,23,42,.58)}
.vps-empty-icon{font-size:46px;margin-bottom:14px}
.vps-empty-title{color:white;font-size:22px;font-weight:950}
.vps-empty-desc{color:#94a3b8;margin-top:8px;font-size:14px}
.vps-guide{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.vps-guide-card{padding:20px;border-radius:20px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.72)}
.vps-guide-card h3{color:white;font-size:16px;font-weight:950;margin:0 0 10px 0}
.vps-guide-card p{color:#94a3b8;font-size:13px;line-height:1.6;margin:0}
@media(max-width:1100px){.vps-hero-grid,.vps-grid,.vps-guide{grid-template-columns:1fr}}
@media(max-width:720px){.vps-stats,.vps-mini-grid,.vps-info-grid{grid-template-columns:1fr}.vps-title{font-size:28px}.vps-panel-title{font-size:22px}}
</style>

<div class="vps-wrap">

    <section class="vps-hero">
        <div class="vps-hero-grid">
            <div>
                <div class="vps-pill">🖥️ VPS Services</div>

                <h1 class="vps-title">My VPS</h1>

                <p class="vps-desc">
                    Di halaman ini kamu bisa melihat layanan VPS, IP server, spesifikasi,
                    status layanan, akses login, dan masa expired. Untuk bantuan teknis, nanti bisa melalui menu Support Tickets di sidebar.
                </p>
            </div>

            <div class="vps-panel-card">
                <div class="vps-label">Support Channel</div>
                <div class="vps-panel-title">Ticket Support</div>

                <div class="vps-mini-grid">
                    <div class="vps-mini">
                        <div class="vps-label">Kategori</div>
                        <strong>VPS / Server</strong>
                    </div>

                    <div class="vps-mini">
                        <div class="vps-label">Response</div>
                        <strong style="color:#86efac;">Admin Panel</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="vps-stats">
        <div class="vps-stat total">
            <div class="vps-stat-icon">🖥️</div>
            <div class="vps-stat-title">Total VPS</div>
            <div class="vps-stat-number">{{ $totalCount }}</div>
        </div>

        <div class="vps-stat active">
            <div class="vps-stat-icon">✅</div>
            <div class="vps-stat-title">VPS Aktif</div>
            <div class="vps-stat-number">{{ $activeCount }}</div>
        </div>

        <div class="vps-stat suspended">
            <div class="vps-stat-icon">⏸️</div>
            <div class="vps-stat-title">VPS Suspended</div>
            <div class="vps-stat-number">{{ $suspendedCount }}</div>
        </div>
    </section>

    @if($vpsRows->count())
        <section class="vps-grid">
            @foreach($vpsRows as $vps)
                @php
                    $status = $vps->status ?? 'active';
                    $statusClass = in_array($status, ['active','pending','suspended','expired','terminated','failed']) ? $status : 'active';

                    $name = $vps->name
                        ?? $vps->hostname
                        ?? $vps->server_name
                        ?? 'VPS-'.$vps->id;

                    $ip = $vps->ip_address
                        ?? $vps->main_ip
                        ?? $vps->public_ip
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

                    $expired = $fmt($vps->expired_at ?? null);
                    $detailUrl = route('client.vps-services.show', $vps);
                @endphp

                <article class="vps-card">
                    <div class="vps-card-head">
                        <div>
                            <div class="vps-name">{{ $name }}</div>
                            <div class="vps-package">{{ $package }}</div>
                        </div>

                        <span class="vps-badge {{ $statusClass }}">
                            {{ $status }}
                        </span>
                    </div>

                    <div class="vps-card-body">
                        <div class="vps-info-grid">
                            <div class="vps-info">
                                <div class="label">IP Address</div>
                                <div class="value">{{ $ip }}</div>
                            </div>

                            <div class="vps-info">
                                <div class="label">CPU</div>
                                <div class="value">{{ $cpu }}</div>
                            </div>

                            <div class="vps-info">
                                <div class="label">RAM</div>
                                <div class="value">{{ $ram }}</div>
                            </div>

                            <div class="vps-info">
                                <div class="label">Disk</div>
                                <div class="value">{{ $disk }}</div>
                            </div>

                            <div class="vps-info">
                                <div class="label">Expired</div>
                                <div class="value">{{ $expired }}</div>
                            </div>

                            <div class="vps-info">
                                <div class="label">Status</div>
                                <div class="value">{{ ucfirst($status) }}</div>
                            </div>
                        </div>

                        <div class="vps-actions">
                            <a href="{{ $detailUrl }}" class="vps-btn vps-btn-primary">
                                Detail VPS
                            </a>

                            <button class="vps-btn vps-btn-secondary"
                                    onclick="copyText('VPS: {{ $name }}\nIP Address: {{ $ip }}\nPackage: {{ $package }}\nCPU: {{ $cpu }}\nRAM: {{ $ram }}\nDisk: {{ $disk }}\nStatus: {{ $status }}\nExpired: {{ $expired }}')">
                                Copy Info
                            </button>

                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @else
        <section class="vps-empty">
            <div class="vps-empty-icon">🖥️</div>
            <div class="vps-empty-title">Belum ada layanan VPS</div>
            <div class="vps-empty-desc">
                Kamu belum memiliki layanan VPS aktif. Silakan order VPS atau hubungi admin melalui tiket support.
            </div>
        </section>
    @endif

    <section class="vps-guide">
        <div class="vps-guide-card">
            <h3>Login VPS</h3>
            <p>
                Gunakan IP address, username, dan password/root key dari detail VPS untuk login via SSH atau RDP.
            </p>
        </div>

        <div class="vps-guide-card">
            <h3>Keamanan</h3>
            <p>
                Setelah VPS aktif, segera ganti password default dan batasi akses port penting.
            </p>
        </div>

        <div class="vps-guide-card">
            <h3>Butuh Bantuan?</h3>
            <p>
                Gunakan menu Support Tickets di sidebar agar admin bisa tracking kendala VPS kamu.
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
