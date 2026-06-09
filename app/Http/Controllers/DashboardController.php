<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\HostingAccount;
use App\Models\Invoice;
use App\Models\VpnUser;
use App\Models\ActivityLog;
use App\Services\ServerMonitorService;

class DashboardController extends Controller
{
    public function index(ServerMonitorService $monitor)
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('status', 'active')->count(),
            'expired_customers' => Customer::where('status', 'expired')->count(),
            'suspended_customers' => Customer::where('status', 'suspended')->count(),
            'total_vpn_users' => VpnUser::count(),
            'active_vpn_users' => VpnUser::where('status', 'active')->count(),
            'unpaid_invoices' => Invoice::where('status', 'unpaid')->count(),
            'revenue_this_month' => Invoice::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total'),
            'total_hosting_accounts' => HostingAccount::count(),
            'active_hosting_accounts' => HostingAccount::where('status', 'active')->count(),
        ];

        $serverStatus = $monitor->getFullReport();

        $expiringCustomers = VpnUser::with('customer', 'package')
            ->where('status', 'active')
            ->whereBetween('expired_at', [now(), now()->addDays(7)])
            ->orderBy('expired_at')
            ->limit(10)
            ->get();

        $unpaidInvoices = Invoice::with('customer', 'package')
            ->whereIn('status', ['unpaid', 'overdue'])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $recentVpnUsers = VpnUser::with('customer', 'package')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentLogs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'stats', 'serverStatus', 'expiringCustomers',
            'unpaidInvoices', 'recentVpnUsers', 'recentLogs'
        ));
    }
}
