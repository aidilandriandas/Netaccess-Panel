@props(['status'])

@php
$colors = match($status) {
    'active', 'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    'expired', 'overdue', 'failed', 'terminated' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    'suspended', 'offline' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'unpaid', 'pending', 'provisioning', 'maintenance' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    'cancelled' => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
};
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors }}">
    {{ ucfirst($status) }}
</span>
