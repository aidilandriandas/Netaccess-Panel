@extends('layouts.app')
@section('title', 'Detail Ticket')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;

    $ticketId = $ticket->id ?? request()->route('ticket');

    $ticketRow = null;
    if (is_object($ticket ?? null)) {
        $ticketRow = $ticket;
    } elseif (Schema::hasTable('support_tickets')) {
        $ticketRow = DB::table('support_tickets')->where('id', $ticketId)->first();
    }

    $messages = collect();
    if (isset($ticket) && is_object($ticket) && method_exists($ticket, 'messages')) {
        try {
            $messages = $ticket->messages()->orderBy('id')->get();
        } catch (\Throwable $e) {
            $messages = collect();
        }
    }

    if ($messages->isEmpty() && Schema::hasTable('support_ticket_messages')) {
        $messages = DB::table('support_ticket_messages')
            ->where('support_ticket_id', $ticketId)
            ->orderBy('id')
            ->get();
    }

    $url = function ($name, $param = null) {
        try {
            if (!Route::has($name)) return '#';
            return $param ? route($name, $param) : route($name);
        } catch (\Throwable $e) {
            return '#';
        }
    };

    $date = function ($value, $format = 'd M Y H:i') {
        if (!$value) return '-';
        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $userInfo = function ($userId = null) {
        try {
            if ($userId && Schema::hasTable('users')) {
                $user = DB::table('users')->where('id', $userId)->first();

                if ($user) {
                    return [
                        'name' => $user->name ?? $user->email ?? 'User-'.$userId,
                        'email' => $user->email ?? '-',
                    ];
                }
            }
        } catch (\Throwable $e) {}

        return [
            'name' => '-',
            'email' => '-',
        ];
    };

    $client = $userInfo($ticketRow->user_id ?? null);

    $status = strtolower($ticketRow->status ?? 'open');
    $priority = strtolower($ticketRow->priority ?? 'normal');
    $category = $ticketRow->category ?? '-';

    $statusClass = match ($status) {
        'open' => 'td-badge open',
        'answered' => 'td-badge answered',
        'closed' => 'td-badge closed',
        default => 'td-badge pending',
    };

    $priorityClass = match ($priority) {
        'urgent', 'high' => 'td-priority high',
        'medium' => 'td-priority medium',
        'low' => 'td-priority low',
        default => 'td-priority normal',
    };

    $messageAuthor = function ($message) use ($userInfo) {
        $senderType = strtolower($message->sender_type ?? '');

        if ($senderType === 'admin' || $senderType === 'staff') {
            return [
                'name' => 'Admin Support',
                'role' => 'Admin',
                'class' => 'admin',
            ];
        }

        $u = $userInfo($message->user_id ?? null);

        return [
            'name' => $u['name'],
            'role' => 'Client',
            'class' => 'client',
        ];
    };

    $attachmentUrl = function ($message) {
        $path = $message->attachment_path ?? null;
        if (!$path) return null;

        try {
            return Storage::url($path);
        } catch (\Throwable $e) {
            return asset('storage/' . ltrim($path, '/'));
        }
    };

    $isImage = function ($message) {
        $mime = strtolower($message->attachment_mime ?? '');
        $name = strtolower($message->attachment_name ?? '');

        return str_starts_with($mime, 'image/')
            || str_ends_with($name, '.jpg')
            || str_ends_with($name, '.jpeg')
            || str_ends_with($name, '.png')
            || str_ends_with($name, '.webp');
    };
@endphp

<style>
.td-page{display:flex;flex-direction:column;gap:18px}
.td-hero{position:relative;overflow:hidden;border-radius:28px;padding:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 22px 60px rgba(0,0,0,.22)}
.td-hero:before{content:"";position:absolute;right:-80px;top:-90px;width:260px;height:260px;border-radius:999px;background:conic-gradient(from 180deg,rgba(124,58,237,.30),rgba(37,99,235,.24),rgba(14,165,233,.14));animation:tdSpin 20s linear infinite;opacity:.55}
@keyframes tdSpin{to{transform:rotate(360deg)}}
.td-hero-inner{position:relative;z-index:2;display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap}
.td-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:11px}
.td-title{margin:0;color:#fff;font-size:31px;font-weight:1000;letter-spacing:-.045em}
.td-sub{margin-top:8px;color:#cbd5e1;font-size:14px;max-width:760px}
.td-actions{display:flex;gap:10px;flex-wrap:wrap}
.td-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:15px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;transition:.2s ease;cursor:pointer}
.td-btn:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.34)}
.td-btn.primary{color:white;background:linear-gradient(135deg,#7c3aed,#2563eb);border-color:transparent;box-shadow:0 14px 30px rgba(124,58,237,.25)}
.td-btn.red{color:#fecaca;background:rgba(239,68,68,.14)}
.td-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:16px}
.td-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.td-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:17px 19px;border-bottom:1px solid rgba(148,163,184,.10)}
.td-card-title{display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.td-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.td-badge{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.td-badge.open{color:#fde68a;background:rgba(245,158,11,.13)}
.td-badge.answered{color:#86efac;background:rgba(34,197,94,.14)}
.td-badge.closed{color:#cbd5e1;background:rgba(100,116,139,.16)}
.td-badge.pending{color:#bfdbfe;background:rgba(37,99,235,.12)}
.td-priority{display:inline-flex;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:1000;text-transform:uppercase;border:1px solid rgba(148,163,184,.14)}
.td-priority.high{color:#fca5a5;background:rgba(239,68,68,.13)}
.td-priority.medium{color:#fde68a;background:rgba(245,158,11,.13)}
.td-priority.low{color:#93c5fd;background:rgba(37,99,235,.13)}
.td-priority.normal{color:#c4b5fd;background:rgba(124,58,237,.13)}
.td-chat{padding:18px;display:flex;flex-direction:column;gap:14px}
.td-message{display:flex;gap:12px;align-items:flex-start}
.td-avatar{width:42px;height:42px;border-radius:16px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;color:white;font-weight:1000;background:linear-gradient(135deg,rgba(37,99,235,.35),rgba(124,58,237,.25))}
.td-message.admin .td-avatar{background:linear-gradient(135deg,rgba(124,58,237,.45),rgba(37,99,235,.25))}
.td-bubble{width:100%;border-radius:20px;padding:15px 16px;border:1px solid rgba(148,163,184,.13);background:rgba(2,6,23,.26)}
.td-message.admin .td-bubble{background:rgba(37,99,235,.10);border-color:rgba(96,165,250,.20)}
.td-msg-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px}
.td-msg-name{color:white;font-weight:1000;font-size:13px}
.td-msg-role{color:#94a3b8;font-size:11px;font-weight:900}
.td-msg-date{color:#64748b;font-size:11px;font-weight:900}
.td-msg-body{color:#dbeafe;font-size:14px;line-height:1.65;white-space:pre-wrap}
.td-attachment{margin-top:12px;border-radius:16px;border:1px solid rgba(148,163,184,.13);background:rgba(15,23,42,.52);padding:11px}
.td-attachment img{max-width:360px;width:100%;border-radius:13px;display:block;margin-bottom:10px}
.td-attach-link{color:#93c5fd;text-decoration:none;font-weight:950;font-size:12px}
.td-reply{padding:18px;border-top:1px solid rgba(148,163,184,.10)}
.td-textarea{width:100%;min-height:145px;border-radius:18px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.70);color:white;padding:14px;resize:vertical;outline:none}
.td-file{margin-top:12px;width:100%}
.td-submit-row{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:14px}
.td-side{padding:17px}
.td-info{padding:14px;border-radius:18px;border:1px solid rgba(148,163,184,.12);background:rgba(2,6,23,.24);margin-bottom:11px}
.td-label{color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em}
.td-value{color:white;font-size:14px;font-weight:950;margin-top:6px;word-break:break-word}
.td-link{color:#60a5fa;text-decoration:none;font-weight:950}
.td-empty{padding:34px;text-align:center;color:#94a3b8}
.td-form-inline{display:inline-flex;margin:0}
.td-closed{padding:18px;color:#cbd5e1;background:rgba(100,116,139,.12);border-top:1px solid rgba(148,163,184,.10)}
@media(max-width:1100px){.td-grid{grid-template-columns:1fr}}
@media(max-width:680px){.td-title{font-size:27px}.td-msg-head{align-items:flex-start;flex-direction:column}}
</style>

<div class="td-page">

    <section class="td-hero">
        <div class="td-hero-inner">
            <div>
                <div class="td-pill">🎫 {{ $ticketRow->ticket_number ?? 'Ticket-'.$ticketId }}</div>
                <h1 class="td-title">{{ $ticketRow->subject ?? 'Detail Ticket' }}</h1>
                <div class="td-sub">
                    Client: <strong style="color:white;">{{ $client['name'] }}</strong>
                    · Category: {{ ucfirst($category) }}
                    · Created: {{ $date($ticketRow->created_at ?? null) }}
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;">
                    <span class="{{ $statusClass }}">{{ $status }}</span>
                    <span class="{{ $priorityClass }}">{{ $priority }}</span>
                </div>
            </div>

            <div class="td-actions">
                <a href="{{ $url('admin.tickets.index') }}" class="td-btn">← Kembali</a>

                @if(($ticketRow->user_id ?? null) && Route::has('admin.client-history.show'))
                    <a href="{{ route('admin.client-history.show', $ticketRow->user_id) }}" class="td-btn primary">Client 360</a>
                @endif

                @if($status !== 'closed' && Route::has('admin.tickets.close'))
                    <form action="{{ route('admin.tickets.close', $ticketId) }}" method="POST" class="td-form-inline">
                        @csrf
                        <button type="submit" class="td-btn red" onclick="return confirm('Close ticket ini?')">Close</button>
                    </form>
                @elseif($status === 'closed' && Route::has('admin.tickets.reopen'))
                    <form action="{{ route('admin.tickets.reopen', $ticketId) }}" method="POST" class="td-form-inline">
                        @csrf
                        <button type="submit" class="td-btn primary" onclick="return confirm('Reopen ticket ini?')">Reopen</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="td-grid">

        <div class="td-card">
            <div class="td-card-head">
                <div class="td-card-title">
                    <span class="td-dot"></span>
                    Percakapan
                </div>
                <div style="color:#94a3b8;font-size:12px;font-weight:900;">
                    {{ $messages->count() }} pesan
                </div>
            </div>

            <div class="td-chat">
                @forelse($messages as $message)
                    @php
                        $author = $messageAuthor($message);
                        $attach = $attachmentUrl($message);
                    @endphp

                    <div class="td-message {{ $author['class'] }}">
                        <div class="td-avatar">{{ strtoupper(substr($author['name'], 0, 1)) }}</div>

                        <div class="td-bubble">
                            <div class="td-msg-head">
                                <div>
                                    <div class="td-msg-name">{{ $author['name'] }}</div>
                                    <div class="td-msg-role">{{ $author['role'] }}</div>
                                </div>
                                <div class="td-msg-date">{{ $date($message->created_at ?? null) }}</div>
                            </div>

                            <div class="td-msg-body">{{ $message->message ?? '-' }}</div>

                            @if($attach)
                                <div class="td-attachment">
                                    @if($isImage($message))
                                        <img src="{{ $attach }}" alt="{{ $message->attachment_name ?? 'attachment' }}">
                                    @endif

                                    <a href="{{ $attach }}" target="_blank" class="td-attach-link">
                                        📎 {{ $message->attachment_name ?? 'Download attachment' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="td-empty">Belum ada pesan di ticket ini.</div>
                @endforelse
            </div>

            @if($status !== 'closed')
                <form action="{{ $url('admin.tickets.reply', $ticketId) }}" method="POST" enctype="multipart/form-data" class="td-reply">
                    @csrf

                    <textarea name="message" class="td-textarea" placeholder="Tulis balasan untuk client..." required></textarea>

                    <input type="file" name="attachment" class="td-file" accept=".jpg,.jpeg,.png,.webp,.pdf">

                    <div class="td-submit-row">
                        <button type="submit" class="td-btn primary">Kirim Balasan</button>
                    </div>
                </form>
            @else
                <div class="td-closed">
                    Ticket ini sudah closed. Reopen dulu kalau ingin membalas.
                </div>
            @endif
        </div>

        <aside class="td-card">
            <div class="td-card-head">
                <div class="td-card-title">
                    <span class="td-dot" style="background:#8b5cf6;"></span>
                    Detail Ticket
                </div>
            </div>

            <div class="td-side">
                <div class="td-info">
                    <div class="td-label">Ticket Number</div>
                    <div class="td-value">{{ $ticketRow->ticket_number ?? 'Ticket-'.$ticketId }}</div>
                </div>

                <div class="td-info">
                    <div class="td-label">Client</div>
                    <div class="td-value">{{ $client['name'] }}</div>
                    <div style="color:#94a3b8;font-size:12px;margin-top:4px;">{{ $client['email'] }}</div>
                </div>

                <div class="td-info">
                    <div class="td-label">Related Service</div>
                    <div class="td-value">{{ $ticketRow->service_label ?? $ticketRow->service_type ?? '-' }}</div>
                </div>

                <div class="td-info">
                    <div class="td-label">Category</div>
                    <div class="td-value">{{ ucfirst($category) }}</div>
                </div>

                <div class="td-info">
                    <div class="td-label">Status</div>
                    <div class="td-value"><span class="{{ $statusClass }}">{{ $status }}</span></div>
                </div>

                <div class="td-info">
                    <div class="td-label">Priority</div>
                    <div class="td-value"><span class="{{ $priorityClass }}">{{ $priority }}</span></div>
                </div>

                <div class="td-info">
                    <div class="td-label">Last Reply</div>
                    <div class="td-value">{{ $date($ticketRow->last_reply_at ?? $ticketRow->updated_at ?? null) }}</div>
                </div>

                @if(($ticketRow->user_id ?? null) && Route::has('admin.client-history.show'))
                    <a href="{{ route('admin.client-history.show', $ticketRow->user_id) }}" class="td-btn primary" style="width:100%;margin-top:8px;">
                        Buka Client 360
                    </a>
                @endif
            </div>
        </aside>

    </section>

</div>
@endsection
