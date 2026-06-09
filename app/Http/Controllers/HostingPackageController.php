<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\HostingPackage;
use Illuminate\Http\Request;

class HostingPackageController extends Controller
{
    public function index(Request $request)
    {
        $query = HostingPackage::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $packages = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('hosting-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('hosting-packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'price'              => 'required|numeric|min:0',
            'setup_fee'          => 'nullable|numeric|min:0',
            'duration_days'      => 'required|integer|min:1',
            'disk_space_mb'      => 'required|integer|min:1',
            'bandwidth_mb'       => 'nullable|integer|min:0',
            'max_email_accounts' => 'required|integer|min:0',
            'max_databases'      => 'required|integer|min:0',
            'max_addon_domains'  => 'nullable|integer|min:0',
            'max_parked_domains' => 'nullable|integer|min:0',
            'max_subdomains'     => 'nullable|integer|min:0',
            'cpanel_package'     => 'nullable|string|max:255',
            'description'        => 'nullable|string|max:2000',
            'is_active'          => 'boolean',
        ]);

        $validated['setup_fee']          = $validated['setup_fee'] ?? 0;
        $validated['max_addon_domains']  = $validated['max_addon_domains'] ?? 0;
        $validated['max_parked_domains'] = $validated['max_parked_domains'] ?? 0;
        $validated['max_subdomains']     = $validated['max_subdomains'] ?? 5;
        $validated['is_active']          = $request->boolean('is_active');

        $package = HostingPackage::create($validated);
        ActivityLog::log('create_hosting_package', "Created hosting package: {$package->name}");

        return redirect()->route('hosting-packages.index')
            ->with('success', 'Paket hosting berhasil dibuat.');
    }

    public function edit(HostingPackage $hostingPackage)
    {
        return view('hosting-packages.edit', ['package' => $hostingPackage]);
    }

    public function update(Request $request, HostingPackage $hostingPackage)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'price'              => 'required|numeric|min:0',
            'setup_fee'          => 'nullable|numeric|min:0',
            'duration_days'      => 'required|integer|min:1',
            'disk_space_mb'      => 'required|integer|min:1',
            'bandwidth_mb'       => 'nullable|integer|min:0',
            'max_email_accounts' => 'required|integer|min:0',
            'max_databases'      => 'required|integer|min:0',
            'max_addon_domains'  => 'nullable|integer|min:0',
            'max_parked_domains' => 'nullable|integer|min:0',
            'max_subdomains'     => 'nullable|integer|min:0',
            'cpanel_package'     => 'nullable|string|max:255',
            'description'        => 'nullable|string|max:2000',
            'is_active'          => 'boolean',
        ]);

        $validated['setup_fee']          = $validated['setup_fee'] ?? 0;
        $validated['max_addon_domains']  = $validated['max_addon_domains'] ?? 0;
        $validated['max_parked_domains'] = $validated['max_parked_domains'] ?? 0;
        $validated['max_subdomains']     = $validated['max_subdomains'] ?? 5;
        $validated['is_active']          = $request->boolean('is_active');

        $hostingPackage->update($validated);
        ActivityLog::log('update_hosting_package', "Updated hosting package: {$hostingPackage->name}");

        return redirect()->route('hosting-packages.index')
            ->with('success', 'Paket hosting berhasil diperbarui.');
    }

    public function destroy(HostingPackage $hostingPackage)
    {
        if ($hostingPackage->hostingAccounts()->exists()) {
            return back()->with('error', 'Tidak bisa hapus paket yang masih punya akun hosting aktif.');
        }

        $name = $hostingPackage->name;
        $hostingPackage->delete();
        ActivityLog::log('delete_hosting_package', "Deleted hosting package: {$name}");

        return redirect()->route('hosting-packages.index')
            ->with('success', 'Paket hosting berhasil dihapus.');
    }
}
