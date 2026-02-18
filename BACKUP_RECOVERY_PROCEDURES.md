# Prosedur Backup dan Recovery - Sistem Antrian Online (SiAntre)

## Gambaran Umum

Dokumen ini menjelaskan prosedur backup dan recovery untuk sistem SiAntre. Tujuan dari dokumen ini adalah untuk memastikan bahwa data sistem dapat dipulihkan dengan cepat dan akurat dalam kasus kegagalan sistem, kerusakan data, atau insiden keamanan.

## Jenis-jenis Backup

### 1. Database Backup
- **Frekuensi**: Harian
- **Waktu**: 02:00 (setelah reset antrian harian)
- **Retensi**: 30 hari lokal, 1 tahun arsip

### 2. File Backup
- **Frekuensi**: Harian
- **Waktu**: 03:00
- **Retensi**: 30 hari lokal, 1 tahun arsip
- **Termasuk**: Assets, uploads, konfigurasi

### 3. Full System Backup
- **Frekuensi**: Mingguan
- **Waktu**: Minggu pukul 04:00
- **Retensi**: 3 bulan

## Prosedur Backup Manual

### 1. Database Backup Manual

#### Backup Seluruh Database
```bash
# Backup database SiAntre
mysqldump -u nama_pengguna -p siantre > siantre_backup_$(date +%Y%m%d_%H%M%S).sql

# Backup dengan kompresi
mysqldump -u nama_pengguna -p siantre | gzip > siantre_backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

#### Backup Struktur Saja
```bash
mysqldump -u nama_pengguna -p --no-data siantre > siantre_structure_$(date +%Y%m%d_%H%M%S).sql
```

#### Backup Data Saja
```bash
mysqldump -u nama_pengguna -p --no-create-info siantre > siantre_data_$(date +%Y%m%d_%H%M%S).sql
```

### 2. File Backup Manual

#### Backup Seluruh Direktori Aplikasi
```bash
# Backup aplikasi
tar -czf siantre_app_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www siantre

# Backup storage directory
tar -czf siantre_storage_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www/siantre/storage .
```

#### Backup Konfigurasi Penting
```bash
# Backup .env file
cp /var/www/siantre/.env /backup/location/siantre_env_$(date +%Y%m%d_%H%M%S).env

# Backup konfigurasi web server
sudo cp /etc/nginx/sites-available/siantre /backup/location/
sudo cp /etc/apache2/sites-available/siantre.conf /backup/location/
```

## Prosedur Backup Otomatis

### 1. Skrip Backup Otomatis

Buat skrip backup di `/usr/local/bin/siantre_backup.sh`:

```bash
#!/bin/bash

# SiAntre Automated Backup Script
BACKUP_DIR="/backup/siantre"
DATE=$(date +%Y%m%d_%H%M%S)
DB_USER="nama_pengguna"
DB_PASS="kata_sandi"
DB_NAME="siantre"
APP_PATH="/var/www/siantre"

# Create backup directory if not exists
mkdir -p $BACKUP_DIR/$DATE

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/$DATE/db_backup.sql.gz

# Application files backup
tar -czf $BACKUP_DIR/$DATE/app_files.tar.gz -C $(dirname $APP_PATH) $(basename $APP_PATH)

# Storage directory backup
tar -czf $BACKUP_DIR/$DATE/storage_files.tar.gz -C $APP_PATH/storage .

# Configuration backup
cp $APP_PATH/.env $BACKUP_DIR/$DATE/env_backup.env
sudo cp /etc/nginx/sites-available/siantre $BACKUP_DIR/$DATE/web_config.conf

# Clean old backups (keep last 30 days)
find $BACKUP_DIR -mindepth 1 -ctime +30 -exec rm -rf {} \;

echo "Backup completed: $DATE"
```

### 2. Konfigurasi Cron Job untuk Backup

Tambahkan ke crontab:
```
# Backup database dan aplikasi setiap hari pukul 02:00
0 2 * * * /usr/local/bin/siantre_backup.sh >> /var/log/siantre_backup.log 2>&1

# Backup mingguan pukul 04:00 setiap hari Minggu
0 4 * * 0 /usr/local/bin/siantre_full_backup.sh >> /var/log/siantre_full_backup.log 2>&1
```

### 3. Skrip Backup Mingguan (Full System)

```bash
#!/bin/bash

# SiAntre Full System Backup Script
BACKUP_DIR="/backup/siantre/full"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Include everything: database, application, configs, system settings
tar -czf $BACKUP_DIR/full_system_backup_$DATE.tar.gz \
  -X /usr/local/bin/exclude_list.txt \
  /var/www/siantre \
  /etc/nginx/sites-available/siantre \
  /etc/apache2/sites-available/siantre.conf \
  /etc/systemd/system/siantre-queue-worker.service

# Clean old backups (keep last 3 months)
find $BACKUP_DIR -mindepth 1 -ctime +90 -exec rm -rf {} \;

echo "Full system backup completed: $DATE"
```

## Prosedur Recovery

### 1. Recovery Database

#### Dari SQL Dump
```bash
# Stop queue workers jika aktif
sudo systemctl stop siantre-queue-worker.service

# Restore database
mysql -u nama_pengguna -p siantre < path_ke_backup.sql

# Start queue workers
sudo systemctl start siantre-queue-worker.service
```

#### Dari Kompresi SQL
```bash
gunzip -c path_ke_backup.sql.gz | mysql -u nama_pengguna -p siantre
```

### 2. Recovery File

#### Restore Aplikasi
```bash
# Stop web server
sudo systemctl stop nginx  # atau apache2

# Remove existing application
sudo rm -rf /var/www/siantre_new

# Extract backup
sudo tar -xzf path_ke_backup.tar.gz -C /var/www/

# Rename if necessary
sudo mv /var/www/siantre_old /var/www/siantre_old_$(date +%Y%m%d_%H%M%S)
sudo mv /var/www/siantre_new /var/www/siantre

# Set permissions
sudo chown -R www-data:www-data /var/www/siantre
sudo chmod -R 755 /var/www/siantre

# Start web server
sudo systemctl start nginx  # atau apache2
```

#### Restore Storage
```bash
# Stop web server
sudo systemctl stop nginx

# Backup current storage
sudo cp -r /var/www/siantre/storage /var/www/siantre/storage_backup_$(date +%Y%m%d_%H%M%S)

# Extract storage backup
sudo tar -xzf path_ke_storage_backup.tar.gz -C /var/www/siantre/storage

# Set permissions
sudo chown -R www-data:www-data /var/www/siantre/storage

# Start web server
sudo systemctl start nginx
```

## Recovery Prosedur Lengkap

### 1. Recovery dari Zero (Server Baru)

#### Langkah 1: Persiapan Server
```bash
# Instalasi prasyarat
sudo apt update
sudo apt install -y mysql-server nginx php8.2 php8.2-fpm php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath git composer nodejs
```

#### Langkah 2: Buat Struktur Direktori
```bash
sudo mkdir -p /var/www/siantre
sudo chown www-data:www-data /var/www/siantre
```

#### Langkah 3: Restore Database
```bash
# Buat database
sudo mysql -u root -p
CREATE DATABASE siantre CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nama_pengguna'@'localhost' IDENTIFIED BY 'kata_sandi';
GRANT ALL ON siantre.* TO 'nama_pengguna'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Restore data
mysql -u nama_pengguna -p siantre < path_ke_database_backup.sql
```

#### Langkah 4: Restore Aplikasi
```bash
# Extract aplikasi
sudo tar -xzf path_ke_aplikasi_backup.tar.gz -C /var/www/

# Instalasi dependensi
cd /var/www/siantre
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm install
sudo -u www-data npm run build

# Konfigurasi .env
sudo -u www-data cp .env.example .env
# Edit .env sesuai konfigurasi baru

# Generate key
sudo -u www-data php artisan key:generate

# Set permissions
sudo chown -R www-data:www-data /var/www/siantre/storage
sudo chown -R www-data:www-data /var/www/siantre/bootstrap/cache
```

#### Langkah 5: Konfigurasi Web Server
```bash
# Copy konfigurasi web server
sudo cp path_ke_web_config.conf /etc/nginx/sites-available/siantre
sudo ln -s /etc/nginx/sites-available/siantre /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Langkah 6: Konfigurasi Cron dan Service
```bash
# Tambahkan cron job
sudo crontab -e
# Tambahkan: * * * * * cd /var/www/siantre && php artisan schedule:run >> /dev/null 2>&1

# Konfigurasi queue worker (jika digunakan)
sudo cp path_ke_queue_service.conf /etc/systemd/system/siantre-queue-worker.service
sudo systemctl daemon-reload
sudo systemctl enable siantre-queue-worker.service
sudo systemctl start siantre-queue-worker.service
```

### 2. Recovery Parsial (Beberapa Tabel Saja)

```bash
# Backup tabel yang akan direcovery
mysqldump -u nama_pengguna -p siantre queues > queues_backup.sql

# Restore hanya tabel tertentu
mysql -u nama_pengguna -p siantre < queues_backup.sql
```

## Testing Recovery

### 1. Testing Backup Validitas
```bash
# Cek apakah backup SQL valid
mysql -u nama_pengguna -p -e "CREATE DATABASE test_recovery;"
mysql -u nama_pengguna -p test_recovery < path_ke_backup.sql
mysql -u nama_pengguna -p -e "DROP DATABASE test_recovery;"

# Cek apakah backup file valid
tar -tzf path_ke_backup.tar.gz > /dev/null && echo "Valid" || echo "Invalid"
```

### 2. Recovery Drills
- Lakukan recovery test minimal sekali dalam 3 bulan
- Gunakan server staging untuk testing
- Dokumentasikan hasil dan perbaikan yang diperlukan

## RTO dan RPO

### Recovery Time Objective (RTO)
- **Normal Operation**: 2 jam
- **Critical Operation**: 30 menit
- **Disaster Recovery**: 4 jam

### Recovery Point Objective (RPO)
- **Database**: 24 jam
- **File Konfigurasi**: 24 jam
- **Aplikasi**: 1 jam (dari git)

## Lokasi Backup

### Lokal
- Path: `/backup/siantre/`
- Retensi: 30 hari
- Media: Hard disk lokal

### Remote/Offsite
- Path: `rsync://remote-server/backup/siantre/`
- Retensi: 1 tahun
- Media: Cloud storage atau server remote

## Monitoring dan Alert

### 1. Backup Success/Failure Monitoring
```bash
# Tambahkan ke skrip backup
if [ $? -eq 0 ]; then
    echo "$(date): Backup successful" >> /var/log/siantre_backup.log
    # Kirim notifikasi sukses
else
    echo "$(date): Backup failed" >> /var/log/siantre_backup_error.log
    # Kirim alert
fi
```

### 2. Alert System
- Email ke admin saat backup gagal
- SMS ke admin saat backup tidak berjalan lebih dari 24 jam
- Dashboard monitoring status backup

## Penanggung Jawab

- **Primary**: System Administrator
- **Secondary**: IT Support
- **Emergency**: Vendor Support

## Dokumentasi Tambahan

### 1. Exclude List untuk Backup
Buat file `/usr/local/bin/exclude_list.txt`:
```
*.log
*.tmp
node_modules/
vendor/
.env.local
.env.backup
.DS_Store
Thumbs.db
```

### 2. Checklist Recovery
- [ ] Server siap digunakan
- [ ] Database terinstall dan berjalan
- [ ] Aplikasi terinstall
- [ ] Konfigurasi terpasang
- [ ] Hak akses terkonfigurasi
- [ ] Web server berjalan
- [ ] Aplikasi dapat diakses
- [ ] Fungsi utama berjalan
- [ ] Cron job aktif
- [ ] Queue worker aktif

## Penutup

Pastikan untuk:
1. Melakukan backup secara rutin
2. Mengujicoba recovery secara berkala
3. Memperbarui dokumen ini saat ada perubahan
4. Memberikan akses dokumen ini kepada personel yang berwenang
5. Menyimpan backup di lokasi yang aman dan terpisah dari server utama