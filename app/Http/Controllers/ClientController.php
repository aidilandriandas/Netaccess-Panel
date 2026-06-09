<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HostingAccount;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\VpsService;
use App\Models\VpnUser;

class ClientController extends Controller
{
    public function dashboard()
    {
        $customer = auth()->user()->customer;

        if (!$customer) {
            return view('client.dashboard', [
                'stats'    => [],
                'customer' => null,
            ]);
        }

        $stats = [
            'active_vpn'     => $customer->vpnUsers()->where('status', 'active')->count(),
            'total_vpn'      => $customer->vpnUsers()->count(),
            'active_hosting' => $customer->hostingAccounts()->where('status', 'active')->count(),
            'total_hosting'  => $customer->hostingAccounts()->count(),
            'active_vps'     => $customer->vpsServices()->where('status', 'active')->count(),
            'total_vps'      => $customer->vpsServices()->count(),
            'unpaid_invoices'=> $customer->invoices()->where('status', 'unpaid')->count(),
            'total_invoices' => $customer->invoices()->count(),
        ];

        $recentVps = $customer->vpsServices()
            ->with('vpsPackage')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentVpn = $customer->vpnUsers()
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentHosting = $customer->hostingAccounts()
            ->with('hostingPackage')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentInvoices = $customer->invoices()
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('client.dashboard', compact('customer', 'stats', 'recentVpn', 'recentHosting', 'recentVps', 'recentInvoices'));
    }

    public function vpnServices()
    {
        $customer = auth()->user()->customer;

        $vpnUsers = $customer
            ? $customer->vpnUsers()->with('package')->orderBy('created_at', 'desc')->paginate(15)
            : collect();

        return view('client.vpn-services', compact('vpnUsers'));
    }

    public function vpnServiceShow(VpnUser $vpnUser)
    {
        $customer = auth()->user()->customer;

        if (!$customer || $vpnUser->customer_id !== $customer->id) {
            abort(403);
        }

        $vpnUser->load('package');

        return view('client.vpn-service-show', compact('vpnUser'));
    }

    public function hostingServices()
    {
        $customer = auth()->user()->customer;

        $hostingAccounts = $customer
            ? $customer->hostingAccounts()->with('hostingPackage')->orderBy('created_at', 'desc')->paginate(15)
            : collect();

        return view('client.hosting-services', compact('hostingAccounts'));
    }

    public function hostingServiceShow(HostingAccount $hostingAccount)
    {
        $customer = auth()->user()->customer;

        if (!$customer || $hostingAccount->customer_id !== $customer->id) {
            abort(403);
        }

        $hostingAccount->load('hostingPackage');

        return view('client.hosting-service-show', compact('hostingAccount'));
    }

    public function invoices()
    {
        $customer = auth()->user()->customer;

        $invoices = $customer
            ? $customer->invoices()->with('package')->orderBy('created_at', 'desc')->paginate(15)
            : collect();

        return view('client.invoices', compact('invoices'));
    }

    public function invoiceShow(Invoice $invoice)
    {
        $customer = auth()->user()->customer;

        if (!$customer || $invoice->customer_id !== $customer->id) {
            abort(403);
        }

        $invoice->load('package', 'paymentProofs');

        return view('client.invoice-show', compact('invoice'));
    }

    public function vpsServices()
    {
        $customer = auth()->user()->customer;

        $vpsServices = $customer
            ? $customer->vpsServices()->with('vpsPackage', 'vpsServer')->orderBy('created_at', 'desc')->paginate(15)
            : collect();

        return view('client.vps-services', compact('vpsServices'));
    }

    public function vpsServiceShow(VpsService $vpsService)
    {
        $customer = auth()->user()->customer;

        if (!$customer || $vpsService->customer_id !== $customer->id) {
            abort(403);
        }

        $vpsService->load('vpsPackage', 'vpsServer');
        $canViewPassword = Setting::get('vps_client_can_view_password', 'false') === 'true';

        ActivityLog::log('client_view_vps', "Client viewed VPS: {$vpsService->hostname}");

        return view('client.vps-service-show', compact('vpsService', 'canViewPassword'));
    }

    public function vpsServiceInvoices(VpsService $vpsService)
    {
        $customer = auth()->user()->customer;

        if (!$customer || $vpsService->customer_id !== $customer->id) {
            abort(403);
        }

        $invoices = $vpsService->vpsOrders()
            ->with('invoice')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('invoice')
            ->filter();

        return view('client.vps-invoices', compact('vpsService', 'invoices'));
    }

    public function vpsGuide()
    {
        return view('client.vps-guide');
    }
}
