# NetAccess Panel

Admin panel untuk bisnis layanan VPN WireGuard, monitoring, dan hosting. Dibangun dengan Laravel 13, Tailwind CSS, dan Blade.

## Fitur

- **Dashboard** — Statistik customer, VPN, invoice, revenue, server status
- **Manajemen Customer** — CRUD, search, filter status/tipe
- **Manajemen Paket** — CRUD paket layanan (VPN, monitoring, hosting, backup, VPS)
- **VPN WireGuard** — Generate username, IP, keys, config, QR code otomatis
- **Invoice & Billing** — Auto-generate invoice, mark paid/unpaid/cancel, upload bukti bayar
- **WhatsApp Reminder** — Tombol kirim reminder H-7/H-3/H-1/expired via wa.me
- **Auto Suspend** — Scheduler cek expired VPN dan overdue invoice
- **Server Monitoring** — CPU, RAM, disk usage, uptime, service status
- **Activity Logs** — Audit trail semua aktivitas admin
- **Settings** — Business info, payment, WireGuard server config, WhatsApp templates
- **Backup** — Backup database manual, download, delete
- **Admin Users** — Role Owner dan Admin
- **Dark/Light Mode** — Toggle dark mode dengan persist ke localStorage
- **Responsive** — Mobile-friendly dengan collapsible sidebar

## Tech Stack

- PHP 8.3 + Laravel 13
- SQLite (default) / MySQL
- Tailwind CSS 4 + Vite
- Alpine.js
- SimpleSoftwareIO/SimpleQrCode

## Instalasi

```bash
# Clone repository
git clone https://github.com/erlanggaa1/netaccess-panel.git
cd netaccess-panel

# Install dependencies
composer install
npm install && npm run build

# Setup environment
cp .env.example .env
php artisan key:generate

# Buat database SQLite
touch database/database.sqlite

# Jalankan migrasi dan seeder
php artisan migrate --seed

# Buat symlink storage
php artisan storage:link

# Jalankan server
php artisan serve
```

Buka http://localhost:8000

## Login Default

- **Email:** `admin@netaccess.local`
- **Password:** `password`
- **Role:** Owner (full access)

## Menggunakan MySQL

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=netaccess_panel
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Scheduler (Auto Suspend)

Tambahkan cron job:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Command yang tersedia:
- `php artisan vpn:check-expired` — Cek dan suspend VPN expired
- `php artisan invoice:check-overdue` — Update invoice overdue
- `php artisan panel:backup` — Backup database

## Menghubungkan WireGuard Server

1. Install WireGuard di server: `apt install wireguard`
2. Generate server keys: `wg genkey | tee server_private.key | wg pubkey > server_public.key`
3. Masukkan server public key dan private key di halaman **Settings → WireGuard Server**
4. Set endpoint ke IP publik server
5. Pastikan user web server bisa menjalankan `wg` command (sudoers)

Contoh sudoers:
```
www-data ALL=(ALL) NOPASSWD: /usr/bin/wg, /usr/bin/wg-quick
```

## Struktur Folder

```
app/
├── Console/Commands/     # CheckExpiredVpn, CheckOverdueInvoice, PanelBackup
├── Http/Controllers/     # Auth, Dashboard, Customer, Package, VpnUser, Invoice, dll.
├── Http/Middleware/       # RoleMiddleware
├── Models/               # Customer, Package, VpnUser, Invoice, Setting, dll.
├── Services/             # WireGuardService, ServerMonitorService
database/
├── migrations/           # 9 migration files
├── seeders/              # Admin, Package, Setting seeders
resources/views/
├── layouts/app.blade.php # Main layout dengan sidebar
├── auth/                 # Login, change password
├── customers/            # Index, show, create, edit
├── packages/             # Index, create, edit
├── vpn-users/            # Index, show, create
├── invoices/             # Index, show, create
├── server-status/        # Server monitoring
├── activity-logs/        # Activity log viewer
├── settings/             # Settings form
├── backups/              # Backup management
├── admin-users/          # Admin user management
├── components/           # nav-link, status-badge
└── dashboard.blade.php   # Dashboard
```

## License

MIT
