<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with('customer')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin-users.index', compact('users'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('admin-users.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:owner,admin,client',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        if ($validated['role'] === 'client' && empty($validated['customer_id'])) {
            return back()->withErrors(['customer_id' => 'Customer harus dipilih untuk role Client.'])->withInput();
        }

        if ($validated['role'] !== 'client') {
            $validated['customer_id'] = null;
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        ActivityLog::log('create_user', "Created user: {$user->email} (role: {$user->role})");

        return redirect()->route('admin-users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $adminUser)
    {
        $customers = Customer::orderBy('name')->get();
        return view('admin-users.edit', ['user' => $adminUser, 'customers' => $customers]);
    }

    public function update(Request $request, User $adminUser)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $adminUser->id,
            'role' => 'required|in:owner,admin,client',
            'customer_id' => 'nullable|exists:customers,id',
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($validated['role'] === 'client' && empty($validated['customer_id'])) {
            return back()->withErrors(['customer_id' => 'Customer harus dipilih untuk role Client.'])->withInput();
        }

        if ($validated['role'] !== 'client') {
            $validated['customer_id'] = null;
        }

        $adminUser->name = $validated['name'];
        $adminUser->email = $validated['email'];
        $adminUser->role = $validated['role'];
        $adminUser->customer_id = $validated['customer_id'];

        if (!empty($validated['password'])) {
            $adminUser->password = Hash::make($validated['password']);
        }

        $adminUser->save();
        ActivityLog::log('update_user', "Updated user: {$adminUser->email} (role: {$adminUser->role})");

        return redirect()->route('admin-users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $adminUser)
    {
        if ($adminUser->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $email = $adminUser->email;
        $adminUser->delete();
        ActivityLog::log('delete_user', "Deleted user: {$email}");

        return redirect()->route('admin-users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
