@extends('layouts.app')
@section('title', 'Buat Tiket Client')

@section('content')
<style>
.admin-create-wrap{max-width:980px}
.admin-create-card{border-radius:28px;padding:28px;border:1px solid rgba(148,163,184,.18);background:radial-gradient(circle at top right,rgba(37,99,235,.22),transparent 34%),radial-gradient(circle at bottom left,rgba(16,185,129,.18),transparent 36%),linear-gradient(135deg,rgba(15,23,42,.96),rgba(30,41,59,.88));box-shadow:0 24px 60px rgba(0,0,0,.28)}
.admin-pill{display:inline-flex;padding:7px 14px;border-radius:999px;border:1px solid rgba(147,197,253,.32);color:#bfdbfe;background:rgba(37,99,235,.12);font-size:12px;font-weight:950;margin-bottom:14px}
.admin-title{color:white;font-size:34px;font-weight:950;margin:0}
.admin-desc{color:#cbd5e1;margin-top:10px;line-height:1.7}
.form-card{margin-top:22px;border-radius:24px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.82);padding:24px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:8px;margin-bottom:16px}
.form-group label{color:#cbd5e1;font-weight:900;font-size:13px}
.form-control{width:100%;border-radius:15px;border:1px solid rgba(148,163,184,.18);background:rgba(2,6,23,.45);color:white;padding:13px 14px;outline:none}
.form-control:focus{border-color:#60a5fa}
.form-btn{border:none;border-radius:14px;padding:13px 16px;color:white;background:linear-gradient(135deg,#2563eb,#16a34a);font-weight:950;cursor:pointer}
.form-back{display:inline-flex;margin-left:10px;text-decoration:none;border-radius:14px;padding:13px 16px;color:#e2e8f0;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16);font-weight:950}
@media(max-width:720px){.form-grid{grid-template-columns:1fr}.admin-title{font-size:28px}}
</style>

<div class="admin-create-wrap">
    <section class="admin-create-card">
        <div class="admin-pill">🎫 Admin Ticket</div>
        <h1 class="admin-title">Buat Tiket untuk Client</h1>
        <div class="admin-desc">
            Admin bisa membuka tiket atas nama client. Tiket ini akan muncul di client portal mereka.
        </div>

        <div class="form-card">
            <form method="POST" action="{{ route('admin.tickets.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>Pilih Client</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">-- Pilih client --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                    data-customer-id="{{ $client->ticket_customer_id ?? '' }}"
                                    {{ old('user_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} — {{ $client->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <small style="color:#fca5a5;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" placeholder="Contoh: Informasi aktivasi layanan VPS" required>
                    @error('subject') <small style="color:#fca5a5;">{{ $message }}</small> @enderror
                </div>


                <div class="form-group">
                    <label>Layanan Terkait</label>
                    <select name="related_service" id="related_service" class="form-control">
                        <option value="">Tidak terkait layanan tertentu</option>
                        @foreach(($serviceOptions ?? []) as $service)
                            <option value="{{ $service['value'] }}"
                                    data-user-id="{{ $service['user_id'] ?? '' }}"
                                    data-customer-id="{{ $service['customer_id'] ?? '' }}"
                                    {{ old('related_service') === $service['value'] ? 'selected' : '' }}>
                                {{ $service['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color:#94a3b8;">Pilih layanan supaya client dan admin tahu tiket ini membahas layanan mana.</small>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category" class="form-control" required>
                            <option value="general">General</option>
                            <option value="vpn">VPN</option>
                            <option value="hosting">Hosting</option>
                            <option value="vps">VPS</option>
                            <option value="billing">Billing / Invoice</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Prioritas</label>
                        <select name="priority" class="form-control" required>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Pesan Admin</label>
                    <textarea name="message" rows="7" class="form-control" placeholder="Tulis pesan untuk client..." required>{{ old('message') }}</textarea>
                    @error('message') <small style="color:#fca5a5;">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label>Upload Attachment</label>
                    <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                    <small style="color:#94a3b8;">Format: JPG, PNG, WEBP, PDF. Maksimal 5 MB.</small>
                    @error('attachment') <small style="color:#fca5a5;">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="form-btn">Buat Tiket</button>
                <a href="{{ route('admin.tickets.index') }}" class="form-back">Kembali</a>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userSelect = document.getElementById('user_id');
    const serviceSelect = document.getElementById('related_service');

    if (!userSelect || !serviceSelect) return;

    const allOptions = Array.from(serviceSelect.options).map(option => ({
        value: option.value,
        text: option.text,
        userId: option.getAttribute('data-user-id') || '',
        customerId: option.getAttribute('data-customer-id') || '',
        selected: option.selected
    }));

    function getSelectedCustomerId() {
        const selected = userSelect.options[userSelect.selectedIndex];
        if (!selected) return '';
        return selected.getAttribute('data-customer-id') || '';
    }

    function rebuildServiceOptions() {
        const selectedUserId = userSelect.value || '';
        const selectedCustomerId = getSelectedCustomerId();

        serviceSelect.innerHTML = '';

        let matchedServices = [];

        allOptions.forEach(item => {
            if (item.value === '') return;

            const matchUser =
                item.userId &&
                selectedUserId &&
                item.userId === selectedUserId;

            const matchCustomer =
                item.customerId &&
                selectedCustomerId &&
                item.customerId === selectedCustomerId;

            if (matchUser || matchCustomer) {
                matchedServices.push(item);
            }
        });

        if (matchedServices.length > 0) {
            matchedServices.forEach((item, index) => {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.text;
                option.setAttribute('data-user-id', item.userId);
                option.setAttribute('data-customer-id', item.customerId);

                // otomatis pilih layanan pertama
                if (index === 0) {
                    option.selected = true;
                }

                serviceSelect.appendChild(option);
            });
        } else {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Tidak terkait layanan tertentu';
            defaultOption.selected = true;
            serviceSelect.appendChild(defaultOption);

            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = 'Client ini belum punya layanan';
            emptyOption.disabled = true;
            serviceSelect.appendChild(emptyOption);
        }
    }

    userSelect.addEventListener('change', rebuildServiceOptions);
    rebuildServiceOptions();
});
</script>

@endsection
