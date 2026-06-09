@extends('layouts.app')
@section('title', 'Edit Hosting Account')

@section('content')
<style>
.he-page{max-width:900px;margin:0 auto;display:flex;flex-direction:column;gap:16px}
.he-card{border-radius:24px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.78),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16);overflow:hidden}
.he-head{padding:20px 22px;border-bottom:1px solid rgba(148,163,184,.10)}
.he-title{margin:0;color:#fff;font-size:24px;font-weight:1000;letter-spacing:-.035em}
.he-sub{margin-top:6px;color:#94a3b8;font-size:13px}
.he-body{padding:22px}
.he-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
.he-field.full{grid-column:1/-1}
.he-field label{display:block;color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em;margin-bottom:8px}
.he-control{width:100%;height:44px;border-radius:15px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.72);color:white;padding:0 13px;outline:none}
textarea.he-control{height:110px;padding:13px;resize:vertical}
.he-control:focus{border-color:rgba(96,165,250,.68);box-shadow:0 0 0 4px rgba(37,99,235,.15)}
.he-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:20px}
.he-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:0;border-radius:14px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;cursor:pointer}
.he-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent}
.he-error{color:#fca5a5;font-size:12px;margin-top:6px}
@media(max-width:780px){.he-grid{grid-template-columns:1fr}}
</style>

<div class="he-page">
    <section class="he-card">
        <div class="he-head">
            <h1 class="he-title">Edit Hosting Account</h1>
            <div class="he-sub">Update domain, username, status, expired, dan catatan hosting.</div>
        </div>

        <div class="he-body">
            <form method="POST" action="{{ route('hosting-accounts.update', $account->id) }}">
                @csrf
                @method('PUT')

                <div class="he-grid">
                    <div class="he-field">
                        <label>Domain</label>
                        <input class="he-control" name="domain" value="{{ old('domain', $account->domain) }}" required>
                        @error('domain') <div class="he-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="he-field">
                        <label>Username</label>
                        <input class="he-control" name="username" value="{{ old('username', $account->username) }}">
                        @error('username') <div class="he-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="he-field">
                        <label>Password Baru</label>
                        <input class="he-control" name="password" type="text" placeholder="Kosongkan kalau tidak diganti">
                        @error('password') <div class="he-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="he-field">
                        <label>Server Type</label>
                        <select class="he-control" name="server_type">
                            @php $serverType = old('server_type', $account->server_type ?? 'whm'); @endphp
                            <option value="whm" @selected($serverType === 'whm')>WHM / cPanel</option>
                            <option value="manual" @selected($serverType === 'manual')>Manual</option>
                        </select>
                        @error('server_type') <div class="he-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="he-field">
                        <label>Status</label>
                        @php $status = old('status', $account->status ?? 'pending'); @endphp
                        <select class="he-control" name="status" required>
                            <option value="pending" @selected($status === 'pending')>Pending</option>
                            <option value="active" @selected($status === 'active')>Active</option>
                            <option value="suspended" @selected($status === 'suspended')>Suspended</option>
                            <option value="terminated" @selected($status === 'terminated')>Terminated</option>
                            <option value="expired" @selected($status === 'expired')>Expired</option>
                        </select>
                        @error('status') <div class="he-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="he-field">
                        <label>Expired At</label>
                        <input class="he-control" type="date" name="expired_at" value="{{ old('expired_at', optional($account->expired_at)->format('Y-m-d')) }}">
                        @error('expired_at') <div class="he-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="he-field full">
                        <label>Notes</label>
                        <textarea class="he-control" name="notes">{{ old('notes', $account->notes) }}</textarea>
                        @error('notes') <div class="he-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="he-actions">
                    <a href="{{ route('hosting-accounts.index') }}" class="he-btn">Batal</a>
                    <button class="he-btn primary" type="submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
