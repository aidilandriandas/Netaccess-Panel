<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\VpsPackage;
use Illuminate\Http\Request;

class VpsPackageController extends Controller
{
    public function index(Request $request)
    {
        $packages = VpsPackage::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('vps-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('vps-packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'cpu_cores' => 'required|integer|min:1',
            'ram_mb' => 'required|integer|min:128',
            'storage_gb' => 'required|integer|min:1',
            'bandwidth_gb' => 'nullable|integer|min:1',
            'ipv4_count' => 'required|integer|min:1',
            'os_options' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['os_options'])) {
            $validated['os_options'] = array_map('trim', explode("\n", $validated['os_options']));
        }

        $validated['is_active'] = $request->boolean('is_active');

        $package = VpsPackage::create($validated);
        ActivityLog::log('create_vps_package', "Created VPS package: {$package->name}");

        return redirect()->route('vps-packages.index')->with('success', 'VPS Package berhasil ditambahkan.');
    }

    public function edit(VpsPackage $vpsPackage)
    {
        return view('vps-packages.edit', ['package' => $vpsPackage]);
    }

    public function update(Request $request, VpsPackage $vpsPackage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'cpu_cores' => 'required|integer|min:1',
            'ram_mb' => 'required|integer|min:128',
            'storage_gb' => 'required|integer|min:1',
            'bandwidth_gb' => 'nullable|integer|min:1',
            'ipv4_count' => 'required|integer|min:1',
            'os_options' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['os_options'])) {
            $validated['os_options'] = array_map('trim', explode("\n", $validated['os_options']));
        } else {
            $validated['os_options'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        $vpsPackage->update($validated);
        ActivityLog::log('update_vps_package', "Updated VPS package: {$vpsPackage->name}");

        return redirect()->route('vps-packages.index')->with('success', 'VPS Package berhasil diperbarui.');
    }

    public function destroy(VpsPackage $vpsPackage)
    {
        if ($vpsPackage->isInUse()) {
            return back()->with('error', 'Package tidak bisa dihapus karena sudah digunakan. Nonaktifkan saja.');
        }

        $name = $vpsPackage->name;
        $vpsPackage->delete();
        ActivityLog::log('delete_vps_package', "Deleted VPS package: {$name}");

        return redirect()->route('vps-packages.index')->with('success', 'VPS Package berhasil dihapus.');
    }
}
