<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminClientHistoryController extends Controller
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

    private function customerIds(User $user): array
    {
        $ids = [];

        if (!Schema::hasTable('customers')) {
            return $ids;
        }

        $query = DB::table('customers');

        $query->where(function ($q) use ($user) {
            if (Schema::hasColumn('customers', 'user_id')) {
                $q->orWhere('user_id', $user->id);
            }

            if (Schema::hasColumn('customers', 'email') && $user->email) {
                $q->orWhere('email', $user->email);
            }
        });

        return $query->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
    }

    private function byOwner(string $table, User $user, array $customerIds)
    {
        $query = DB::table($table);

        $query->where(function ($q) use ($table, $user, $customerIds) {
            if (Schema::hasColumn($table, 'user_id')) {
                $q->orWhere('user_id', $user->id);
            }

            if (Schema::hasColumn($table, 'customer_id') && count($customerIds)) {
                $q->orWhereIn('customer_id', $customerIds);
            }
        });

        return $query;
    }

    public function show(User $user)
    {
        $this->ensureAdmin();

        $customerIds = $this->customerIds($user);

        $tickets = collect();
        $invoices = collect();
        $vpnUsers = collect();
        $hostings = collect();
        $vpsServices = collect();
        $orders = collect();
        $emails = collect();

        if (Schema::hasTable('support_tickets')) {
            $tickets = DB::table('support_tickets')
                ->where('user_id', $user->id)
                ->orderByDesc('last_reply_at')
                ->orderByDesc('id')
                ->get();
        }

        if (Schema::hasTable('invoices')) {
            $invoices = $this->byOwner('invoices', $user, $customerIds)
                ->orderByDesc('id')
                ->get();
        }

        if (Schema::hasTable('vpn_users')) {
            $vpnUsers = $this->byOwner('vpn_users', $user, $customerIds)
                ->orderByDesc('id')
                ->get();
        }

        if (Schema::hasTable('hosting_accounts')) {
            $hostings = $this->byOwner('hosting_accounts', $user, $customerIds)
                ->orderByDesc('id')
                ->get();
        }

        if (Schema::hasTable('vps_services')) {
            $vpsServices = $this->byOwner('vps_services', $user, $customerIds)
                ->orderByDesc('id')
                ->get();
        }

        if (Schema::hasTable('orders')) {
            $orders = $this->byOwner('orders', $user, $customerIds)
                ->orderByDesc('id')
                ->get();
        }

        if (Schema::hasTable('email_logs')) {
            $emails = $this->byOwner('email_logs', $user, $customerIds)
                ->orderByDesc('id')
                ->limit(50)
                ->get();
        }

        return view('admin.client-history.show', compact(
            'user',
            'customerIds',
            'tickets',
            'invoices',
            'vpnUsers',
            'hostings',
            'vpsServices',
            'orders',
            'emails'
        ));
    }
    public function showCustomer(Customer $customer)
    {
        $this->ensureAdmin();

        $userId = null;

        if (Schema::hasColumn('customers', 'user_id') && !empty($customer->user_id)) {
            $userId = $customer->user_id;
        }

        if (!$userId && !empty($customer->email)) {
            $userId = User::where('email', $customer->email)->value('id');
        }

        if ($userId) {
            return redirect()->route('admin.client-history.show', $userId);
        }

        return back()->with('error', 'User login untuk customer ini belum ditemukan.');
    }

}
