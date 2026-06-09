@extends('layouts.app')
@section('title', 'Activity Logs')

@section('content')
<div class="mb-4">
    <form method="GET" class="flex gap-2 items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari action/deskripsi..."
               class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-blue-500 outline-none">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">Cari</button>
    </form>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="px-4 py-3">Waktu</th><th class="px-4 py-3">User</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Deskripsi</th><th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-4 py-3"><span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">{{ $log->action }}</span></td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $log->description }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada log.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
