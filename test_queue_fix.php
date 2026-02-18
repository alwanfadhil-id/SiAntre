<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\Queue;

// Create Laravel application instance for testing
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing queue number uniqueness fix...\n";

// Create a service for testing
$service = Service::create([
    'name' => 'Test Service',
    'prefix' => 'TS'
]);

// Test 1: Generate two queue numbers and ensure they are different
$firstNumber = Queue::generateNextNumber($service->id);
$secondNumber = Queue::generateNextNumber($service->id);

echo "First number: $firstNumber\n";
echo "Second number: $secondNumber\n";

if ($firstNumber !== $secondNumber) {
    echo "✓ Test 1 PASSED: Queue numbers are different\n";
} else {
    echo "✗ Test 1 FAILED: Queue numbers are the same\n";
}

// Test 2: Try to manually create a duplicate to see if constraint works
try {
    // Create the first queue
    $queue1 = Queue::create([
        'number' => 'TS.001',
        'service_id' => $service->id,
        'status' => 'waiting',
        'queue_date' => now()->toDateString(), // Include the date column
    ]);
    
    // Try to create a second queue with the same number and date
    $queue2 = Queue::create([
        'number' => 'TS.001', // Same number
        'service_id' => $service->id,
        'status' => 'waiting',
        'queue_date' => now()->toDateString(), // Same date
    ]);
    
    echo "✗ Test 2 FAILED: Duplicate queue number was allowed\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
        echo "✓ Test 2 PASSED: Duplicate queue number was prevented by constraint\n";
    } else {
        echo "✗ Test 2 FAILED: Unexpected error: " . $e->getMessage() . "\n";
    }
}

// Clean up
$service->delete();

echo "Testing completed.\n";