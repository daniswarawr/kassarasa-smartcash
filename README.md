# KASSARASA — SmartCash UMKM

Aplikasi **Website Kasir Digital & Manajemen Usaha UMKM Kuliner** berbasis **Laravel 12 + Filament v3 + MySQL**.

Repository GitHub: `https://github.com/daniswarawr/kassarasa-smartcash.git`

---

## Fitur Utama
- **Dashboard Analitik:** Total Penjualan, Total Transaksi, Estimasi Laba Kotor, Stok Menipis, Grafik Penjualan 30 Hari, Transaksi Terbaru.
- **Kasir (Smart POS):** Pilih menu dengan foto, keranjang belanja, metode pembayaran (Tunai, QRIS, Transfer, Debit, Kredit), hitung kembalian otomatis, dan pengurangan stok produk & bahan baku otomatis.
- **Produk / Menu & Kategori:** CRUD lengkap dengan foto menu, harga jual, HPP, stok, dan status.
- **Smart Inventory (Stok Bahan Baku):** Pantau stok bahan baku dengan status `Aman`, `Menipis`, `Habis` serta badge peringatan.
- **HPP Calculator:** Hitung HPP per porsi, harga jual, laba kotor, margin (%), dan simulasi N porsi.
- **Laporan Penjualan:** Filter tanggal, grafik penjualan harian, produk terlaris, serta estimasi HPP & laba kotor.

---

## Panduan Setup untuk Kolaborator (Setelah `git clone`)

Pastikan sudah terinstall **PHP 8.2+ (XAMPP)**, **Composer**, dan **Git**, serta ekstensi PHP `intl` dan `zip` aktif di `php.ini`.

1. **Clone repository:**
   ```powershell
   git clone https://github.com/daniswarawr/kassarasa-smartcash.git
   cd kassarasa-smartcash
   ```

2. **Install dependency PHP:**
   ```powershell
   composer install
   ```

3. **Siapkan file `.env` & App Key:**
   ```powershell
   copy .env.example .env
   php artisan key:generate
   ```

4. **Buat database MySQL (`kasir_digital`)** via phpMyAdmin / XAMPP, lalu jalankan migrasi & seeder:
   ```powershell
   php artisan migrate --seed
   php artisan storage:link
   ```

5. **Jalankan server:**
   ```powershell
   php artisan serve
   ```
   Buka browser: `http://127.0.0.1:8000/admin`
   - **Email:** `admin@kasirdigital.com`
   - **Password:** `admin123`

---

## Alur Kerja Kolaborasi Git Tim
1. Sebelum mulai coding, selalu tarik pembaruan terbaru:
   ```powershell
   git pull origin main
   ```
2. Buat branch baru untuk fitur/perbaikan masing-masing (atau kerjakan di branch fitur lalu buat Pull Request):
   ```powershell
   git checkout -b fitur/nama-fitur
   ```
3. Simpan dan upload perubahan ke GitHub:
   ```powershell
   git add .
   git commit -m "Deskripsi perubahan"
   git push -u origin fitur/nama-fitur
   ```
