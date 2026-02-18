# Panduan Instalasi Sistem Antrian Online (SiAntre)

## Prasyarat Sistem

- PHP >= 8.2
- Composer
- MySQL 5.7+ atau MariaDB 10.3+
- Web Server (Apache/Nginx)
- Node.js dan npm (untuk kompilasi assets)

## Langkah-langkah Instalasi

### 1. Clone atau Unduh Proyek

```bash
git clone <repository-url> siantre
cd siantre
```

### 2. Instal Dependensi PHP

```bash
composer install
```

### 3. Konfigurasi Lingkungan

Salin file `.env.example` ke `.env` dan sesuaikan konfigurasi:

```bash
cp .env.example .env
```

Edit file `.env` dan atur konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siantre
DB_USERNAME=nama_pengguna_database
DB_PASSWORD=kata_sandi_database
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Instal Dependensi Frontend

```bash
npm install
npm run build
```

### 6. Migrasi Database

Pastikan database telah dibuat, lalu jalankan migrasi:

```bash
php artisan migrate
```

### 7. Seed Data Awal

```bash
php artisan db:seed
```

### 8. Konfigurasi Web Server

#### Apache (`.htaccess`)
File `.htaccess` sudah disertakan dalam proyek.

#### Nginx
```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 9. Jalankan Aplikasi

Untuk development:

```bash
php artisan serve
```

Atau konfigurasikan web server Anda untuk menunjuk ke direktori `public/`.

## Konfigurasi Cron Job (Opsional)

Untuk fitur reset otomatis antrian harian, tambahkan entri berikut ke crontab sistem:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Akun Default

Setelah instalasi selesai, akun admin default akan tersedia:

- Email: `admin@siantre.test`
- Password: `password`

Akun operator default:
- Email: `operator@siantre.test`
- Password: `password`

## Troubleshooting

### Masalah Umum

1. **Error saat migrasi database**: Pastikan nama database sudah dibuat dan kredensial benar.
2. **Asset tidak muncul**: Jalankan `npm run build` atau `npm run dev`.
3. **Permission error**: Pastikan direktori `storage` dan `bootstrap/cache` dapat ditulis.

### Clear Cache

Jika mengalami masalah, coba bersihkan cache:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Update Sistem

Untuk memperbarui sistem:

```bash
git pull origin main
composer install
php artisan migrate
npm install
npm run build
```

Pastikan selalu backup database sebelum melakukan update.