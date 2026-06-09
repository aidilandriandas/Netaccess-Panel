@extends('layouts.app')
@section('title', 'Server Status')

@section('content')
<div class="space-y-6">
    {{-- System Resources --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">CPU Usage</h3>
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $report['cpu']['percentage'] }}%</p>
            @if(isset($report['cpu']['cores']))<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $report['cpu']['cores'] }} cores</p>@endif
            @if(isset($report['cpu']['note']))<p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">{{ $report['cpu']['note'] }}</p>@endif
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mt-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: {{ min($report['cpu']['percentage'], 100) }}%"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">RAM Usage</h3>
            <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $report['ram']['percentage'] }}%</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $report['ram']['used'] }} / {{ $report['ram']['total'] }}</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mt-3">
                <div class="bg-green-600 h-3 rounded-full transition-all" style="width: {{ min($report['ram']['percentage'], 100) }}%"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">Disk Usage</h3>
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $report['disk']['percentage'] }}%</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $report['disk']['used'] }} / {{ $report['disk']['total'] }}</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mt-3">
                <div class="bg-yellow-600 h-3 rounded-full transition-all" style="width: {{ min($report['disk']['percentage'], 100) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Uptime & Load --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">Uptime</h3>
            <p class="text-lg text-gray-800 dark:text-gray-200">{{ $report['uptime'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">Load Average</h3>
            <div class="flex gap-4 text-sm">
                <div><span class="text-gray-500 dark:text-gray-400">1 min:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ $report['load_average']['1min'] }}</span></div>
                <div><span class="text-gray-500 dark:text-gray-400">5 min:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ $report['load_average']['5min'] }}</span></div>
                <div><span class="text-gray-500 dark:text-gray-400">15 min:</span> <span class="font-medium text-gray-800 dark:text-gray-200">{{ $report['load_average']['15min'] }}</span></div>
            </div>
        </div>
    </div>

    {{-- Services --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Service Status</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($report['services'] as $service => $status)
            <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                @if($status === 'active')
                <div class="w-2.5 h-2.5 bg-green-500 rounded-full"></div>
                @elseif($status === 'inactive')
                <div class="w-2.5 h-2.5 bg-red-500 rounded-full"></div>
                @else
                <div class="w-2.5 h-2.5 bg-gray-400 rounded-full"></div>
                @endif
                <div>
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $service }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $status }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
