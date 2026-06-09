@extends('layouts.client')
@section('title', 'Detail VPN Saya')

@section('content')
@php
    $vpn = $vpnUser ?? $service ?? $vpnService ?? null;

    $vpnServer = \App\Models\Setting::get('mikrotik_public_ip')
        ?? \App\Models\Setting::get('mikrotik_host')
        ?? '-';

    $vpnPsk = \App\Models\Setting::get('mikrotik_ipsec_secret')
        ?? \App\Models\Setting::get('ipsec_preshared_key')
        ?? \App\Models\Setting::get('l2tp_ipsec_secret')
        ?? '-';

    $vpnPassword = $vpn?->l2tp_password
        ?? $vpn?->password
        ?? '-';
@endphp

@if(!$vpn)
    <div class="bg-red-900/30 border border-red-700 text-red-300 px-4 py-3 rounded-xl">
        Data VPN tidak ditemukan.
    </div>
@else

<div class="space-y-6">

    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Detail VPN Saya
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Gunakan data berikut untuk konek ke VPN L2TP/IPSec.
            </p>
        </div>

        <a href="{{ url()->previous() }}"
           class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-white text-sm">
            Kembali
        </a>
    </div>

    <div class="bg-gradient-to-br from-blue-900/50 via-indigo-900/40 to-gray-900 border border-blue-700/40 rounded-2xl p-6 shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">
                    Akun VPN L2TP/IPSec
                </h2>
                <p class="text-sm text-blue-200 mt-1">
                    Jangan bagikan username, password, dan IPSec Key kepada orang lain.
                </p>
            </div>

            <span class="inline-flex w-fit px-3 py-1 rounded-full text-xs font-bold
                {{ $vpn->status === 'active' ? 'bg-green-500/20 text-green-300 border border-green-500/30' : 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' }}">
                {{ strtoupper($vpn->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-gray-950/50 rounded-xl p-4 border border-white/10">
                <div class="text-xs text-blue-200 mb-1">Server VPN</div>
                <div class="flex items-center justify-between gap-2">
                    <div class="font-bold text-white text-lg">{{ $vpnServer }}</div>
                    <button onclick="copyText('{{ $vpnServer }}')"
                            class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg">
                        Copy
                    </button>
                </div>
            </div>

            <div class="bg-gray-950/50 rounded-xl p-4 border border-white/10">
                <div class="text-xs text-blue-200 mb-1">Tipe VPN</div>
                <div class="font-bold text-white text-lg">L2TP/IPSec PSK</div>
            </div>

            <div class="bg-gray-950/50 rounded-xl p-4 border border-white/10">
                <div class="text-xs text-blue-200 mb-1">Username</div>
                <div class="flex items-center justify-between gap-2">
                    <div class="font-bold text-white text-lg">{{ $vpn->username }}</div>
                    <button onclick="copyText('{{ $vpn->username }}')"
                            class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg">
                        Copy
                    </button>
                </div>
            </div>

            <div class="bg-gray-950/50 rounded-xl p-4 border border-white/10">
                <div class="text-xs text-blue-200 mb-1">Password</div>
                <div class="flex items-center justify-between gap-2">
                    <div class="font-bold text-white text-lg">{{ $vpnPassword }}</div>
                    <button onclick="copyText('{{ $vpnPassword }}')"
                            class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg">
                        Copy
                    </button>
                </div>
            </div>

            <div class="bg-gray-950/50 rounded-xl p-4 border border-white/10">
                <div class="text-xs text-blue-200 mb-1">IPSec Pre-shared Key</div>
                <div class="flex items-center justify-between gap-2">
                    <div class="font-bold text-white text-lg">{{ $vpnPsk }}</div>
                    <button onclick="copyText('{{ $vpnPsk }}')"
                            class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg">
                        Copy
                    </button>
                </div>
            </div>

            <div class="bg-gray-950/50 rounded-xl p-4 border border-white/10">
                <div class="text-xs text-blue-200 mb-1">Expired</div>
                <div class="font-bold text-white text-lg">
                    {{ $vpn->expired_at?->format('d M Y') ?? '-' }}
                </div>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                Panduan Koneksi
            </h2>

            <div class="space-y-4">

                <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                        Android
                    </h3>
                    <ol class="list-decimal list-inside text-sm text-gray-600 dark:text-gray-300 space-y-1">
                        <li>Buka Settings / Pengaturan.</li>
                        <li>Masuk ke Network & Internet lalu pilih VPN.</li>
                        <li>Tambah VPN baru.</li>
                        <li>Pilih tipe <b>L2TP/IPSec PSK</b>.</li>
                        <li>Isi Server VPN, Username, Password, dan IPSec Key.</li>
                    </ol>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                        iPhone / iPad
                    </h3>
                    <ol class="list-decimal list-inside text-sm text-gray-600 dark:text-gray-300 space-y-1">
                        <li>Buka Settings.</li>
                        <li>Masuk ke General.</li>
                        <li>Pilih VPN & Device Management.</li>
                        <li>Add VPN Configuration.</li>
                        <li>Type pilih <b>L2TP</b>.</li>
                        <li>Isi Server, Account, Password, dan Secret.</li>
                    </ol>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                        Windows
                    </h3>
                    <ol class="list-decimal list-inside text-sm text-gray-600 dark:text-gray-300 space-y-1">
                        <li>Buka Settings.</li>
                        <li>Masuk ke Network & Internet.</li>
                        <li>Pilih VPN lalu Add VPN.</li>
                        <li>VPN Provider pilih <b>Windows built-in</b>.</li>
                        <li>VPN Type pilih <b>L2TP/IPSec with pre-shared key</b>.</li>
                        <li>Isi Server VPN, Username, Password, dan IPSec Key.</li>
                    </ol>
                </div>

            </div>
        </div>

        <div class="space-y-6">

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    Ringkasan Layanan
                </h2>

                <div class="space-y-3 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Paket</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $vpn->package?->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Status</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ ucfirst($vpn->status) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Mulai</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $vpn->started_at?->format('d M Y') ?? $vpn->created_at?->format('d M Y') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Expired</div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $vpn->expired_at?->format('d M Y') ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-900/20 border border-yellow-700/40 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-yellow-300 mb-3">
                    Catatan Penting
                </h2>

                <ul class="list-disc list-inside text-sm text-yellow-100/90 space-y-2">
                    <li>Gunakan <b>Server VPN</b>, bukan IP private.</li>
                    <li>IP private VPN akan otomatis didapat setelah berhasil konek.</li>
                    <li>Jangan bagikan password VPN ke orang lain.</li>
                    <li>Kalau gagal konek, cek ulang Username, Password, dan IPSec Key.</li>
                </ul>
            </div>

        </div>

    </div>

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

@endif
@endsection
