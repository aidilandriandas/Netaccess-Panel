@php $u = $user ?? null; @endphp

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama *</label>
    <input type="text" name="name" value="{{ old('name', $u?->name) }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
    <input type="email" name="email" value="{{ old('email', $u?->email) }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div x-data="{ role: '{{ old('role', $u?->role ?? 'admin') }}' }">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role *</label>
    <select name="role" required x-model="role" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="admin" {{ old('role', $u?->role) === 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="owner" {{ old('role', $u?->role) === 'owner' ? 'selected' : '' }}>Owner</option>
        <option value="client" {{ old('role', $u?->role) === 'client' ? 'selected' : '' }}>Client</option>
    </select>
    @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

    <div x-show="role === 'client'" x-cloak class="mt-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link ke Customer *</label>
        <select name="customer_id" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">-- Pilih Customer --</option>
            @foreach($customers ?? [] as $c)
            <option value="{{ $c->id }}" {{ old('customer_id', $u?->customer_id) == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->email }})</option>
            @endforeach
        </select>
        <p class="text-xs text-gray-400 mt-1">User client akan melihat layanan VPN/Hosting milik customer ini.</p>
        @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
