# Monitoring HM

Aplikasi pencatatan HM harian dengan Laravel 12, Breeze Blade, MySQL, dan Laravel Excel.

## Menjalankan di XAMPP

1. Gunakan PHP 8.2 atau lebih baru dan Composer. Aktifkan ekstensi PHP `pdo_mysql`, `fileinfo`, `zip`, `gd`, `xml`, dan `mbstring`.
2. Nyalakan MySQL dari XAMPP, lalu buat database `hm_monitoring` dengan collation `utf8mb4_unicode_ci` di phpMyAdmin.
3. Periksa konfigurasi MySQL pada `.env` (default XAMPP: user `root`, password kosong).
4. Dari root project ini, jalankan:

   ```bash
   composer install
   npm install
   php artisan key:generate
   php artisan migrate --seed
   npm run build
   php artisan serve
   ```

5. Buka `http://127.0.0.1:8000/register` untuk membuat akun. Setelah login, input HM dan ekspor laporan tersedia di aplikasi. Halaman **Laporan** menyediakan ekspor Check Up Mesin, rekap HM bulanan, dan riwayat pergantian komponen.

## Data station dan mesin

Seeder menyediakan `Opt. Loading Ramp`, `Opt. EBP`, `Opt. Press`, dan `Opt. Boiler`. Tambahkan stasiun dan mesin melalui menu **Master Data** di dalam aplikasi. Contoh SQL untuk data awal:

Data komponen dikelola dari menu **Data Master > Data Komponen**. Setiap komponen harus dikaitkan dengan stasiun serta mesin yang sudah terdaftar. Catat pemasangan ulang melalui **Ganti Komponen**; data komponen aktif diperbarui dan catatan sebelumnya tetap tersedia pada **Riwayat Pergantian**.

```sql
INSERT INTO machines (station_id, name, pic, hm_awal, created_at, updated_at)
SELECT id, 'FFB SCRAPPER CONVEYOR NO. 1', 'Nama PIC', 0, NOW(), NOW()
FROM stations
WHERE name = 'Opt. Loading Ramp';
```

Ganti PIC dan HM awal dengan data yang telah diverifikasi. Database membatasi satu log per mesin per tanggal. HM terpakai dihitung dari pembacaan sebelumnya (atau `hm_awal` untuk log pertama). Jika tanggal lama dikoreksi, pemakaian log sesudahnya ikut dihitung ulang.

## Pengujian

Test menggunakan SQLite in-memory agar tidak mengubah database MySQL:

```bash
php artisan test
```

Excel bulanan memiliki sheet Rekap Bulanan dan satu sheet per station: Unit Mesin, PIC, HM Awal, 31 kolom reading, 31 kolom pemakaian, dan Total HM Terpakai. Riwayat Check Up dapat diunduh melalui tombol **Download Excel Check Up**; file berisi satu sheet untuk setiap mesin pada Data Master, termasuk mesin yang belum memiliki riwayat.
