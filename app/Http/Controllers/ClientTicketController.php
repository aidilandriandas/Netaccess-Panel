<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClientTicketController extends Controller
{
    private function uploadAttachment(Request $request): array
    {
        if (!$request->hasFile('attachment')) {
            return [
                'attachment_path' => null,
                'attachment_name' => null,
                'attachment_mime' => null,
            ];
        }

        $file = $request->file('attachment');

        return [
            'attachment_path' => $file->store('ticket-attachments', 'public'),
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_mime' => $file->getMimeType(),
        ];
    }

    private function serviceOptions(?int $userId = null): array
    {
        $userId = $userId ?: Auth::id();
        $items = [];

        if (Schema::hasTable('vpn_users')) {
            $query = DB::table('vpn_users');

            if (Schema::hasColumn('vpn_users', 'user_id')) {
                $query->where('user_id', $userId);
            } elseif (Schema::hasColumn('vpn_users', 'customer_id')) {
                $query->where('customer_id', $userId);
            }

            foreach ($query->orderByDesc('id')->get() as $row) {
                $name = $row->username ?? ('VPN-'.$row->id);
                $ip = $row->assigned_ip ?? $row->ip_address ?? '';
                $items[] = [
                    'value' => 'vpn|'.$row->id.'|VPN - '.$name.($ip ? ' / '.$ip : ''),
                    'label' => 'VPN - '.$name.($ip ? ' / '.$ip : ''),
                ];
            }
        }

        if (Schema::hasTable('hosting_accounts')) {
            $query = DB::table('hosting_accounts');

            if (Schema::hasColumn('hosting_accounts', 'user_id')) {
                $query->where('user_id', $userId);
            } elseif (Schema::hasColumn('hosting_accounts', 'customer_id')) {
                $query->where('customer_id', $userId);
            }

            foreach ($query->orderByDesc('id')->get() as $row) {
                $domain = $row->domain ?? $row->domain_name ?? $row->hostname ?? ('Hosting-'.$row->id);
                $items[] = [
                    'value' => 'hosting|'.$row->id.'|Hosting - '.$domain,
                    'label' => 'Hosting - '.$domain,
                ];
            }
        }

        if (Schema::hasTable('vps_services')) {
            $query = DB::table('vps_services');

            if (Schema::hasColumn('vps_services', 'user_id')) {
                $query->where('user_id', $userId);
            } elseif (Schema::hasColumn('vps_services', 'customer_id')) {
                $query->where('customer_id', $userId);
            }

            foreach ($query->orderByDesc('id')->get() as $row) {
                $name = $row->name ?? $row->hostname ?? $row->server_name ?? ('VPS-'.$row->id);
                $ip = $row->ip_address ?? $row->main_ip ?? $row->public_ip ?? '';
                $items[] = [
                    'value' => 'vps|'.$row->id.'|VPS - '.$name.($ip ? ' / '.$ip : ''),
                    'label' => 'VPS - '.$name.($ip ? ' / '.$ip : ''),
                ];
            }
        }

        return $items;
    }

    private function parseService(?string $value): array
    {
        if (!$value) {
            return [
                'service_type' => null,
                'service_id' => null,
                'service_label' => null,
            ];
        }

        $parts = explode('|', $value, 3);

        return [
            'service_type' => $parts[0] ?? null,
            'service_id' => isset($parts[1]) ? (int) $parts[1] : null,
            'service_label' => $parts[2] ?? null,
        ];
    }

    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->latest('last_reply_at')
            ->latest()
            ->paginate(10);

        return view('client.tickets.index', compact('tickets'));
    }

    public function create()
    {
        $serviceOptions = $this->serviceOptions();

        return view('client.tickets.create', compact('serviceOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'string', 'max:50'],
            'related_service' => ['nullable', 'string', 'max:500'],
            'message' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $ticket = SupportTicket::create(array_merge([
            'ticket_number' => 'TKT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'user_id' => Auth::id(),
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
            'last_reply_at' => now(),
        ], $this->parseService($data['related_service'] ?? null)));

        SupportTicketMessage::create(array_merge([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'sender_type' => 'client',
            'message' => $data['message'],
        ], $this->uploadAttachment($request)));

        return redirect()
            ->route('client.tickets.show', $ticket)
            ->with('success', 'Tiket berhasil dibuat.');
    }

    public function show(SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $ticket->load(['messages.user']);

        return view('client.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $data = $request->validate([
            'message' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        SupportTicketMessage::create(array_merge([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'sender_type' => 'client',
            'message' => $data['message'],
        ], $this->uploadAttachment($request)));

        $ticket->update([
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }
    public function close(SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $ticket->update([
            'status' => 'closed',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Tiket berhasil ditutup.');
    }

    public function reopen(SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $ticket->update([
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Tiket berhasil dibuka kembali.');
    }

}
