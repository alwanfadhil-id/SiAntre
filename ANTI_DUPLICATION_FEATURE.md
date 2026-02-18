# Anti-Duplication Queue Feature

## Overview
The anti-duplication queue feature prevents users from taking multiple queue numbers for the same service per day, enhancing fairness and preventing system abuse.

## Business Rules

### Core Rule
- Each IP address can only take one queue per service per day
- Exception: User can take a new queue if their previous queue is marked as 'done' or 'canceled'

### Status-Based Validation
- **Waiting ('waiting')**: User cannot take new queue for same service
- **Called ('called')**: User cannot take new queue for same service  
- **Done ('done')**: User can take new queue for same service
- **Canceled ('canceled')**: User can take new queue for same service

### Allowed Behaviors
- Take queues for different services simultaneously
- Take new queue for same service on different days
- Take new queue for same service if previous was completed or canceled

## Technical Implementation

### Database Changes
- Added `ip_address` column to `queues` table with indexing
- Column tracks the IP address of the user who took the queue

### Model Methods
- `userCanTakeQueueForService(string $ipAddress, int $serviceId)`: Checks if user can take new queue
- `getUserLatestQueueForService(string $ipAddress, int $serviceId)`: Gets user's latest queue for service

### Validation Logic
```php
public static function userCanTakeQueueForService(string $ipAddress, int $serviceId): bool
{
    // Get the user's latest queue for this service today
    $existingQueue = self::where('ip_address', $ipAddress)
        ->where('service_id', $serviceId)
        ->whereDate('created_at', now()->toDateString())
        ->latest('created_at') // Get the most recent queue
        ->first();
    
    // If no previous queue exists, user can take a new one
    if (!$existingQueue) {
        return true;
    }
    
    // If previous queue is done or canceled, user can take a new one
    if (in_array($existingQueue->status, ['done', 'canceled'])) {
        return true;
    }
    
    // If previous queue is still in process (waiting or called), user cannot take a new one
    return false;
}
```

## User Experience

### Success Path
1. User visits service selection page
2. User selects a service they haven't taken queue for today
3. User receives queue number successfully

### Blocked Path
1. User attempts to take queue for service they already have active queue for
2. System checks user's latest queue status for that service
3. If status is 'waiting' or 'called', user receives error message:
   - "Anda sudah mengambil antrian untuk layanan {service_name} dengan nomor {queue_number} hari ini (status: {status_message}). Anda hanya bisa mengambil antrian baru setelah antrian sebelumnya selesai atau dibatalkan."

## Security Benefits

### Abuse Prevention
- Prevents users from gaming the system by taking multiple queues
- Stops spam queue generation from same IP
- Maintains queue fairness for all users

### Monitoring
- Tracks IP addresses for each queue
- Logs attempts to violate anti-duplication rules
- Enables identification of suspicious activities

## Administrative Considerations

### Monitoring
- Admin can view IP addresses associated with queues
- Reports can show queue patterns by IP address
- Helps identify potential system abuse

### Exception Handling
- In special cases, admin may need to manually assist users
- Clear error messages help users understand the rules
- System remains fair while preventing abuse

## Testing

### Test Cases Implemented
- User cannot take multiple queues for same service when previous is active
- User can take new queue when previous is 'done'
- User can take new queue when previous is 'canceled'
- User can take queues for different services simultaneously
- Cross-service flexibility maintained

## Future Enhancements

### Potential Improvements
- Combine IP tracking with device fingerprinting
- Implement temporary IP blacklisting for repeated violations
- Add administrative override for special cases
- Consider integrating with national ID system for stronger identity verification