# Panduan Penggunaan Sistem Antrian Online (SiAntre) - Administrator

## Pendahuluan

Administrator adalah pengguna dengan hak akses tertinggi dalam sistem SiAntre. Panduan ini menjelaskan cara mengelola sistem secara keseluruhan termasuk manajemen layanan, pengguna, dan pelaporan.

## Login ke Sistem

1. Buka browser dan kunjungi halaman login SiAntre
2. Masukkan email dan password admin
3. Klik "Login"
4. Anda akan diarahkan ke dashboard admin

## Dashboard Admin

Dashboard admin menampilkan informasi statistik sistem:
- **Total Layanan**: Jumlah layanan yang terdaftar
- **Total Pengguna**: Jumlah akun pengguna (admin dan operator)
- **Antrian Hari Ini**: Jumlah total antrian yang diambil hari ini
- **Selesai Hari Ini**: Jumlah antrian yang telah selesai hari ini
- **Ringkasan Layanan**: Detail jumlah antrian per layanan

## Manajemen Layanan

### 1. Melihat Daftar Layanan

- Klik menu "Kelola Layanan" di sidebar atau tombol "Kelola Layanan" di dashboard
- Anda akan melihat tabel berisi semua layanan yang terdaftar

### 2. Menambah Layanan Baru

1. Klik tombol "Tambah Layanan"
2. Isi formulir:
   - **Nama Layanan**: Nama layanan (contoh: "Poli Umum", "Laboratorium")
   - **Awalan Nomor Antrian**: Kode singkat untuk awalan nomor (contoh: "A", "POLI", "LAB")
3. Klik "Simpan"

### 3. Mengedit Layanan

1. Di tabel layanan, klik link "Edit" pada layanan yang ingin diedit
2. Ubah informasi yang diperlukan
3. Klik "Perbarui"

### 4. Menghapus Layanan

- Klik tombol "Hapus" pada layanan yang ingin dihapus
- Perhatian: Layanan hanya dapat dihapus jika belum memiliki antrian

## Manajemen Pengguna

### 1. Melihat Daftar Pengguna

- Klik menu "Kelola Pengguna" di sidebar atau tombol "Kelola Pengguna" di dashboard
- Anda akan melihat tabel berisi semua pengguna sistem

### 2. Menambah Pengguna Baru

1. Klik tombol "Tambah Pengguna"
2. Isi formulir:
   - **Nama**: Nama lengkap pengguna
   - **Email**: Alamat email pengguna
   - **Password**: Kata sandi (minimal 8 karakter)
   - **Konfirmasi Password**: Ketik ulang kata sandi
   - **Peran**: Pilih antara "Admin" atau "Operator"
3. Klik "Simpan"

### 3. Mengedit Pengguna

1. Di tabel pengguna, klik link "Edit" pada pengguna yang ingin diedit
2. Ubah informasi yang diperlukan (kata sandi opsional)
3. Klik "Perbarui"

### 4. Menghapus Pengguna

- Klik tombol "Hapus" pada pengguna yang ingin dihapus
- Perhatian: Anda tidak dapat menghapus akun Anda sendiri

## Reset Antrian Harian

### 1. Reset Manual

- Di dashboard admin, klik tombol "Reset Antrian Hari Ini"
- Atau gunakan tombol "Reset Antrian" di sidebar
- Sistem akan mengubah status semua antrian yang masih "menunggu" atau "dipanggil" menjadi "dibatalkan"

### 2. Reset Otomatis

- Sistem dapat diatur untuk reset otomatis setiap jam 00:01
- Fitur ini diaktifkan melalui cron job di server

## Laporan dan Statistik

### 1. Akses Laporan

- Klik menu "Laporan" di sidebar
- Anda dapat memfilter data berdasarkan:
  - Layanan
  - Tanggal
  - Status antrian

### 2. Jenis Statistik Tersedia

- Jumlah antrian per layanan
- Rata-rata waktu tunggu
- Jam puncak kunjungan
- Tingkat pembatalan antrian

## Pengaturan Sistem

### 1. Konfigurasi Umum

Beberapa pengaturan umum dapat diubah di file `.env`:
- Nama sistem
- Waktu operasional
- Format nomor antrian

### 2. Manajemen IP Whitelist

Fitur baru memungkinkan administrator untuk mengelola daftar IP yang diizinkan mengakses sistem:

**1. Akses Menu IP Whitelist**
- Klik menu "Kelola IP Whitelist" di sidebar admin
- Atau kunjungi halaman `/admin/ip-whitelist`

**2. Menambah IP ke Whitelist**
- Klik tombol "Add IP Address"
- Isi formulir:
  - IP Address: Alamat IP yang diizinkan (contoh: 192.168.1.100)
  - Category: Pilih kategori akses (Admin atau Operator)
  - Active: Centang jika ingin diaktifkan langsung
  - Expiration Date: (Opsional) Tanggal kadaluarsa akses
  - Description: (Opsional) Deskripsi untuk identifikasi
- Klik "Add to Whitelist"

**3. Mengelola Daftar IP**
- Lihat semua IP yang terdaftar di tabel
- Gunakan toggle untuk mengaktif/nonaktifkan IP tanpa menghapus
- Gunakan ikon edit untuk mengubah detail IP
- Gunakan ikon hapus untuk menghapus IP dari whitelist

**4. Kategori IP**
- Admin: Akses khusus untuk panel administrasi
- Operator: Akses untuk panel operator

### 3. Backup Data

- Lakukan backup database secara rutin
- Gunakan perintah: `php artisan backup:run` (jika paket backup diinstal)

## Troubleshooting

### Masalah Umum

1. **Pengguna tidak bisa login**: Cek kembali email dan password, pastikan akun tidak dinonaktifkan
2. **Layanan tidak muncul**: Pastikan layanan sudah ditambahkan dan tidak dihapus
3. **Antrian tidak terupdate**: Refresh halaman atau cek koneksi database
4. **Reset antrian gagal**: Pastikan cron job diaktifkan di server

### Keamanan Sistem

- Ganti password admin secara berkala
- Gunakan password yang kuat
- Batasi akses IP jika diperlukan
- Aktifkan log aktivitas sistem

## Bantuan Teknis

Jika mengalami masalah teknis:
- Cek log sistem di `storage/logs/`
- Hubungi tim IT atau pengembang sistem
- Gunakan perintah `php artisan` untuk troubleshooting
- Backup sistem sebelum melakukan perubahan besar

## Update Sistem

Untuk memperbarui sistem:
1. Backup database dan file konfigurasi
2. Pull kode terbaru dari repository
3. Jalankan `composer install`
4. Jalankan migrasi: `php artisan migrate`
5. Update assets: `npm run build`
6. Clear cache: `php artisan config:clear`