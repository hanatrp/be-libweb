# LibWeb Backend Documentation

Ini adalah dokumentasi *backend* untuk Sistem Informasi Perpustakaan (LibWeb), yang dibangun menggunakan **Laravel 11** dan **MySQL**. Backend ini menyediakan API RESTful untuk dihubungkan dengan frontend React.

## Spesifikasi
- **Framework**: Laravel 11
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **ORM**: Eloquent (Dilengkapi dengan *Soft Deletes* & *Event Hooks*)

## Cara Menjalankan di Lokal

### 1. Persiapan Database
Pastikan server MySQL Anda berjalan (misal: via XAMPP).
Buat database baru bernama `libweb_db` pada phpMyAdmin atau MySQL *command line*:
```sql
CREATE DATABASE libweb_db;
```

### 2. Konfigurasi Lingkungan (.env)
Buka folder `be` (backend ini), salin `.env.example` menjadi `.env` (sudah dilakukan otomatis jika Anda menggunakan skrip ini).
Pastikan kredensial *database* di `.env` sesuai dengan konfigurasi MySQL Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=libweb_db
DB_USERNAME=root
DB_PASSWORD=  # (Isi jika ada password)
```

### 3. Migrasi & Seeding (Dummy Data)
Untuk membuat semua tabel beserta relasinya sekaligus mengisi data percobaan (Admin, Member, Buku, FAQ), jalankan perintah di dalam folder `be`:
```bash
php artisan migrate:fresh --seed
```

> **Catatan:** Data dummy yang di-generate:
> **Admin:** Email: `admin@libweb.com` | Password: `password123`
> **Member:** Email: `member@libweb.com` | Password: `password123`

### 4. Menjalankan Server
Jalankan development server bawaan Laravel:
```bash
php artisan serve
```
Server akan berjalan di `http://127.0.0.1:8000`. Endpoint API berada di `http://127.0.0.1:8000/api/...`.

---

## Struktur Database & Model

1. **User**: Menyimpan data anggota & admin (memiliki kolom `role: admin|member`).
2. **Book**: Menyimpan data buku fisik (dengan `stock` dan `rack_location`) & e-book (dengan path PDF).
3. **Loan**: Mencatat transaksi peminjaman. Memiliki **Eloquent Event Observer** yang otomatis:
   - *Mengurangi* stok buku ketika status berubah menjadi `borrowed`.
   - *Menambah* stok buku ketika status berubah menjadi `returned`.
4. **ChatMessage** & **ChatTemplate**: Untuk fitur komunikasi dengan Admin atau *Chatbot* FAQ.
