@extends('layouts.client')
@section('title', 'Support Tickets')

@section('content')
<style>
.ticket-wrap{display:flex;flex-direction:column;gap:24px}
.ticket-hero{border-radius:26px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(16,185,129,.24),transparent 35%),radial-gradient(circle at bottom left,rgba(37,99,235,.20),transparent 36%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(20,83,45,.62));box-shadow:0 24px 60px rgba(0,0,0,.25)}
.ticket-top{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.ticket-pill{display:inline-flex;padding:7px 14px;border-radius:999px;border:1px solid rgba(134,239,172,.32);color:#bbf7d0;background:rgba(34,197,94,.12);font-size:12px;font-weight:900;margin-bottom:14px}
.ticket-title{color:white;font-size:34px;font-weight:950;margin:0}
.ticket-desc{color:#cbd5e1;margin-top:10px;line-height:1.7}
.ticket-btn{display:inline-flex;text-decoration:none;border-radius:14px;padding:12px 16px;color:white;background:linear-gradient(135deg,#16a34a,#2563eb);font-weight:950}
.ticket-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.80);box-shadow:0 20px 45px rgba(0,0,0,.18);overflow:hidden}
.ticket-row{display:grid;grid-template-columns:1.2fr .55fr .45fr .45fr .45fr;gap:16px;padding:18px 22px;border-bottom:1px solid rgba(148,163,184,.12);align-items:center}
.ticket-head{color:#94a3b8;font-size:12px;text-transform:uppercase;letter-spacing:.06em;font-weight:950}
.ticket-subject{color:white;font-weight:950;text-decoration:none}
.ticket-meta{color:#94a3b8;font-size:13px;margin-top:4px}
.ticket-text{color:#cbd5e1;font-size:14px}
.ticket-badge{display:inline-flex;width:max-content;padding:7px 11px;border-radius:999px;font-size:12px;font-weight:950;text-transform:uppercase}
.ticket-badge.open{color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(37,99,235,.24)}
.ticket-badge.answered{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.ticket-badge.closed{color:#cbd5e1;background:rgba(100,116,139,.18);border:1px solid rgba(148,163,184,.20)}
.ticket-empty{text-align:center;padding:55px 22px;color:#94a3b8}
@media(max-width:900px){.ticket-row{grid-template-columns:1fr}.ticket-head{display:none}.ticket-title{font-size:28px}}
</style>

<div class="ticket-wrap">
    <section class="ticket-hero">
        <div class="ticket-top">
            <div>
                <div class="ticket-pill">🎫 Support Center</div>
                <h1 class="ticket-title">Support Tickets</h1>
                <div class="ticket-desc">
                    Buat tiket jika ada kendala layanan VPN, Hosting, VPS, invoice, atau bantuan teknis lainnya.
                </div>
            </div>

            <a href="{{ route('client.tickets.create') }}" class="ticket-btn">
                Buat Tiket Baru
            </a>
        </div>
    </section>

    <section class="ticket-card">
        <div class="ticket-row ticket-head">
            <div>Subject</div>
            <div>Kategori</div>
            <div>Prioritas</div>
            <div>Status</div>
            <div>Update</div>
        </div>

        @forelse($tickets as $ticket)
            <div class="ticket-row">
                <div>
                    <a href="{{ route('client.tickets.show', $ticket) }}" class="ticket-subject">
                        {{ $ticket->subject }}
                    </a>
                    <div class="ticket-meta">{{ $ticket->ticket_number }}</div>
                    @if($ticket->service_label)
                        <div class="ticket-meta">Layanan: {{ $ticket->service_label }}</div>
                    @endif
                </div>

                <div class="ticket-text">{{ ucfirst($ticket->category) }}</div>
                <div class="ticket-text">{{ ucfirst($ticket->priority) }}</div>

                <div>
                    <span class="ticket-badge {{ $ticket->status }}">
                        {{ $ticket->status }}
                    </span>
                </div>

                <div class="ticket-text">
                    {{ $ticket->last_reply_at?->diffForHumans() ?? '-' }}
                </div>
            </div>
        @empty
            <div class="ticket-empty">
                <div style="font-size:46px;margin-bottom:12px;">🎫</div>
                <div style="color:white;font-size:22px;font-weight:950;">Belum ada tiket</div>
                <div style="margin-top:8px;">Kalau ada kendala layanan, buat tiket baru dari tombol di atas.</div>
            </div>
        @endforelse

        @if($tickets->hasPages())
            <div style="padding:18px 22px;border-top:1px solid rgba(148,163,184,.14);">
                {{ $tickets->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
