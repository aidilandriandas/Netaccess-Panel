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
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Cycle *</label>
        <select name="billing_cycle" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="monthly" {{ old('billing_cycle', $pkg?->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="quarterly" {{ old('billing_cycle', $pkg?->billing_cycle) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
            <option value="yearly" {{ old('billing_cycle', $pkg?->billing_cycle) === 'yearly' ? 'selected' : '' }}>Yearly</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CPU Cores *</label>
        <input type="number" name="cpu_cores" value="{{ old('cpu_cores', $pkg?->cpu_cores ?? 1) }}" min="1" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('cpu_cores') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RAM (MB) *</label>
        <input type="number" name="ram_mb" value="{{ old('ram_mb', $pkg?->ram_mb ?? 1024) }}" min="128" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('ram_mb') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Storage (GB) *</label>
        <input type="number" name="storage_gb" value="{{ old('storage_gb', $pkg?->storage_gb ?? 20) }}" min="1" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('storage_gb') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bandwidth (GB)</label>
        <input type="number" name="bandwidth_gb" value="{{ old('bandwidth_gb', $pkg?->bandwidth_gb) }}" min="1"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Kosongkan = Unlimited">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IPv4 Count *</label>
        <input type="number" name="ipv4_count" value="{{ old('ipv4_count', $pkg?->ipv4_count ?? 1) }}" min="1" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">OS Options</label>
        <textarea name="os_options" rows="3" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Satu OS per baris, contoh:&#10;Ubuntu 22.04&#10;CentOS 9&#10;Debian 12">{{ old('os_options', is_array($pkg?->os_options) ? implode("\n", $pkg->os_options) : '') }}</textarea>
        <p class="text-xs text-gray-400 mt-0.5">Satu OS per baris. Kosongkan jika bebas pilih.</p>
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
        <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description', $pkg?->description) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $pkg?->is_active ?? true) ? 'checked' : '' }}
                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
        </label>
    </div>
</div>
