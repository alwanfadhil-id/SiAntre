# SiAntre (Sistem Antrian Online) - MVP 1.0

Sistem Antrian Online (SiAntre) adalah aplikasi manajemen antrian berbasis web yang dirancang untuk klinik, bengkel, salon, dan kantor desa. Sistem ini menyediakan solusi komprehensif untuk pengelolaan antrian dengan pembaruan real-time dan kontrol akses berbasis peran.

## Fitur Utama

- **Multi-role System**: Admin, Operator, dan Pasien dengan izin spesifik
- **Manajemen Antrian**: Pembuatan antrian real-time dan pelacakan status
- **Manajemen Layanan**: Banyak jenis layanan dengan awalan unik
- **Tampilan Real-time**: Layar tampilan publik dengan informasi antrian langsung
- **Integrasi QR Code**: Akses mudah bagi pasien melalui kode QR
- **Reset Antrian Harian**: Reset otomatis antrian pada tengah malam
- **Logging Komprehensif**: Jejak audit lengkap dari semua operasi
- **Sistem Cache**: Optimasi kinerja dengan caching strategis
- **Pembaruan Real-time**: Pembaruan instan untuk semua layanan di dasbor operator
- **Fungsionalitas Operator**: Antarmuka operator yang ditingkatkan dengan manajemen antrian yang lebih baik
- **Anti-Duplikasi Antrian**: Validasi berbasis IP mencegah pengambilan antrian ganda per layanan per hari
- **Pencegahan Race Condition**: Mekanisme locking yang lebih kuat mencegah penomoran ganda
- **Sistem Ekspirasi Antrian**: Deteksi otomatis antrian yang tidak diproses dalam waktu tertentu
- **Peningkatan Validasi Status**: Validasi transisi status yang lebih ketat dengan logging terperinci
- **Peningkatan Error Handling**: Logging komprehensif dan pesan error yang ramah pengguna
- **Cache Tagging**: Strategi caching yang lebih efisien dengan penggunaan tag
- **Foreign Key Constraints**: Integritas data yang lebih kuat dengan constraint database
- **Queue Cleanup Automation**: Pembersihan otomatis antrian lama dan expired

## Teknologi yang Digunakan

- **Framework**: Laravel 11
- **Database**: MySQL
- **Frontend**: Tailwind CSS, Alpine.js
- **Autentikasi**: Laravel Breeze
- **Queue System**: Laravel Queues
- **Caching**: Redis
- **Testing**: PHPUnit

## Instalasi

### Prasyarat
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (untuk kompilasi aset)

### Langkah-langkah
1. Clone repositori:
   ```bash
   git clone <repository-url>
   cd siantre
   ```

2. Instal dependensi PHP:
   ```bash
   composer install
   ```

3. Instal dependensi Node.js:
   ```bash
   npm install
   ```

4. Salin file environment:
   ```bash
   cp .env.example .env
   ```

5. Generate kunci aplikasi:
   ```bash
   php artisan key:generate
   ```

6. Konfigurasi database di `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=siantre
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Jalankan migrasi:
   ```bash
   php artisan migrate
   ```

8. Kompilasi aset:
   ```bash
   npm run build
   ```

9. Mulai server pengembangan:
   ```bash
   php artisan serve
   ```

## Dokumentasi

Untuk dokumentasi lengkap, silakan lihat file [DOCUMENTATION.md](DOCUMENTATION.md).

## Struktur Aplikasi

### Model
- **User**: Manajemen pengguna dengan soft deletes dan kontrol akses berbasis peran
- **Service**: Definisi layanan dengan hubungan ke antrian
- **Queue**: Manajemen nomor antrian dengan pelacakan status

### Controller
- **Admin**: Dasbor dan manajemen layanan/pengguna
- **Operator**: Dasbor dan manajemen antrian
- **Patient**: Antarmuka pembuatan dan pelacakan antrian

### Middleware
- **RoleMiddleware**: Kontrol akses berbasis peran

## Fitur Keamanan

- **Autentikasi & Otorisasi**: RBAC (Role-Based Access Control)
- **Two-Factor Authentication (2FA)**: Otentikasi dua faktor untuk akun admin/operator
- **IP Whitelisting**: Pembatasan akses berbasis alamat IP dengan manajemen UI
- **Sistem Perizinan Granular**: Kontrol akses tingkat lanjut dengan izin spesifik per fitur
- **Manajemen Sesi**: Timeout sesi berbasis peran pengguna
- **Header Keamanan**: Perlindungan tambahan melalui header HTTP
- **Perlindungan Data**: Soft deletes dan validasi input
- **Monitoring**: Logging audit komprehensif

## Optimasi Kinerja

- **Strategi Caching**: Caching untuk daftar layanan dan status antrian
- **Optimasi Database**: Indeks dan query efisien
- **Optimasi Kode**: Algoritma efisien dan operasi asynchronous

## Testing

Aplikasi telah melalui pengujian komprehensif:
- 47 tes berhasil dilewati
- Unit tests untuk logika bisnis
- Feature tests untuk alur pengguna
- Integration tests untuk komponen sistem

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT - lihat file LICENSE untuk detail selengkapnya.

## Versi

### MVP 1.0
- Rilis awal dengan fitur manajemen antrian inti
- Sistem multi-peran (admin, operator, pasien)
- Tampilan antrian real-time
- Manajemen layanan
- Manajemen pengguna
- Pelacakan status antrian
- Reset antrian harian
- Logging komprehensif
