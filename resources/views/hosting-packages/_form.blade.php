@php $pkg = $package ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Paket *</label>
        <input type="text" name="name" value="{{ old('name', $pkg?->name) }}" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga (Rp) *</label>
        <input type="number" name="price" value="{{ old('price', $pkg?->price) }}" step="0.01" min="0" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Setup Fee (Rp)</label>
        <input type="number" name="setup_fee" value="{{ old('setup_fee', $pkg?->setup_fee ?? 0) }}" step="0.01" min="0"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Durasi (hari) *</label>
        <input type="number" name="duration_days" value="{{ old('duration_days', $pkg?->duration_days ?? 30) }}" min="1" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Disk Space (MB) *</label>
        <input type="number" name="disk_space_mb" value="{{ old('disk_space_mb', $pkg?->disk_space_mb ?? 1000) }}" min="1" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bandwidth (MB)</label>
        <input type="number" name="bandwidth_mb" value="{{ old('bandwidth_mb', $pkg?->bandwidth_mb) }}" min="0"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Kosongkan = Unlimited">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Email Accounts *</label>
        <input type="number" name="max_email_accounts" value="{{ old('max_email_accounts', $pkg?->max_email_accounts ?? 5) }}" min="0" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Databases *</label>
        <input type="number" name="max_databases" value="{{ old('max_databases', $pkg?->max_databases ?? 3) }}" min="0" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Addon Domains</label>
        <input type="number" name="max_addon_domains" value="{{ old('max_addon_domains', $pkg?->max_addon_domains ?? 0) }}" min="0"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Subdomains</label>
        <input type="number" name="max_subdomains" value="{{ old('max_subdomains', $pkg?->max_subdomains ?? 5) }}" min="0"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">cPanel Package Name</label>
        <input type="text" name="cpanel_package" value="{{ old('cpanel_package', $pkg?->cpanel_package) }}"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Nama paket di WHM/cPanel">
    </div>
    <div>
        <label class="flex items-center gap-2 mt-6">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $pkg?->is_active ?? true) ? 'checked' : '' }}
                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
        </label>
    </div>
</div>
<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
    <textarea name="description" rows="3"
              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description', $pkg?->description) }}</textarea>
</div>
