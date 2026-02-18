# Auto-Sync Display Screen Documentation

## Overview
Sistem antrian SiAntre menggunakan mekanisme auto-sync untuk memastikan display screen menampilkan data antrian yang paling terbaru secara real-time.

## Mekanisme Auto-Sync

### 1. **Livewire Polling (Primary)**
Display screen menggunakan Livewire polling yang akan melakukan refresh data secara otomatis setiap **3 detik**.

```blade
<div wire:poll.3s="refreshData">
    <!-- Display content -->
</div>
```

**Keuntungan:**
- ✅ Simple, tidak butuh setup tambahan
- ✅ Bekerja dengan baik tanpa WebSocket/Redis
- ✅ Reliable untuk semua ukuran fasilitas
- ✅ Otomatis reconnect jika koneksi terputus

### 2. **Cache Invalidation**
Setiap ada perubahan antrian (call/done/cancel), cache akan di-clear otomatis:

```php
// QueueController
private function clearOperatorCaches($serviceId)
{
    $today = now()->toDateString();
    
    cache()->forget("operator_queues_{$serviceId}_{$today}");
    cache()->forget("next_queue_{$serviceId}_{$today}");
    cache()->forget("current_queue_{$serviceId}_{$today}");
    cache()->forget('services_list');
    cache()->forget("service_{$serviceId}_waiting_count_{$today}");
}
```

### 3. **Event Broadcasting (Future Enhancement)**
Event `QueueUpdated` di-dispatch setiap ada perubahan antrian:

```php
event(new \App\Events\QueueUpdated($queue));
```

Saat ini event di-log karena broadcast driver = `log`. Untuk real-time WebSocket, setup Reverb/Pusher.

## Flow Diagram

```
┌──────────────┐
│   Operator   │
│  Call Queue  │
└──────┬───────┘
       │
       ▼
┌─────────────────────────────────┐
│  QueueController::call()        │
│  1. Update status to 'called'   │
│  2. Commit transaction          │
│  3. Clear caches                │
│  4. Dispatch QueueUpdated event │
└──────┬──────────────────────────┘
       │
       ▼
┌─────────────────────────────────┐
│  Database (Queue Table)         │
│  Status updated                 │
└──────┬──────────────────────────┘
       │
       │ (Every 3 seconds)
       ▼
┌─────────────────────────────────┐
│  Display Screen                 │
│  Livewire::refreshData()        │
│  1. Query latest queue data     │
│  2. Update component state      │
│  3. Re-render view              │
└─────────────────────────────────┘
```

## Configuration

### Environment Variables (.env)

```env
# Broadcast driver (current: log)
# For real-time WebSocket: 'reverb' or 'pusher'
BROADCAST_CONNECTION=log

# Queue connection (must be 'database' or 'redis')
QUEUE_CONNECTION=database

# Cache store (must be 'database' or 'redis')
CACHE_STORE=database
```

### Livewire Polling Interval

Default: **3 seconds**

Untuk mengubah interval, edit file blade:
```blade
<!-- Change from 3s to 5s -->
<div wire:poll.5s="refreshData">
```

## Testing Auto-Sync

### Manual Test Steps:

1. **Buka 2 browser tabs:**
   - Tab 1: `/operator/queues/{service_id}` (Operator page)
   - Tab 2: `/display/show?services[]={service_id}` (Display page)

2. **Di Tab 1 (Operator):**
   - Klik "Panggil" pada antrian waiting

3. **Di Tab 2 (Display):**
   - Dalam maksimal 3 detik, nomor antrian harus update
   - Lihat animasi perubahan nomor
   - Lihat timestamp "Updated:" di pojok kanan bawah

### Expected Behavior:

| Action | Display Update Time |
|--------|---------------------|
| Call Queue | ≤ 3 seconds |
| Complete Queue | ≤ 3 seconds |
| Cancel Queue | ≤ 3 seconds |
| New Queue Created | ≤ 3 seconds |

## Troubleshooting

### Display tidak update otomatis:

1. **Check Livewire polling:**
   ```javascript
   // Open browser console
   // Look for Livewire requests every 3 seconds
   ```

2. **Check cache:**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan config:clear
   ```

3. **Check queue worker:**
   ```bash
   php artisan queue:work
   ```

4. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Update terlambat (> 3 detik):

1. Kemungkinan network latency
2. Database query lambat
3. Server overload

**Solusi:**
- Optimize database query
- Add database indexes
- Consider using Redis for cache/sessions

## Future Enhancements

### WebSocket Integration (Real-time Push)

Untuk update instant (< 1 second), setup WebSocket:

1. **Install Laravel Reverb:**
   ```bash
   composer require laravel/reverb
   php artisan reverb:install
   ```

2. **Update .env:**
   ```env
   BROADCAST_CONNECTION=reverb
   ```

3. **Enable in Livewire:**
   ```php
   public function getListeners()
   {
       return [
           'echo:queue-channel.QueueUpdated' => 'handleQueueUpdate',
       ];
   }
   ```

Dengan WebSocket:
- Update instant (< 100ms)
- Tidak perlu polling
- Lebih efisien untuk banyak display

## Performance Notes

### Current Setup (Polling):
- **Network requests:** 1 request per 3 seconds per display
- **Database queries:** 1-2 queries per poll
- **Suitable for:** 1-10 concurrent displays

### With WebSocket:
- **Network requests:** 1 persistent connection
- **Database queries:** Only on queue changes
- **Suitable for:** 10-100+ concurrent displays

## Security Considerations

1. **Public access:** Display routes are public
   - Consider adding IP whitelist for displays
   - Or use display-specific tokens

2. **Rate limiting:** Already handled by Livewire
   - Default: 3 second interval
   - Cannot be abused by users

3. **Data exposure:** Only queue numbers shown
   - No personal information displayed
   - Safe for public viewing

## Files Reference

| File | Purpose |
|------|---------|
| `app/Livewire/DisplayScreen.php` | Original display component |
| `app/Livewire/MultiServiceDisplay.php` | Multi-service display component |
| `app/Http/Controllers/Operator/QueueController.php` | Queue operations with cache clearing |
| `app/Events/QueueUpdated.php` | Event for queue changes |
| `resources/views/livewire/display-screen.blade.php` | Original display view with polling |
| `resources/views/livewire/multi-service-display.blade.php` | Multi-service display view |
| `config/broadcasting.php` | Broadcast configuration |

## Support

Untuk pertanyaan atau issue, buat ticket di GitHub repository.
