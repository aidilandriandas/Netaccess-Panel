@extends('layouts.app')
@section('title', 'Full Backups')

@section('content')
@php
    use Illuminate\Support\Facades\File;

    if (!isset($files)) {
        $backupDir = storage_path('app/full-backups');
        File::ensureDirectoryExists($backupDir);

        $files = collect(File::files($backupDir))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.zip'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => round($file->getSize() / 1024 / 1024, 2) . ' MB',
                'created' => date('d M Y H:i', $file->getMTime()),
            ])
            ->values();
    }
@endphp

<style>
.bk-page{display:flex;flex-direction:column;gap:16px}
.bk-hero{position:relative;overflow:hidden;border-radius:26px;padding:22px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 20px 55px rgba(0,0,0,.22)}
.bk-hero:before{content:"";position:absolute;right:-90px;top:-110px;width:280px;height:280px;border-radius:999px;background:conic-gradient(from 180deg,rgba(16,185,129,.34),rgba(37,99,235,.26),rgba(124,58,237,.13));animation:bkSpin 20s linear infinite;opacity:.55}
@keyframes bkSpin{to{transform:rotate(360deg)}}
.bk-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.bk-pill{display:inline-flex;padding:7px 12px;border-radius:999px;color:#bbf7d0;background:rgba(34,197,94,.14);border:1px solid rgba(134,239,172,.22);font-size:11px;font-weight:950;margin-bottom:10px}
.bk-title{margin:0;color:#fff;font-size:31px;font-weight:1000;letter-spacing:-.045em}
.bk-sub{margin-top:7px;color:#cbd5e1;font-size:13px}
.bk-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:0;border-radius:14px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;cursor:pointer;transition:.2s ease}
.bk-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.bk-btn.primary{color:white;background:linear-gradient(135deg,#10b981,#2563eb);border-color:transparent;box-shadow:0 14px 30px rgba(16,185,129,.22)}
.bk-btn.red{color:#fecaca;background:rgba(239,68,68,.13)}
.bk-grid{display:grid;grid-template-columns:320px minmax(0,1fr);gap:16px}
.bk-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.bk-head{padding:16px 18px;border-bottom:1px solid rgba(148,163,184,.10);display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.bk-dot{width:9px;height:9px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 5px rgba(34,197,94,.13)}
.bk-body{padding:18px}
.bk-note{color:#94a3b8;font-size:13px;line-height:1.65;margin-bottom:16px}
.bk-alert{border-radius:16px;padding:13px 14px;font-size:13px;font-weight:850;margin-bottom:14px}
.bk-alert.success{color:#bbf7d0;background:rgba(34,197,94,.12);border:1px solid rgba(134,239,172,.18)}
.bk-alert.error{color:#fecaca;background:rgba(239,68,68,.12);border:1px solid rgba(252,165,165,.18)}
.bk-table-wrap{overflow-x:auto}
.bk-table{width:100%;border-collapse:collapse}
.bk-table th,.bk-table td{padding:14px 16px;border-bottom:1px solid rgba(148,163,184,.09);font-size:13px;text-align:left;white-space:nowrap}
.bk-table th{color:#94a3b8;font-size:10px;text-transform:uppercase;letter-spacing:.075em;font-weight:1000}
.bk-table td{color:#dbeafe}
.bk-main{color:white;font-weight:950}
.bk-muted{color:#94a3b8;font-size:12px;margin-top:2px}
.bk-actions{display:flex;gap:8px;flex-wrap:wrap}
.bk-empty{padding:34px;text-align:center;color:#94a3b8}
.bk-loading{display:none;margin-top:12px;color:#bfdbfe;font-size:12px;font-weight:900}
@media(max-width:1000px){.bk-grid{grid-template-columns:1fr}}
</style>

<div class="bk-page">

    <section class="bk-hero">
        <div class="bk-inner">
            <div>
                <div class="bk-pill">💾 One Click Full Backup</div>
                <h1 class="bk-title">Full Backups</h1>
                <div class="bk-sub">Backup web Laravel + database MySQL jadi satu file ZIP.</div>
            </div>

            <a href="{{ route('dashboard') }}" class="bk-btn">← Dashboard</a>
        </div>
    </section>

    @if(session('success'))
        <div class="bk-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="bk-alert error">{{ session('error') }}</div>
    @endif

    <section class="bk-grid">

        <aside class="bk-card">
            <div class="bk-head">
                <span class="bk-dot"></span>
                Backup Sekarang
            </div>

            <div class="bk-body">
                <div class="bk-note">
                    Sekali klik akan membuat backup berisi:
                    <br>✅ File web Laravel
                    <br>✅ File .env
                    <br>✅ Database .sql
                    <br>✅ Catatan restore
                </div>

                <form action="{{ route('admin.full-backups.store') }}" method="POST" onsubmit="document.getElementById('bkLoading').style.display='block'; this.querySelector('button').disabled=true;">
                    @csrf
                    <button type="submit" class="bk-btn primary" style="width:100%;">
                        Buat Full Backup
                    </button>

                    <div id="bkLoading" class="bk-loading">
                        Lagi proses backup... jangan refresh halaman.
                    </div>
                </form>
            </div>
        </aside>

        <div class="bk-card">
            <div class="bk-head">
                <span class="bk-dot"></span>
                File Backup
            </div>

            <div class="bk-table-wrap">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($files as $file)
                            <tr>
                                <td>
                                    <div class="bk-main">{{ $file['name'] }}</div>
                                    <div class="bk-muted">Full web + database</div>
                                </td>
                                <td>{{ $file['size'] }}</td>
                                <td>{{ $file['created'] }}</td>
                                <td>
                                    <div class="bk-actions">
                                        <a href="{{ route('admin.full-backups.download', $file['name']) }}" class="bk-btn">
                                            Download
                                        </a>

                                        <form action="{{ route('admin.full-backups.destroy', $file['name']) }}" method="POST" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bk-btn red" onclick="return confirm('Hapus backup ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="bk-empty">Belum ada file backup.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>

</div>
@endsection
