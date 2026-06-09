@php $p = $package ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Paket *</label>
        <input type="text" name="name" value="{{ old('name', $p?->name) }}" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Layanan *</label>
        <select name="service_type" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            @foreach(['vpn','monitoring','hosting','backup','vps'] as $t)
            <option value="{{ $t }}" {{ old('service_type', $p?->service_type) === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga (Rp) *</label>
        <input type="number" name="price" value="{{ old('price', $p?->price) }}" required min="0" step="1000"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Setup Fee (Rp)</label>
        <input type="number" name="setup_fee" value="{{ old('setup_fee', $p?->setup_fee ?? 0) }}" min="0" step="1000"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Durasi (hari) *</label>
        <input type="number" name="duration_days" value="{{ old('duration_days', $p?->duration_days ?? 30) }}" required min="1"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Users *</label>
        <input type="number" name="max_users" value="{{ old('max_users', $p?->max_users ?? 1) }}" required min="1"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bandwidth Limit</label>
        <input type="text" name="bandwidth_limit" value="{{ old('bandwidth_limit', $p?->bandwidth_limit) }}" placeholder="e.g., 100 Mbps"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div class="flex items-end">
        <label class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $p?->is_active ?? true) ? 'checked' : '' }}
                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700 dark:text-gray-300">Aktif</span>
        </label>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
    <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('description', $p?->description) }}</textarea>
</div>
