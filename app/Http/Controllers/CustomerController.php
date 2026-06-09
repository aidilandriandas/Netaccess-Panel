<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        $customers = $query->withCount(['vpnUsers', 'invoices'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:personal,kantor,reseller,rtrw_net',
            'status' => 'required|in:active,expired,suspended',
            'notes' => 'nullable|string|max:2000',
        ]);

        $customer = Customer::create($validated);
        ActivityLog::log('create_customer', "Created customer: {$customer->name}");

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        // Redirect detail customer ke halaman Client 360 jika user client ditemukan.
        if (Route::has('admin.client-history.show')) {
            $historyUserId = null;

            if (Schema::hasColumn('customers', 'user_id') && !empty($customer->user_id)) {
                $historyUserId = $customer->user_id;
            }

            if (!$historyUserId && !empty($customer->email)) {
                $historyUserId = User::where('email', $customer->email)->value('id');
            }

            if ($historyUserId) {
                return redirect()->route('admin.client-history.show', $historyUserId);
            }
        }

        $customer->load(['vpnUsers.package', 'invoices.package']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:personal,kantor,reseller,rtrw_net',
            'status' => 'required|in:active,expired,suspended',
            'notes' => 'nullable|string|max:2000',
        ]);

        $customer->update($validated);
        ActivityLog::log('update_customer', "Updated customer: {$customer->name}");

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
 if (
        $customer->vpnUsers()->count() > 0 ||
        $customer->invoices()->count() > 0 ||
        $customer->vpsOrders()->count() > 0
    ) {
        return back()->with('error', 'Customer tidak bisa dihapus karena masih memiliki layanan, invoice, atau order.');
    }

    $name = $customer->name;

    $customer->delete();

    ActivityLog::log('delete_customer', "Deleted customer: {$name}");

    return redirect()->route('customers.index')
        ->with('success', 'Customer berhasil dihapus.');
}
}
