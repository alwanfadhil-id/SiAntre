# Dokumentasi Cron Job Setup - Reset Antrian Harian

## Gambaran Umum

Sistem SiAntre menyediakan fitur reset otomatis antrian harian yang akan mengubah status semua antrian yang masih "menunggu" atau "dipanggil" menjadi "dibatalkan" pada akhir hari. Fitur ini penting untuk memastikan antrian tidak menumpuk dari hari ke hari dan sistem tetap bersih.

## Komponen yang Terlibat

### 1. Console Command
- **Nama Command**: `queue:reset-daily`
- **Lokasi File**: `app/Console/Commands/ResetDailyQueues.php`
- **Deskripsi**: Mengubah status semua antrian dari kemarin yang masih "waiting" atau "called" menjadi "canceled"

### 2. Scheduler Laravel
- **Lokasi File**: `routes/console.php`
- **Deskripsi**: Mendefinisikan jadwal eksekusi command

## Konfigurasi Cron Job

### 1. Laravel Scheduler

Laravel menyediakan sistem scheduler yang hanya membutuhkan satu entri cron job di sistem untuk menjalankan semua scheduled command.

**Entri Cron Job yang Dibutuhkan:**
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Konfigurasi di routes/console.php

```php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule the daily queue reset command to run at 1 minute past midnight
Schedule::command('queue:reset-daily')->dailyAt('00:01');
```

## Jadwal Eksekusi

- **Waktu Eksekusi**: Setiap hari pukul 00:01 (1 menit setelah tengah malam)
- **Deskripsi**: Reset semua antrian dari hari sebelumnya yang belum selesai

## Fungsi Reset Daily Queue

### Apa yang Dilakukan:
1. Mencari semua antrian dari tanggal kemarin
2. Yang memiliki status "waiting" atau "called"
3. Mengubah statusnya menjadi "canceled"
4. Mencatat jumlah antrian yang direset di log

### Kode Implementasi:
```php
public function handle()
{
    // Get yesterday's date
    $yesterday = now()->subDay()->toDateString();
    
    // Find all queues from yesterday that are still waiting or called (not done or canceled)
    $queues = Queue::whereDate('created_at', $yesterday)
                  ->whereIn('status', ['waiting', 'called'])
                  ->get();
    
    $count = 0;
    foreach ($queues as $queue) {
        $queue->update(['status' => 'canceled']);
        $count++;
    }
    
    $this->info("Successfully reset {$count} queues from {$yesterday} to canceled status.");
}
```

## Setup di Server Produksi

### 1. Tambahkan Cron Job ke Sistem

Login sebagai user yang memiliki akses ke aplikasi (biasanya user web server):

```bash
sudo crontab -e -u www-data
```

Tambahkan entri berikut:
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/path-to-your-project` dengan path absolut ke direktori aplikasi Anda.

### 2. Verifikasi Setup

**Cek apakah cron job aktif:**
```bash
sudo crontab -l -u www-data
```

**Cek log scheduler (jika ada error):**
```bash
tail -f /path-to-your-project/storage/logs/laravel-$(date +%Y-%m-%d).log
```

**Cek apakah command berjalan dengan benar:**
```bash
php artisan queue:reset-daily
```

### 3. Alternatif: Eksekusi Langsung (tidak disarankan)

Jika Anda ingin menjalankan command secara langsung tanpa Laravel scheduler:

```bash
0 0 * * * cd /path-to-your-project && php artisan queue:reset-daily >> /var/log/siantre-reset.log 2>&1
```

Namun, pendekatan ini tidak disarankan karena akan melewati fitur logging dan monitoring Laravel.

## Monitoring dan Troubleshooting

### 1. Monitoring Eksekusi

Cek log aplikasi untuk melihat apakah command berjalan:
```bash
grep -i "reset" /path-to-your-project/storage/logs/laravel-$(date +%Y-%m-%d).log
```

### 2. Troubleshooting Umum

**Masalah: Command tidak berjalan sesuai jadwal**
- Solusi: Pastikan cron job telah ditambahkan ke sistem
- Solusi: Pastikan user cron memiliki akses ke aplikasi
- Solusi: Cek permission file dan direktori

**Masalah: Error saat eksekusi command**
- Solusi: Cek log aplikasi untuk detail error
- Solusi: Pastikan database connection aktif
- Solusi: Pastikan semua dependencies terinstal

**Masalah: Tidak semua antrian direset**
- Solusi: Pastikan format tanggal benar
- Solusi: Pastikan query mencakup semua kondisi yang benar

### 3. Testing Manual

Untuk menguji apakah command berjalan dengan benar:

```bash
# Jalankan command secara manual
php artisan queue:reset-daily

# Simulasikan dengan membuat antrian kemarin
# (Gunakan tinker untuk membuat data test)
php artisan tinker
>>> $yesterday = now()->subDay();
>>> $queue = new App\Models\Queue();
>>> $queue->number = 'TEST.001';
>>> $queue->service_id = 1;
>>> $queue->status = 'waiting';
>>> $queue->created_at = $yesterday;
>>> $queue->save();
```

## Konfigurasi Waktu Alternatif

Jika waktu tengah malam tidak cocok untuk lingkungan Anda, Anda bisa mengganti waktu eksekusi di `routes/console.php`:

```php
// Misalnya, reset pada pukul 01:00
Schedule::command('queue:reset-daily')->dailyAt('01:00');

// Atau reset pada pukul 23:59 (akhir hari)
Schedule::command('queue:reset-daily')->dailyAt('23:59');
```

## Integrasi dengan Sistem Monitoring

Anda bisa menambahkan monitoring untuk memastikan cron job berjalan:

```php
// Di routes/console.php
Schedule::command('queue:reset-daily')
    ->dailyAt('00:01')
    ->before(function () {
        Log::info('Starting daily queue reset command');
    })
    ->after(function () {
        Log::info('Finished daily queue reset command');
    })
    ->pingBefore('https://monitoring-service.com/heartbeat/siantre-reset-start')
    ->thenPing('https://monitoring-service.com/heartbeat/siantre-reset-end');
```

## Penutup

Setup cron job untuk reset antrian harian telah selesai. Pastikan untuk:
1. Menambahkan cron entry ke sistem
2. Memverifikasi bahwa command berjalan sesuai jadwal
3. Memantau log untuk memastikan tidak ada error
4. Menguji secara manual jika diperlukan