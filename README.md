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

Ekstensi PHP yang wajib aktif: `php-cli`, `php-fpm`, `php-mysql`, `php-mbstring`, `php-xml`, `php-curl`, `php-zip`, `php-bcmath`, `php-gd`, `php-tokenizer`, `php-fileinfo`, `php-opcache`