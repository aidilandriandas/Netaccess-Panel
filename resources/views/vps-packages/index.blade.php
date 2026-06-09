@extends('layouts.app')
@section('title', 'VPS Packages')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari paket VPS..."
               class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-blue-500 outline-none">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Filter</button>
    </form>
    <a href="{{ route('vps-packages.create') }}" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Paket
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="px-4 py-3">Nama</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">CPU</th><th class="px-4 py-3">RAM</th><th class="px-4 py-3">Storage</th><th class="px-4 py-3">Cycle</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($packages as $pkg)
                <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ $pkg->name }}</td>
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">Rp {{ number_format($pkg->price, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $pkg->cpu_cores }} Core</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $pkg->ram_mb >= 1024 ? ($pkg->ram_mb / 1024) . ' GB' : $pkg->ram_mb . ' MB' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $pkg->storage_gb }} GB</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ ucfirst($pkg->billing_cycle) }}</td>
                    <td class="px-4 py-3">
                        @if($pkg->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('vps-packages.edit', $pkg) }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('vps-packages.destroy', $pkg) }}" x-data @submit.prevent="if(confirm('Hapus paket ini?')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-500 hover:text-red-600 dark:text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada paket VPS.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $packages->links() }}</div>
</div>
@endsection
