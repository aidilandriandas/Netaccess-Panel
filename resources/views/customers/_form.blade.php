@php $c = $customer ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama *</label>
        <input type="text" name="name" value="{{ old('name', $c?->name) }}" required
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $c?->phone) }}"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $c?->email) }}"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Perusahaan</label>
        <input type="text" name="company_name" value="{{ old('company_name', $c?->company_name) }}"
               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Customer *</label>
        <select name="customer_type" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="personal" {{ old('customer_type', $c?->customer_type) === 'personal' ? 'selected' : '' }}>Personal</option>
            <option value="kantor" {{ old('customer_type', $c?->customer_type) === 'kantor' ? 'selected' : '' }}>Kantor</option>
            <option value="reseller" {{ old('customer_type', $c?->customer_type) === 'reseller' ? 'selected' : '' }}>Reseller</option>
            <option value="rtrw_net" {{ old('customer_type', $c?->customer_type) === 'rtrw_net' ? 'selected' : '' }}>RT/RW Net</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
        <select name="status" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="active" {{ old('status', $c?->status) === 'active' ? 'selected' : '' }}>Active</option>
            <option value="expired" {{ old('status', $c?->status) === 'expired' ? 'selected' : '' }}>Expired</option>
            <option value="suspended" {{ old('status', $c?->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat</label>
    <textarea name="address" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('address', $c?->address) }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
    <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">{{ old('notes', $c?->notes) }}</textarea>
</div>
