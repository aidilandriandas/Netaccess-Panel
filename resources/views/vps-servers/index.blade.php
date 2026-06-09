@extends('layouts.app')
@section('title', 'VPS Servers')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari server..."
               class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-blue-500 outline-none">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Filter</button>
    </form>
    <a href="{{ route('vps-servers.create') }}" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Server
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="px-4 py-3">Nama</th><th class="px-4 py-3">Host</th><th class="px-4 py-3">Provider</th><th class="px-4 py-3">Location</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">VPS</th><th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($servers as $server)
                <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ $server->name }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $server->host }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ ucfirst($server->provider_type) }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $server->location }}</td>
                    <td class="px-4 py-3">
                        <x-status-badge :status="$server->status" />
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $server->vps_services_count ?? $server->vpsServices()->count() }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('vps-servers.edit', $server) }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('vps-servers.destroy', $server) }}" x-data @submit.prevent="if(confirm('Hapus server ini?')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-500 hover:text-red-600 dark:text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada VPS server.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $servers->links() }}</div>
</div>
@endsection
