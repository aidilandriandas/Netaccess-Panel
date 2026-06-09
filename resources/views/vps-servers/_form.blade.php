@php $srv = $server ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Server *</label>
        <input type="text" name="name" value="{{ old('name', $srv?->name) }}" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provider Type *</label>
        <select name="provider_type" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            @foreach(['manual', 'proxmox', 'virtualizor', 'solusvm', 'cloud_api'] as $type)
            <option value="{{ $type }}" {{ old('provider_type', $srv?->provider_type) === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Host / IP *</label>
        <input type="text" name="host" value="{{ old('host', $srv?->host) }}" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('host') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SSH Port</label>
        <input type="number" name="ssh_port" value="{{ old('ssh_port', $srv?->ssh_port ?? 22) }}" min="1" max="65535" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location *</label>
        <input type="text" name="location" value="{{ old('location', $srv?->location) }}" required placeholder="Jakarta, ID"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
        <select name="status" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="active" {{ old('status', $srv?->status) === 'active' ? 'selected' : '' }}>Active</option>
            <option value="maintenance" {{ old('status', $srv?->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            <option value="offline" {{ old('status', $srv?->status) === 'offline' ? 'selected' : '' }}>Offline</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API URL</label>
        <input type="url" name="api_url" value="{{ old('api_url', $srv?->api_url) }}"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="https://proxmox.example.com:8006/api2/json">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Username</label>
        <input type="text" name="api_username" value="{{ old('api_username', $srv?->api_username) }}"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Token</label>
        <input type="password" name="api_token" value="" placeholder="{{ $srv?->api_token ? '••••••••' : '' }}"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <p class="text-xs text-gray-400 mt-0.5">Kosongkan jika tidak ingin mengubah.</p>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Secret</label>
        <input type="password" name="api_secret" value="" placeholder="{{ $srv?->api_secret ? '••••••••' : '' }}"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <p class="text-xs text-gray-400 mt-0.5">Kosongkan jika tidak ingin mengubah.</p>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes', $srv?->notes) }}</textarea>
    </div>
</div>
