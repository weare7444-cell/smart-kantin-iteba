# Smart Kantin ITEBA

Sistem pemesanan makanan multi-stall berbasis web untuk kantin ITEBA.  
Dibangun dengan **Laravel 12**, **Alpine.js**, dan **Tailwind CSS**.

## Fitur

### Mahasiswa (Pembeli)
- Login / Register dengan role mahasiswa
- Pilih stand kantin dan lihat menu
- Tambah ke keranjang dan checkout
- Pembayaran QRIS (simulasi)
- Status pesanan real-time

### Penjual
- Login dengan akun penjual masing-masing stand
- Dashboard Kanban (pending / processing / ready / completed)
- Update status pesanan (Siapkan, Selesai, Siap)
- Tolak pesanan dengan alasan
- Toggle stok menu (tersedia / habis)
- Tambah dan hapus menu
- Cetak struk pesanan
- Laporan penjualan (summary harian/bulanan) dengan print/PDF

## Tech Stack

| Komponen       | Teknologi                     |
|----------------|-------------------------------|
| Framework      | Laravel 12                    |
| Frontend       | Alpine.js, Tailwind CSS       |
| Database       | MySQL                         |
| Auth           | Laravel Auth (session-based)  |
| Template       | Blade                         |
| Testing        | Playwright                    |

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL / XAMPP
- Apache / Nginx

## Instalasi

```bash
# 1. Clone repo
git clone https://github.com/weare7444-cell/smart-kantin-iteba.git
cd smart-kantin-iteba

# 2. Install dependensi PHP
composer install

# 3. Install dependensi JS
npm install && npm run build

# 4. Environment
cp .env.example .env
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL

# 5. Generate key
php artisan key:generate

# 6. Buat database MySQL
mysql -u root -e "CREATE DATABASE smart_kantin_iteba"

# 7. Migrasi dan seed
php artisan migrate:fresh --seed

# 8. Jalankan
php artisan serve
```

Akses di `http://localhost:8000`.

## Struktur Database

- **users** — id, name, email, password, role (mahasiswa/penjual), stall_id
- **foods** — id, name, price, image, stall_id, is_ready
- **orders** — id, user_id, stall_id, items (JSON), total, status (pending/processing/ready/completed/rejected), catatan, reject_reason

## Struktur Folder

```
app/
  Http/
    Controllers/
      AuthController.php
      DashboardController.php
      FoodController.php
      OrderController.php
    Middleware/
      RoleCheck.php
  Models/
    Food.php
    Order.php
    User.php
database/
  migrations/
  seeders/
    DatabaseSeeder.php
    FoodSeeder.php
    UserSeeder.php
resources/views/
  auth/ (login, register)
  kantin/
    dashboard.blade.php
    sales-report.blade.php
    receipt.blade.php
    laporan-print.blade.php
    laporan-pdf.blade.php
  welcome.blade.php
routes/
  web.php
```

## Lisensi

MIT
