@extends('layouts.client')
@section('title', 'Buat Tiket')

@section('content')
<style>
.form-wrap{max-width:850px}
.form-card{border-radius:26px;padding:28px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.82);box-shadow:0 24px 60px rgba(0,0,0,.25)}
.form-title{color:white;font-size:32px;font-weight:950;margin:0 0 8px 0}
.form-desc{color:#94a3b8;margin-bottom:24px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:8px;margin-bottom:16px}
.form-group label{color:#cbd5e1;font-weight:900;font-size:13px}
.form-control{width:100%;border-radius:14px;border:1px solid rgba(148,163,184,.18);background:rgba(2,6,23,.45);color:white;padding:13px 14px;outline:none}
.form-control:focus{border-color:#60a5fa}
.form-btn{border:none;border-radius:14px;padding:13px 16px;color:white;background:linear-gradient(135deg,#16a34a,#2563eb);font-weight:950;cursor:pointer}
.form-back{display:inline-flex;margin-left:10px;text-decoration:none;border-radius:14px;padding:13px 16px;color:#e2e8f0;background:rgba(30,41,59,.78);border:1px solid rgba(148,163,184,.16);font-weight:950}
@media(max-width:720px){.form-grid{grid-template-columns:1fr}.form-title{font-size:27px}}
</style>

<div class="form-wrap">
    <div class="form-card">
        <h1 class="form-title">Buat Tiket Baru</h1>
        <div class="form-desc">Jelaskan kendala kamu sedetail mungkin agar admin lebih cepat bantu.</div>

        <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject', request('subject')) }}" placeholder="Contoh: VPS tidak bisa login" required>
                @error('subject') <small style="color:#fca5a5;">{{ $message }}</small> @enderror
            </div>


            <div class="form-group">
                <label>Layanan Terkait</label>
                <select name="related_service" class="form-control">
                    <option value="">Tidak terkait layanan tertentu</option>
                    @foreach(($serviceOptions ?? []) as $service)
                        <option value="{{ $service['value'] }}" {{ old('related_service') === $service['value'] ? 'selected' : '' }}>
                            {{ $service['label'] }}
                        </option>
                    @endforeach
                </select>
                <small style="color:#94a3b8;">Pilih layanan yang bermasalah agar admin tahu konteks tiket.</small>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="category" class="form-control" required>
                        <option value="general" {{ old('category', request('category')) === 'general' ? 'selected' : '' }}>General</option>
                        <option value="vpn" {{ old('category', request('category')) === 'vpn' ? 'selected' : '' }}>VPN</option>
                        <option value="hosting" {{ old('category', request('category')) === 'hosting' ? 'selected' : '' }}>Hosting</option>
                        <option value="vps" {{ old('category', request('category')) === 'vps' ? 'selected' : '' }}>VPS</option>
                        <option value="billing" {{ old('category', request('category')) === 'billing' ? 'selected' : '' }}>Billing / Invoice</option>
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
                <label>Pesan</label>
                <textarea name="message" rows="7" class="form-control" placeholder="Tulis kendala kamu..." required>{{ old('message') }}</textarea>
                @error('message') <small style="color:#fca5a5;">{{ $message }}</small> @enderror
            </div>


            <div class="form-group">
                <label>Upload Screenshot / Bukti Pembayaran</label>
                <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                <small style="color:#94a3b8;">Format: JPG, PNG, WEBP, PDF. Maksimal 5 MB.</small>
                @error('attachment') <small style="color:#fca5a5;">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="form-btn">Kirim Tiket</button>
            <a href="{{ route('client.tickets.index') }}" class="form-back">Kembali</a>
        </form>
    </div>
</div>
@endsection
