# Deployment Script untuk Sistem Antrian Online (SiAntre)

## Persiapan Server

### 1. Instalasi Prasyarat

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y curl wget git unzip
sudo apt install -y php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-json php8.2-ldap php8.2-soap php8.2-intl php8.2-readline php8.2-pcov php8.2-msgpack php8.2-igbinary php8.2-redis php8.2-swoole php8.2-xdebug

# Instalasi Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Instalasi Node.js dan npm
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Instalasi Database

```bash
# Instalasi MySQL
sudo apt install -y mysql-server

# Konfigurasi awal MySQL
sudo mysql_secure_installation
```

## Deployment Aplikasi

### 1. Clone Repository

```bash
cd /var/www
sudo git clone <repository-url> siantre
sudo chown -R www-data:www-data siantre
cd siantre
```

### 2. Instalasi Dependensi

```bash
# Instalasi dependensi PHP
sudo -u www-data composer install --no-dev --optimize-autoloader

# Instalasi dependensi Node.js
sudo -u www-data npm install
sudo -u www-data npm run build
```

### 3. Konfigurasi Lingkungan

```bash
# Buat file .env dari contoh
sudo -u www-data cp .env.example .env

# Generate app key
sudo -u www-data php artisan key:generate

# Edit konfigurasi database di .env
sudo nano .env
```

Contoh konfigurasi `.env`:
```
APP_NAME="Sistem Antrian Online (SiAntre)"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://siantre.contohklinik.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siantre
DB_USERNAME=nama_pengguna
DB_PASSWORD=kata_sandi

BROADCAST_CONNECTION=log
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Two-Factor Authentication Settings
OTP_ENABLED=true
OTP_LIFETIME=0
OTP_KEEP_ALIVE=true
OTP_THROW_EXCEPTION=false

# IP Whitelisting Settings
ADMIN_ALLOWED_IPS=127.0.0.1,::1
```

### 5. Instalasi Package dan Migrasi

```bash
# Buat database (login ke MySQL sebagai root)
sudo mysql -u root -p
CREATE DATABASE siantre CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL ON siantre.* TO 'nama_pengguna'@'localhost' IDENTIFIED BY 'kata_sandi';
FLUSH PRIVILEGES;
EXIT;

# Jalankan migrasi
sudo -u www-data php artisan migrate --force

# Seed data awal
sudo -u www-data php artisan db:seed
```

### 6. Konfigurasi Web Server

#### Apache

```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Buat virtual host
sudo nano /etc/apache2/sites-available/siantre.conf
```

Konfigurasi virtual host Apache:
```
<VirtualHost *:80>
    ServerName siantre.contohklinik.com
    DocumentRoot /var/www/siantre/public

    <Directory /var/www/siantre/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/siantre_error.log
    CustomLog ${APACHE_LOG_DIR}/siantre_access.log combined
</VirtualHost>
```

Aktifkan situs dan restart Apache:
```bash
sudo a2ensite siantre.conf
sudo systemctl restart apache2
```

#### Nginx (alternatif)

```bash
sudo nano /etc/nginx/sites-available/siantre
```

Konfigurasi Nginx:
```
server {
    listen 80;
    server_name siantre.contohklinik.com;
    root /var/www/siantre/public;
    index index.php;

    access_log /var/log/nginx/siantre_access.log;
    error_log /var/log/nginx/siantre_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~ /.+\.php(/.*)?$ {
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

Aktifkan situs dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/siantre /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. Konfigurasi Hak Akses

```bash
# Set hak akses direktori
sudo chown -R www-data:www-data /var/www/siantre/storage
sudo chown -R www-data:www-data /var/www/siantre/bootstrap/cache
sudo chmod -R 755 /var/www/siantre/storage
sudo chmod -R 755 /var/www/siantre/bootstrap/cache
```

### 8. Konfigurasi Cron Job

```bash
# Tambahkan cron job untuk Laravel scheduler
sudo crontab -e
```

Tambahkan baris berikut:
```
* * * * * cd /var/www/siantre && php artisan schedule:run >> /dev/null 2>&1
```

Ini akan menjalankan scheduler Laravel setiap menit untuk menangani tugas-tugas seperti reset antrian harian.

### 9. Konfigurasi Queue Worker (Opsional)

Jika menggunakan queue worker:

```bash
# Buat systemd service untuk queue worker
sudo nano /etc/systemd/system/siantre-queue-worker.service
```

Isi file dengan:
```
[Unit]
Description=SiAntre Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/var/www/siantre
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3
StandardOutput=append:/var/log/siantre-queue.log
StandardError=append:/var/log/siantre-queue-error.log

[Install]
WantedBy=multi-user.target
```

Aktifkan dan mulai service:
```bash
sudo systemctl enable siantre-queue-worker.service
sudo systemctl start siantre-queue-worker.service
```

## Post-Deployment

### 10. Clear Cache

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
```

### 11. Optimasi Aplikasi

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan event:cache
```

### 13. Uji Coba Aplikasi

Buka browser dan kunjungi alamat domain Anda untuk memastikan semuanya berfungsi dengan baik.

## Backup dan Pemulihan

### Backup Database

```bash
# Backup database
mysqldump -u nama_pengguna -p siantre > siantre_backup_$(date +%Y%m%d_%H%M%S).sql
```

### Backup File

```bash
# Backup file aplikasi
tar -czf siantre_files_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www siantre
```

## Monitoring dan Pemeliharaan

### 14. Log Monitoring

```bash
# Monitor log aplikasi
tail -f /var/www/siantre/storage/logs/laravel-$(date +%Y-%m-%d).log

# Monitor log web server
sudo tail -f /var/log/apache2/siantre_error.log  # untuk Apache
# atau
sudo tail -f /var/log/nginx/siantre_error.log   # untuk Nginx
```

### 2. Update Sistem

```bash
# Backup sistem
# ... (backup proses)

# Update kode aplikasi
cd /var/www/siantre
git pull origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm install
sudo -u www-data npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo systemctl restart php8.2-fpm
```

## Troubleshooting

### 15. Permission Error

```bash
# Perbaiki hak akses
sudo chown -R www-data:www-data /var/www/siantre/storage
sudo chown -R www-data:www-data /var/www/siantre/bootstrap/cache
sudo chmod -R 775 /var/www/siantre/storage
sudo chmod -R 775 /var/www/siantre/bootstrap/cache
```

### 16. Memory Limit Error

Tambahkan ke file `.env`:
```
PHP_MEMORY_LIMIT=512M
```

Dan ubah konfigurasi PHP:
```bash
sudo nano /etc/php/8.2/apache2/php.ini
# atau
sudo nano /etc/php/8.2/fpm/php.ini

# Cari dan ubah
memory_limit = 512M
```

Restart web server setelah perubahan.

### 17. Clear Semua Cache

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan event:clear
sudo -u www-data php artisan optimize:clear
```

## Skrip Deployment Otomatis

### 18. Skrip Deployment Otomatis

Berikut adalah skrip bash untuk deployment otomatis:

```bash
#!/bin/bash

# SiAntre Deployment Script
APP_PATH="/var/www/siantre"
BRANCH="main"

echo "Starting SiAntre deployment..."

# Backup current version
DATE=$(date +%Y%m%d_%H%M%S)
echo "Creating backup..."
sudo tar -czf "/tmp/siantre_backup_$DATE.tar.gz" -C "$(dirname $APP_PATH)" "$(basename $APP_PATH)" 2>/dev/null && echo "Backup created" || echo "Backup failed"

# Update code
echo "Updating code from repository..."
cd $APP_PATH
sudo -u www-data git fetch origin
sudo -u www-data git reset --hard origin/$BRANCH

# Install/update dependencies
echo "Installing PHP dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader

echo "Installing and building assets..."
sudo -u www-data npm install
sudo -u www-data npm run build

# Run migrations
echo "Running database migrations..."
sudo -u www-data php artisan migrate --force

# Clear and cache configurations
echo "Clearing and caching configurations..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache

# Set proper permissions
echo "Setting proper permissions..."
sudo chown -R www-data:www-data $APP_PATH/storage
sudo chown -R www-data:www-data $APP_PATH/bootstrap/cache
sudo chmod -R 775 $APP_PATH/storage
sudo chmod -R 775 $APP_PATH/bootstrap/cache

# Restart services
echo "Restarting services..."
sudo systemctl reload apache2  # atau nginx
sudo systemctl reload php8.2-fpm

echo "Deployment completed!"
```

Simpan skrip ini sebagai `deploy.sh`, berikan hak eksekusi, dan gunakan untuk deployment otomatis:

```bash
chmod +x deploy.sh
./deploy.sh
```

## Penutup

### 19. Penutup

Deployment SiAntre sekarang selesai. Pastikan untuk:
1. Menguji semua fungsi utama
2. Memverifikasi bahwa cron job berjalan dengan baik
3. Mengamankan akses ke sistem
4. Membuat jadwal backup rutin