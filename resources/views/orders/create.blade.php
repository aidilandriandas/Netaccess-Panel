@extends('layouts.app')
@section('title', 'Create Order')

@section('content')
<style>
.uo-page{max-width:1100px;margin:0 auto;display:flex;flex-direction:column;gap:16px}
.uo-hero{position:relative;overflow:hidden;border-radius:26px;padding:22px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(135deg,rgba(15,23,42,.82),rgba(30,41,59,.56));box-shadow:0 20px 55px rgba(0,0,0,.22)}
.uo-hero:before{content:"";position:absolute;right:-90px;top:-110px;width:280px;height:280px;border-radius:999px;background:conic-gradient(from 180deg,rgba(37,99,235,.34),rgba(124,58,237,.26),rgba(14,165,233,.13));animation:uoSpin 20s linear infinite;opacity:.55}
@keyframes uoSpin{to{transform:rotate(360deg)}}
.uo-inner{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.uo-pill{display:inline-flex;padding:7px 12px;border-radius:999px;color:#bfdbfe;background:rgba(37,99,235,.15);border:1px solid rgba(147,197,253,.22);font-size:11px;font-weight:950;margin-bottom:10px}
.uo-title{margin:0;color:#fff;font-size:31px;font-weight:1000;letter-spacing:-.045em}
.uo-sub{margin-top:7px;color:#cbd5e1;font-size:13px}
.uo-card{overflow:hidden;border-radius:25px;border:1px solid rgba(148,163,184,.16);background:linear-gradient(180deg,rgba(15,23,42,.76),rgba(15,23,42,.58));box-shadow:0 18px 42px rgba(0,0,0,.16)}
.uo-head{padding:16px 18px;border-bottom:1px solid rgba(148,163,184,.10);display:flex;align-items:center;gap:9px;color:white;font-size:14px;font-weight:1000}
.uo-dot{width:9px;height:9px;border-radius:999px;background:#60a5fa;box-shadow:0 0 0 5px rgba(96,165,250,.13)}
.uo-body{padding:20px}
.uo-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
.uo-field.full{grid-column:1/-1}
.uo-field label{display:block;color:#94a3b8;font-size:11px;font-weight:1000;text-transform:uppercase;letter-spacing:.075em;margin-bottom:8px}
.uo-control{width:100%;height:44px;border-radius:15px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.72);color:white;padding:0 13px;outline:none}
textarea.uo-control{height:105px;padding:13px;resize:vertical}
.uo-control:focus{border-color:rgba(96,165,250,.68);box-shadow:0 0 0 4px rgba(37,99,235,.15)}
.uo-tabs{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:18px}
.uo-tab{border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.55);border-radius:18px;padding:14px;text-align:left;color:#cbd5e1;cursor:pointer;transition:.2s}
.uo-tab.active{background:linear-gradient(135deg,rgba(37,99,235,.36),rgba(124,58,237,.28));border-color:rgba(147,197,253,.35);color:white}
.uo-tab-title{font-size:14px;font-weight:1000}
.uo-tab-sub{font-size:11px;color:#94a3b8;margin-top:4px;font-weight:800}
.uo-hidden{display:none}
.uo-alert{border-radius:16px;padding:13px 14px;font-size:13px;font-weight:850;margin-bottom:14px}
.uo-alert.success{color:#bbf7d0;background:rgba(34,197,94,.12);border:1px solid rgba(134,239,172,.18)}
.uo-alert.error{color:#fecaca;background:rgba(239,68,68,.12);border:1px solid rgba(252,165,165,.18)}
.uo-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;flex-wrap:wrap}
.uo-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:0;border-radius:14px;min-height:42px;padding:0 15px;color:#e5e7eb;background:rgba(15,23,42,.72);border:1px solid rgba(148,163,184,.18);font-size:12px;font-weight:950;cursor:pointer}
.uo-btn.primary{color:white;background:linear-gradient(135deg,#2563eb,#7c3aed);border-color:transparent}
.uo-error{color:#fca5a5;font-size:12px;margin-top:6px}
@media(max-width:820px){.uo-grid,.uo-tabs{grid-template-columns:1fr}.uo-title{font-size:26px}}
</style>

<div class="uo-page">

    <section class="uo-hero">
        <div class="uo-inner">
            <div>
                <div class="uo-pill">🧾 Unified Order</div>
                <h1 class="uo-title">Create Order</h1>
                <div class="uo-sub">Satu halaman untuk order VPN, Hosting, dan VPS.</div>
            </div>

            <a href="{{ route('dashboard') }}" class="uo-btn">← Dashboard</a>
        </div>
    </section>

    @if(session('success'))
        <div class="uo-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="uo-alert error">{{ session('error') }}</div>
    @endif

    <section class="uo-card">
        <div class="uo-head">
            <span class="uo-dot"></span>
            Order Baru
        </div>

        <div class="uo-body">
            <div class="uo-tabs">
                <button type="button" class="uo-tab active" data-type="vpn">
                    <div class="uo-tab-title">🛡️ VPN / L2TP</div>
                    <div class="uo-tab-sub">Paket VPN MikroTik</div>
                </button>

                <button type="button" class="uo-tab" data-type="hosting">
                    <div class="uo-tab-title">🌐 Hosting</div>
                    <div class="uo-tab-sub">cPanel / WHM</div>
                </button>

                <button type="button" class="uo-tab" data-type="vps">
                    <div class="uo-tab-title">🖥️ VPS</div>
                    <div class="uo-tab-sub">Virtual server</div>
                </button>
            </div>

            <form method="POST" action="{{ route('unified-orders.store') }}">
                @csrf

                <input type="hidden" name="service_type" id="service_type" value="{{ old('service_type', 'vpn') }}">

                <div class="uo-grid">
                    <div class="uo-field">
                        <label>Customer</label>
                        <select class="uo-control" name="customer_id" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->name }}{{ !empty($customer->email) ? ' - '.$customer->email : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="uo-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="uo-field">
                        <label>Package</label>
                        <select class="uo-control" name="package_id" id="package_id" required>
                            <option value="">-- Pilih Package --</option>
                            @foreach($packages as $package)
                                <option
                                    value="{{ $package->id }}"
                                    data-type="{{ $package->service_type }}"
                                    data-price="{{ $package->price }}"
                                    @selected(old('package_id') == $package->id)
                                >
                                    {{ strtoupper($package->service_type) }} - {{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('package_id') <div class="uo-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="uo-field hosting-only uo-hidden">
                        <label>Domain Hosting</label>
                        <input class="uo-control" type="text" name="domain" value="{{ old('domain') }}" placeholder="example.com">
                        @error('domain') <div class="uo-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="uo-field vps-only uo-hidden">
                        <label>Hostname VPS</label>
                        <input class="uo-control" type="text" name="hostname" value="{{ old('hostname') }}" placeholder="server-client-01">
                        @error('hostname') <div class="uo-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="uo-field vps-only uo-hidden">
                        <label>Billing Cycle</label>
                        <select class="uo-control" name="billing_cycle">
                            <option value="monthly" @selected(old('billing_cycle') === 'monthly')>Monthly</option>
                            <option value="quarterly" @selected(old('billing_cycle') === 'quarterly')>Quarterly</option>
                            <option value="yearly" @selected(old('billing_cycle') === 'yearly')>Yearly</option>
                        </select>
                    </div>

                    <div class="uo-field vps-only uo-hidden">
                        <label>OS</label>
                        <input class="uo-control" type="text" name="os_name" value="{{ old('os_name', 'Ubuntu 22.04') }}">
                    </div>

                    <div class="uo-field full">
                        <label>Notes</label>
                        <textarea class="uo-control" name="notes" placeholder="Catatan order...">{{ old('notes') }}</textarea>
                        @error('notes') <div class="uo-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="uo-actions">
                    <a href="{{ route('dashboard') }}" class="uo-btn">Batal</a>
                    <button class="uo-btn primary" type="submit">Buat Order & Invoice</button>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
(function(){
    const tabs = document.querySelectorAll('.uo-tab');
    const serviceInput = document.getElementById('service_type');
    const packageSelect = document.getElementById('package_id');
    const allOptions = Array.from(packageSelect.querySelectorAll('option')).map(opt => opt.cloneNode(true));

    function renderPackages(type) {
        const oldValue = packageSelect.value;
        packageSelect.innerHTML = '';

        allOptions.forEach(opt => {
            if (!opt.value || opt.dataset.type === type) {
                packageSelect.appendChild(opt.cloneNode(true));
            }
        });

        const exists = Array.from(packageSelect.options).some(opt => opt.value === oldValue);
        if (exists) packageSelect.value = oldValue;
    }

    function setType(type) {
        serviceInput.value = type;

        tabs.forEach(tab => tab.classList.toggle('active', tab.dataset.type === type));

        document.querySelectorAll('.hosting-only').forEach(el => el.classList.toggle('uo-hidden', type !== 'hosting'));
        document.querySelectorAll('.vps-only').forEach(el => el.classList.toggle('uo-hidden', type !== 'vps'));

        renderPackages(type);
    }

    tabs.forEach(tab => tab.addEventListener('click', () => setType(tab.dataset.type)));

    setType(serviceInput.value || 'vpn');
})();
</script>
@endsection
