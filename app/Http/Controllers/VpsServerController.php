<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\VpsServer;
use Illuminate\Http\Request;

class VpsServerController extends Controller
{
    public function index(Request $request)
    {
        $servers = VpsServer::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('host', 'like', "%{$s}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('vps-servers.index', compact('servers'));
    }

    public function create()
    {
        return view('vps-servers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider_type' => 'required|in:manual,proxmox,virtualizor,solusvm,cloud_api',
            'host' => 'required|string|max:255',
            'api_url' => 'nullable|url|max:500',
            'api_username' => 'nullable|string|max:255',
            'api_token' => 'nullable|string|max:2000',
            'api_secret' => 'nullable|string|max:2000',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,maintenance,offline',
            'notes' => 'nullable|string|max:2000',
        ]);

        $server = VpsServer::create($validated);
        ActivityLog::log('create_vps_server', "Created VPS server: {$server->name} ({$server->host})");

        return redirect()->route('vps-servers.index')->with('success', 'VPS Server berhasil ditambahkan.');
    }

    public function edit(VpsServer $vpsServer)
    {
        return view('vps-servers.edit', ['server' => $vpsServer]);
    }

    public function update(Request $request, VpsServer $vpsServer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider_type' => 'required|in:manual,proxmox,virtualizor,solusvm,cloud_api',
            'host' => 'required|string|max:255',
            'api_url' => 'nullable|url|max:500',
            'api_username' => 'nullable|string|max:255',
            'api_token' => 'nullable|string|max:2000',
            'api_secret' => 'nullable|string|max:2000',
            'ssh_port' => 'required|integer|min:1|max:65535',
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,maintenance,offline',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (empty($validated['api_token'])) {
            unset($validated['api_token']);
        }
        if (empty($validated['api_secret'])) {
            unset($validated['api_secret']);
        }

        $vpsServer->update($validated);
        ActivityLog::log('update_vps_server', "Updated VPS server: {$vpsServer->name}");

        return redirect()->route('vps-servers.index')->with('success', 'VPS Server berhasil diperbarui.');
    }

    public function destroy(VpsServer $vpsServer)
    {
        if ($vpsServer->vpsServices()->exists()) {
            return back()->with('error', 'Server tidak bisa dihapus karena masih memiliki VPS services.');
        }

        $name = $vpsServer->name;
        $vpsServer->delete();
        ActivityLog::log('delete_vps_server', "Deleted VPS server: {$name}");

        return redirect()->route('vps-servers.index')->with('success', 'VPS Server berhasil dihapus.');
    }
}
