@extends('layouts.client')
@section('title', 'VPS Guide')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Panduan VPS</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">Cara mengakses dan mengelola VPS Anda.</p>
    </div>

    {{-- SSH Access --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">1. Akses via SSH (Linux/Mac)</h3>
        <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <p>Buka terminal dan jalankan perintah:</p>
            <code class="block bg-gray-100 dark:bg-gray-700 rounded px-4 py-3 text-xs font-mono">ssh username@ip_address -p port</code>
            <p>Contoh:</p>
            <code class="block bg-gray-100 dark:bg-gray-700 rounded px-4 py-3 text-xs font-mono">ssh root@103.123.456.789 -p 22</code>
            <p>Masukkan password ketika diminta.</p>
        </div>
    </div>

    {{-- PuTTY --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">2. Akses via PuTTY (Windows)</h3>
        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <ol class="list-decimal list-inside space-y-1">
                <li>Download <a href="https://www.putty.org/" target="_blank" class="text-blue-600 hover:text-blue-800">PuTTY</a> jika belum punya.</li>
                <li>Buka PuTTY, masukkan <strong>Host Name</strong> = IP VPS Anda.</li>
                <li>Masukkan <strong>Port</strong> = SSH port (biasanya 22).</li>
                <li>Klik <strong>Open</strong>.</li>
                <li>Login dengan username dan password yang diberikan admin.</li>
            </ol>
        </div>
    </div>

    {{-- RDP --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">3. Akses via Remote Desktop (Windows VPS)</h3>
        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <ol class="list-decimal list-inside space-y-1">
                <li>Buka <strong>Remote Desktop Connection</strong> (ketik <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">mstsc</code> di Run).</li>
                <li>Masukkan IP VPS di kolom <strong>Computer</strong>.</li>
                <li>Klik <strong>Connect</strong>.</li>
                <li>Masukkan username dan password.</li>
            </ol>
        </div>
    </div>

    {{-- Tips --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">4. Tips Keamanan</h3>
        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <ul class="list-disc list-inside space-y-1">
                <li>Segera ganti password default setelah login pertama.</li>
                <li>Gunakan SSH key authentication untuk keamanan lebih baik.</li>
                <li>Update sistem operasi secara berkala.</li>
                <li>Aktifkan firewall (ufw/iptables) dan hanya buka port yang diperlukan.</li>
                <li>Backup data penting secara rutin.</li>
            </ul>
        </div>
    </div>

    {{-- Support --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-6">
        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">Butuh Bantuan?</h3>
        <p class="text-sm text-blue-700 dark:text-blue-400">Hubungi admin jika mengalami masalah dengan akses VPS atau perlu bantuan teknis.</p>
    </div>
</div>
@endsection
