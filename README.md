# NetAccess Panel

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Node.js-339933?style=for-the-badge&logo=nodedotjs&logoColor=white" alt="Node.js">
</p>

**NetAccess Panel** adalah aplikasi web berbasis Laravel yang dirancang untuk mengelola layanan VPN/L2TP MikroTik, Hosting, VPS, Customer, Order, Invoice, Support Ticket, hingga konfirmasi pembayaran manual.

Project ini sangat cocok untuk penyedia layanan internet (ISP), cloud kecil, reseller VPN, hosting provider, atau bisnis UMKM yang ingin memiliki panel administrasi mandiri yang terintegrasi.

---

## 🚀 Fitur Utama

### 🖥️ Panel Utama
* **Dashboard Admin:** Ringkasan data bisnis, statistik order, dan invoice secara real-time.
* **Client Portal:** Halaman khusus pelanggan untuk mengelola layanan, tiket, dan tagihan mereka.
* **Settings Panel:** Pengaturan konfigurasi aplikasi dan sistem dalam satu tempat.
* **Full Backup Panel:** Fitur cadangan data untuk keamanan sistem.

### 👥 Manajemen Pengguna & Dukungan
* **Manajemen Customer & Admin:** Pengelolaan hak akses (`Owner`, `Admin`) dan data pelanggan.
* **Support Ticket:** Sistem tiket bantuan untuk menangani keluhan atau pertanyaan pelanggan.

### 📦 Layanan & Integrasi
* **Manajemen Paket Layanan:** Kustomisasi produk yang ditawarkan.
* **VPN/L2TP MikroTik:** Integrasi langsung dengan API MikroTik untuk manajemen user.
* **Hosting & VPS:** Integrasi dengan WHM / cPanel API serta manajemen *VPS service*.

### 💳 Transaksi & Billing
* **Manajemen Order & Invoice:** Otomatisasi pembuatan tagihan dan pelacakan status order.
* **Sistem Pembayaran:** Upload bukti pembayaran oleh pelanggan, konfirmasi manual, dan approval pembayaran oleh admin.

---

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP 8.2+ & Laravel
* **Database:** MySQL / MariaDB
* **Frontend:** Blade Template, Tailwind CSS, Vite
* **Package Manager:** Composer & NPM (Node.js 20+)
* **Integrasi API:** MikroTik API & WHM / cPanel API

---

## 🖥️ Spesifikasi & Dukungan OS

### Lingkungan Sistem

| Kategori | Lingkungan | Detail OS / Aplikasi |
| :--- | :--- | :--- |
| **Production** | Rekomendasi Utama | Ubuntu Server 22.04 LTS / 24.04 LTS |
| | Didukung Juga | Debian 12, AlmaLinux 9, Rocky Linux 9 |
| **Local Dev** | Sistem Operasi | Windows 10/11, Linux, macOS |
| | Tool Windows | Laragon, XAMPP, WSL Ubuntu, Docker |

### Kebutuhan Server (System Requirements)

* **Minimal:** CPU 1 Core, RAM 1 GB, Storage 10 GB (OS: Ubuntu 22.04 / 24.04)
* **Rekomendasi:** CPU 2 Core, RAM 2 GB atau lebih, Storage 20 GB atau lebih (OS: Ubuntu 22.04 / 24.04)

### Prasyarat Software & Ekstensi PHP
Pastikan software berikut sudah terpasang: `PHP 8.2+`, `Composer`, `MySQL/MariaDB`, `Node.js 20+ & NPM`, `Git`, `Nginx / Apache`.

Ekstensi PHP yang wajib aktif:
```text
php-cli, php-fpm, php-mysql, php-mbstring, php-xml, php-curl, php-zip, php-bcmath, php-gd, php-tokenizer, php-fileinfo, php-opcache


⚙️ Panduan Instalasi (Ubuntu Server)

Ikuti langkah-langkah berikut untuk memasang NetAccess Panel di server Anda:

1. Update Sistem & Install Package Dasar

Bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y git curl unzip zip software-properties-common

2. Install PHP, Ekstensi, & Composer

Bash
sudo apt install -y php php-cli php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-bcmath php-gd php-tokenizer php-fileinfo php-opcache

# Install Composer
curl -sS [https://getcomposer.org/installer](https://getcomposer.org/installer) | php
sudo mv composer.phar /usr/local/bin/composer
3. Setup Database (MariaDB)
Bash
sudo apt install -y mariadb-server mariadb-client
sudo systemctl enable mariadb && sudo systemctl start mariadb

# Masuk ke MariaDB untuk membuat database dan user
sudo mysql
Di dalam prompt MariaDB, jalankan perintah berikut:

SQL
CREATE DATABASE netaccess CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'netaccess'@'localhost' IDENTIFIED BY 'password_database_kamu';
GRANT ALL PRIVILEGES ON netaccess.* TO 'netaccess'@'localhost';
FLUSH PRIVILEGES;
EXIT;
4. Install Node.js 20
Bash
curl -fsSL [https://deb.nodesource.com/setup_20.x](https://deb.nodesource.com/setup_20.x) | sudo -E bash -
sudo apt install -y nodejs
5. Clone Repository & Install Dependency
Bash
cd /var/www
sudo git clone [https://github.com/aidilandriandas/Netaccess-Panel.git](https://github.com/aidilandriandas/Netaccess-Panel.git)
sudo chown -R $USER:$USER Netaccess-Panel
cd Netaccess-Panel

# Install dependensi PHP dan Frontend
composer install
npm install
6. Konfigurasi Environment (.env)
Bash
cp .env.example .env
nano .env
Sesuaikan bagian konfigurasi database dan URL aplikasi Anda:

Cuplikan kode
APP_NAME="NetAccess Panel"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=[http://domain-kamu.com](http://domain-kamu.com)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=netaccess
DB_USERNAME=netaccess
DB_PASSWORD=password_database_kamu
Setelah selesai mengedit, buat application key:

Bash
php artisan key:generate
7. Migrasi Database & Folder Permission
Bash
# Jalankan migrasi dan seeder
php artisan migrate
php artisan db:seed

# Hubungkan folder storage
php artisan storage_link

# Atur hak akses folder agar bisa dibaca oleh web server
sudo chown -R www-data:www-data /var/www/Netaccess-Panel
sudo chmod -R 775 /var/www/Netaccess-Panel/storage
sudo chmod -R 775 /var/www/Netaccess-Panel/bootstrap/cache
🏃 Menjalankan Aplikasi
Pengembangan (Local Development)
Bash
# Menjalankan server lokal
php artisan serve

# Menjalankan Vite (Hot Reload frontend)
npm run dev
Aplikasi dapat diakses melalui: http://localhost:8000

Produksi (VPS dengan IP Publik)
Bash
# Compile aset frontend terlebih dahulu
npm run build

# Menjalankan server di IP Publik
php artisan serve --host=0.0.0.0 --port=8000
Aplikasi dapat diakses melalui: http://IP-SERVER-ANDA:8000

🔐 Informasi Login Default
Setelah proses instalasi dan db:seed berhasil dijalankan, Anda dapat masuk menggunakan akun default berikut:

Email: admin@netaccess.local

Password: password

Role: Owner / Admin

⚠️ PENTING: Demi keamanan, segera ubah password default Anda atau buat akun admin baru setelah berhasil masuk ke sistem untuk pertama kalinya.