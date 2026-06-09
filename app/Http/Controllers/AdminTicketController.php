<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminTicketController extends Controller
{
    private function ensureAdmin(): void
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $role = strtolower((string) ($user->role ?? ''));
        $type = strtolower((string) ($user->type ?? ''));

        if (in_array($role, ['client', 'customer', 'user']) || in_array($type, ['client', 'customer', 'user'])) {
            abort(403);
        }
    }

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


    private function resolveServiceOwner(object $row): array
    {
        $userId = null;
        $customerId = null;

        if (isset($row->user_id) && $row->user_id) {
            $userId = (int) $row->user_id;
        }

        if (isset($row->customer_id) && $row->customer_id) {
            $customerId = (int) $row->customer_id;
        }

        if (!$userId && $customerId && Schema::hasTable('customers')) {
            if (Schema::hasColumn('customers', 'user_id')) {
                $mappedUserId = DB::table('customers')
                    ->where('id', $customerId)
                    ->value('user_id');

                if ($mappedUserId) {
                    $userId = (int) $mappedUserId;
                }
            }

            if (!$userId && Schema::hasColumn('customers', 'email')) {
                $email = DB::table('customers')
                    ->where('id', $customerId)
                    ->value('email');

                if ($email && Schema::hasTable('users')) {
                    $mappedUserId = DB::table('users')
                        ->where('email', $email)
                        ->value('id');

                    if ($mappedUserId) {
                        $userId = (int) $mappedUserId;
                    }
                }
            }
        }

        return [
            'user_id' => $userId,
            'customer_id' => $customerId,
        ];
    }

    private function allServiceOptions(): array
    {
        $items = [];

        if (Schema::hasTable('vpn_users')) {
            foreach (DB::table('vpn_users')->orderByDesc('id')->get() as $row) {
                $name = $row->username ?? ('VPN-'.$row->id);
                $ip = $row->assigned_ip ?? $row->ip_address ?? '';
                $owner = $this->resolveServiceOwner($row);

                $items[] = [
                    'value' => 'vpn|'.$row->id.'|VPN - '.$name.($ip ? ' / '.$ip : ''),
                    'label' => 'VPN - '.$name.($ip ? ' / '.$ip : ''),
                    'user_id' => $owner['user_id'],
                    'customer_id' => $owner['customer_id'],
                ];
            }
        }

        if (Schema::hasTable('hosting_accounts')) {
            foreach (DB::table('hosting_accounts')->orderByDesc('id')->get() as $row) {
                $domain = $row->domain ?? $row->domain_name ?? $row->hostname ?? ('Hosting-'.$row->id);
                $owner = $this->resolveServiceOwner($row);

                $items[] = [
                    'value' => 'hosting|'.$row->id.'|Hosting - '.$domain,
                    'label' => 'Hosting - '.$domain,
                    'user_id' => $owner['user_id'],
                    'customer_id' => $owner['customer_id'],
                ];
            }
        }

        if (Schema::hasTable('vps_services')) {
            foreach (DB::table('vps_services')->orderByDesc('id')->get() as $row) {
                $name = $row->name ?? $row->hostname ?? $row->server_name ?? ('VPS-'.$row->id);
                $ip = $row->ip_address ?? $row->main_ip ?? $row->public_ip ?? '';
                $owner = $this->resolveServiceOwner($row);

                $items[] = [
                    'value' => 'vps|'.$row->id.'|VPS - '.$name.($ip ? ' / '.$ip : ''),
                    'label' => 'VPS - '.$name.($ip ? ' / '.$ip : ''),
                    'user_id' => $owner['user_id'],
                    'customer_id' => $owner['customer_id'],
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

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $query = SupportTicket::with('user')->latest('last_reply_at')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $this->ensureAdmin();

        $clients = User::query()
            ->orderBy('name')
            ->get();

        if (Schema::hasTable('customers')) {
            foreach ($clients as $client) {
                $customerQuery = DB::table('customers');

                $customerQuery->where(function ($q) use ($client) {
                    if (Schema::hasColumn('customers', 'user_id')) {
                        $q->orWhere('user_id', $client->id);
                    }

                    if (Schema::hasColumn('customers', 'email') && $client->email) {
                        $q->orWhere('email', $client->email);
                    }
                });

                $customer = $customerQuery->first();

                $client->ticket_customer_id = $customer->id ?? null;
            }
        }

        $serviceOptions = $this->allServiceOptions();

        return view('tickets.create', compact('clients', 'serviceOptions'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string'],
            'related_service' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $ticket = SupportTicket::create(array_merge([
            'ticket_number' => 'TKT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'user_id' => $data['user_id'],
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'answered',
            'last_reply_at' => now(),
        ], $this->parseService($data['related_service'] ?? null)));

        SupportTicketMessage::create(array_merge([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $data['message'],
        ], $this->uploadAttachment($request)));

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('success', 'Tiket untuk client berhasil dibuat.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->ensureAdmin();

        $ticket->load(['user', 'messages.user']);

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'message' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        SupportTicketMessage::create(array_merge([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $data['message'],
        ], $this->uploadAttachment($request)));

        $ticket->update([
            'status' => 'answered',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Balasan admin berhasil dikirim.');
    }

    public function close(SupportTicket $ticket)
    {
        $this->ensureAdmin();

        $ticket->update([
            'status' => 'closed',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Tiket berhasil ditutup.');
    }

    public function reopen(SupportTicket $ticket)
    {
        $this->ensureAdmin();

        $ticket->update([
            'status' => 'open',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Tiket dibuka kembali.');
    }
}
