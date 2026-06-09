@extends('layouts.app')

@section('title', 'Payment Confirmations')

@section('content')
@php
    $money = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-black text-white">Payment Confirmations</h1>
            <p class="mt-1 text-sm text-slate-400">Cek dan approve bukti pembayaran manual dari client.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-bold text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-700/60 bg-slate-900/70 shadow-xl">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700/60">
                <thead class="bg-slate-950/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-400">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-400">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-400">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-400">Bank</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-400">Proof</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-wider text-slate-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40">
                    @forelse($confirmations as $confirmation)
                        @php
                            $status = strtolower($confirmation->status ?? 'pending');
                            $badge = $status === 'approved'
                                ? 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30'
                                : ($status === 'rejected'
                                    ? 'bg-red-500/10 text-red-300 border-red-500/30'
                                    : 'bg-yellow-500/10 text-yellow-300 border-yellow-500/30');
                        @endphp

                        <tr class="hover:bg-slate-800/40">
                            <td class="px-4 py-4">
                                <div class="font-black text-white">
                                    {{ $confirmation->invoice->invoice_number ?? 'INV-'.$confirmation->invoice_id }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $confirmation->created_at?->format('d M Y H:i') }}
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-200">
                                    {{ $confirmation->customer->name ?? $confirmation->user->name ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $confirmation->customer->email ?? $confirmation->user->email ?? '' }}
                                </div>
                            </td>

                            <td class="px-4 py-4 font-black text-white">
                                {{ $money($confirmation->amount ?? 0) }}
                            </td>

                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-200">{{ $confirmation->bank_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $confirmation->account_name ?? '' }}</div>
                            </td>

                            <td class="px-4 py-4">
                                @if($confirmation->proof_path)
                                    <a href="{{ asset('storage/'.$confirmation->proof_path) }}" target="_blank"
                                       class="rounded-lg border border-blue-500/30 bg-blue-500/10 px-3 py-2 text-xs font-black text-blue-300 hover:bg-blue-500/20">
                                        Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-sm text-slate-500">Tidak ada</span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <span class="rounded-full border px-3 py-1 text-xs font-black uppercase {{ $badge }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                @if($status === 'pending')
                                    <div class="flex flex-col gap-2">
                                        <form method="POST" action="{{ route('payment-confirmations.approve', $confirmation->id) }}">
                                            @csrf
                                            <input type="hidden" name="admin_notes" value="Approved by admin">
                                            <button class="w-full rounded-lg bg-emerald-600 px-3 py-2 text-xs font-black text-white hover:bg-emerald-500">
                                                Approve
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('payment-confirmations.reject', $confirmation->id) }}">
                                            @csrf
                                            <input type="hidden" name="admin_notes" value="Rejected by admin">
                                            <button class="w-full rounded-lg bg-red-600 px-3 py-2 text-xs font-black text-white hover:bg-red-500"
                                                    onclick="return confirm('Tolak konfirmasi pembayaran ini?')">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-xs text-slate-500">
                                        {{ $confirmation->admin_notes ?? '-' }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                                Belum ada konfirmasi pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-700/60 px-4 py-3">
            {{ $confirmations->links() }}
        </div>
    </div>
</div>
@endsection
