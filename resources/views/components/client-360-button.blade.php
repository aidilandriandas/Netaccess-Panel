@props([
    'userId' => null,
    'customerId' => null,
    'label' => 'Client 360',
])

@php
    $url = null;

    if ($userId) {
        $url = route('admin.client-history.show', $userId);
    } elseif ($customerId) {
        $url = route('admin.client-history.customer', $customerId);
    }
@endphp

@if($url)
    <a href="{{ $url }}"
       style="display:inline-flex;align-items:center;gap:7px;text-decoration:none;border-radius:12px;padding:9px 12px;color:white;background:linear-gradient(135deg,#2563eb,#16a34a);font-size:12px;font-weight:950;">
        👤 {{ $label }}
    </a>
@endif
