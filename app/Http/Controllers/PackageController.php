<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('service_type', $request->type);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $packages = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'service_type' => strtolower(trim($request->input('service_type', 'vpn'))),
        ]);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'service_type'    => 'required|string|in:vpn,monitoring,hosting,backup,vps',
            'description'     => 'nullable|string|max:2000',
            'price'           => 'required|numeric|min:0',
            'setup_fee'       => 'nullable|numeric|min:0',
            'duration_days'   => 'required|integer|min:1',
            'max_users'       => 'nullable|integer|min:1',
            'bandwidth_limit' => 'nullable|string|max:255',
            'is_active'       => 'nullable|boolean',
        ]);

        $validated['setup_fee'] = $validated['setup_fee'] ?? 0;
        $validated['max_users'] = $validated['max_users'] ?? 1;
        $validated['is_active'] = $request->boolean('is_active');

        $package = Package::create($validated);

        ActivityLog::log('create_package', "Created package: {$package->name} ({$package->service_type})");

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil dibuat.');
    }

    public function show(Package $package)
    {
        return view('packages.show', compact('package'));
    }

    public function edit(Package $package)
    {
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $request->merge([
            'service_type' => strtolower(trim($request->input('service_type', $package->service_type ?? 'vpn'))),
        ]);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'service_type'    => 'required|string|in:vpn,monitoring,hosting,backup,vps',
            'description'     => 'nullable|string|max:2000',
            'price'           => 'required|numeric|min:0',
            'setup_fee'       => 'nullable|numeric|min:0',
            'duration_days'   => 'required|integer|min:1',
            'max_users'       => 'nullable|integer|min:1',
            'bandwidth_limit' => 'nullable|string|max:255',
            'is_active'       => 'nullable|boolean',
        ]);

        $validated['setup_fee'] = $validated['setup_fee'] ?? 0;
        $validated['max_users'] = $validated['max_users'] ?? 1;
        $validated['is_active'] = $request->boolean('is_active');

        $package->update($validated);

        ActivityLog::log('update_package', "Updated package: {$package->name} ({$package->service_type})");

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package)
    {
        $usedByVpnUsers = method_exists($package, 'vpnUsers')
            ? $package->vpnUsers()->count()
            : 0;

        $usedByInvoices = method_exists($package, 'invoices')
            ? $package->invoices()->count()
            : 0;

        if ($usedByVpnUsers > 0 || $usedByInvoices > 0) {
            return back()->with('error', 'Paket tidak bisa dihapus karena masih digunakan oleh layanan atau invoice. Nonaktifkan paket saja.');
        }

        $name = $package->name;

        $package->delete();

        ActivityLog::log('delete_package', "Deleted package: {$name}");

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil dihapus.');
    }
}
