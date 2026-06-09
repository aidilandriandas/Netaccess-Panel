@extends('layouts.client')
@section('title', 'Detail Tiket')

@section('content')
<style>
.ticket-detail-wrap{display:flex;flex-direction:column;gap:22px}
.ticket-detail-hero{border-radius:24px;padding:24px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(37,99,235,.22),transparent 34%),radial-gradient(circle at bottom left,rgba(16,185,129,.18),transparent 36%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(30,41,59,.88));box-shadow:0 22px 50px rgba(0,0,0,.24)}
.ticket-hero-top{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap}
.ticket-pill{display:inline-flex;padding:7px 13px;border-radius:999px;border:1px solid rgba(147,197,253,.30);color:#bfdbfe;background:rgba(37,99,235,.12);font-size:12px;font-weight:950;margin-bottom:12px}
.ticket-title{color:white;font-size:30px;font-weight:950;margin:0;word-break:break-word}
.ticket-meta{display:flex;flex-wrap:wrap;gap:10px;margin-top:12px}
.ticket-meta span{display:inline-flex;padding:7px 10px;border-radius:999px;background:rgba(15,23,42,.48);border:1px solid rgba(148,163,184,.14);color:#cbd5e1;font-size:12px;font-weight:850}
.ticket-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.ticket-badge{display:inline-flex;padding:9px 13px;border-radius:999px;font-size:12px;font-weight:950;text-transform:uppercase}
.ticket-badge.open{color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(37,99,235,.24)}
.ticket-badge.answered{color:#86efac;background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.24)}
.ticket-badge.closed{color:#cbd5e1;background:rgba(100,116,139,.18);border:1px solid rgba(148,163,184,.20)}
.ticket-btn{display:inline-flex;align-items:center;justify-content:center;border:none;text-decoration:none;cursor:pointer;border-radius:14px;padding:11px 15px;color:white;font-size:13px;font-weight:950}
.ticket-btn-red{background:#dc2626}
.ticket-btn-green{background:#16a34a}
.ticket-btn-dark{color:#e2e8f0;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16)}
.ticket-card{border-radius:24px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.80);box-shadow:0 18px 42px rgba(0,0,0,.18);padding:22px}
.ticket-card-title{font-weight:950;color:white;font-size:18px;margin-bottom:14px}
.service-table-wrap{overflow-x:auto}
.service-table{width:100%;border-collapse:collapse}
.service-table th{padding:12px;text-align:left;color:white;background:rgba(37,99,235,.22);font-size:13px}
.service-table td{padding:12px;color:#e2e8f0;border-bottom:1px solid rgba(148,163,184,.14);font-size:14px}
.msg{padding:16px;border-radius:18px;border:1px solid rgba(148,163,184,.14);background:rgba(30,41,59,.50);margin-bottom:14px}
.msg.admin{background:rgba(37,99,235,.13);border-color:rgba(37,99,235,.22)}
.msg-head{display:flex;justify-content:space-between;gap:12px;color:#94a3b8;font-size:13px;margin-bottom:10px}
.msg-name{color:white;font-weight:950}
.msg-time{color:#94a3b8;font-size:12px}
.msg-body{color:#e2e8f0;line-height:1.7;white-space:pre-wrap}
.attach-box{margin-top:14px;border-radius:16px;border:1px solid rgba(148,163,184,.16);background:rgba(2,6,23,.35);padding:12px}
.attach-img{max-width:100%;max-height:360px;border-radius:14px;display:block;margin-bottom:10px}
.attach-link{display:inline-flex;text-decoration:none;color:#bfdbfe;font-weight:950}
.reply-box textarea,.reply-box input{width:100%;border-radius:16px;border:1px solid rgba(148,163,184,.18);background:rgba(2,6,23,.45);color:white;padding:14px;outline:none}
.reply-label{display:block;color:#cbd5e1;font-weight:900;margin:12px 0 8px 0;font-size:13px}
.reply-btn{border:none;border-radius:14px;padding:13px 16px;color:white;background:linear-gradient(135deg,#16a34a,#2563eb);font-weight:950;cursor:pointer;margin-top:12px}
.closed-box{border-radius:20px;padding:18px;border:1px solid rgba(148,163,184,.16);background:rgba(100,116,139,.10);color:#cbd5e1}
@media(max-width:720px){.ticket-title{font-size:25px}.ticket-hero-top{flex-direction:column}.ticket-actions{width:100%}.ticket-btn{width:100%}.msg-head{flex-direction:column}}
</style>

<div class="ticket-detail-wrap">

    <section class="ticket-detail-hero">
        <div class="ticket-hero-top">
            <div>
                <div class="ticket-pill">🎫 Detail Tiket</div>

                <h1 class="ticket-title">{{ $ticket->subject }}</h1>

                <div class="ticket-meta">
                    <span>{{ $ticket->ticket_number }}</span>
                    <span>{{ ucfirst($ticket->category) }}</span>
                    <span>{{ ucfirst($ticket->priority) }}</span>
                    @if($ticket->service_label)
                        <span>{{ $ticket->service_label }}</span>
                    @endif
                </div>
            </div>

            <div class="ticket-actions">
                <span class="ticket-badge {{ $ticket->status }}">{{ $ticket->status }}</span>

                @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('client.tickets.close', $ticket) }}" onsubmit="return confirm('Tutup tiket ini?')">
                        @csrf
                        <button class="ticket-btn ticket-btn-red">Close Tiket</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('client.tickets.reopen', $ticket) }}" onsubmit="return confirm('Buka ulang tiket ini?')">
                        @csrf
                        <button class="ticket-btn ticket-btn-green">Reopen Tiket</button>
                    </form>
                @endif

                <a href="{{ route('client.tickets.index') }}" class="ticket-btn ticket-btn-dark">Kembali</a>
            </div>
        </div>
    </section>

    @if($ticket->service_label)
        <section class="ticket-card">
            <div class="ticket-card-title">Layanan Terkait</div>

            <div class="service-table-wrap">
                <table class="service-table">
                    <thead>
                        <tr>
                            <th>Product/Service</th>
                            <th>Type</th>
                            <th>Service ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $ticket->service_label }}</td>
                            <td>{{ strtoupper($ticket->service_type) }}</td>
                            <td>#{{ $ticket->service_id }}</td>
                            <td style="color:#86efac;font-weight:950;">Associated</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="ticket-card">
        <div class="ticket-card-title">Conversation</div>

        @foreach($ticket->messages as $message)
            <div class="msg {{ $message->sender_type === 'admin' ? 'admin' : '' }}">
                <div class="msg-head">
                    <div class="msg-name">
                        {{ $message->sender_type === 'admin' ? 'Admin Support' : 'Saya' }}
                    </div>
                    <div class="msg-time">{{ $message->created_at?->format('d M Y H:i') }}</div>
                </div>

                <div class="msg-body">{{ $message->message }}</div>

                @if($message->attachment_path)
                    <div class="attach-box">
                        @if(str_starts_with($message->attachment_mime ?? '', 'image/'))
                            <a href="{{ asset('storage/'.$message->attachment_path) }}" target="_blank">
                                <img src="{{ asset('storage/'.$message->attachment_path) }}" class="attach-img">
                            </a>
                        @endif

                        <a href="{{ asset('storage/'.$message->attachment_path) }}" target="_blank" class="attach-link">
                            📎 {{ $message->attachment_name ?? 'Lihat attachment' }}
                        </a>
                    </div>
                @endif
            </div>
        @endforeach
    </section>

    @if($ticket->status !== 'closed')
        <section class="ticket-card reply-box">
            <div class="ticket-card-title">Balas Tiket</div>

            <form method="POST" action="{{ route('client.tickets.reply', $ticket) }}" enctype="multipart/form-data">
                @csrf

                <label class="reply-label">Balasan</label>
                <textarea name="message" rows="5" placeholder="Tulis balasan..." required></textarea>

                <label class="reply-label">Upload Screenshot / Bukti Tambahan</label>
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf">

                <button type="submit" class="reply-btn">Kirim Balasan</button>
            </form>
        </section>
    @else
        <section class="closed-box">
            Tiket ini sudah ditutup. Klik <b>Reopen Tiket</b> kalau masih butuh bantuan lanjutan.
        </section>
    @endif

</div>
@endsection
