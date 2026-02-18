<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\Queue;

// Create Laravel application instance for testing
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing queue number uniqueness with proper constraints...\n";

// Create a service for testing
$service = Service::create([
    'name' => 'Test Service',
    'prefix' => 'TS'
]);

// Test 1: Create two queues using the proper generation method
$firstNumber = Queue::generateNextNumber($service->id);
$secondNumber = Queue::generateNextNumber($service->id);

echo "Generated first number: $firstNumber\n";
echo "Generated second number: $secondNumber\n";

// Create the first queue record
$queue1 = Queue::create([
    'number' => $firstNumber,
    'service_id' => $service->id,
    'status' => 'waiting',
]);

// Create the second queue record
$queue2 = Queue::create([
    'number' => $secondNumber,
    'service_id' => $service->id,
    'status' => 'waiting',
]);

echo "Created first queue with number: {$queue1->number}\n";
echo "Created second queue with number: {$queue2->number}\n";

if ($queue1->number !== $queue2->number) {
    echo "✓ Test 1 PASSED: Different queue numbers were generated and created\n";
} else {
    echo "✗ Test 1 FAILED: Same queue numbers were created\n";
}

// Test 2: Try to create a duplicate manually to see if constraint works
try {
    $queue3 = Queue::create([
        'number' => $firstNumber, // Same number as the first queue
        'service_id' => $service->id,
        'status' => 'waiting',
    ]);
    
    echo "✗ Test 2 FAILED: Duplicate queue number was allowed\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
        echo "✓ Test 2 PASSED: Duplicate queue number was prevented by database constraint\n";
    } else {
        echo "✗ Test 2 FAILED: Unexpected error: " . $e->getMessage() . "\n";
    }
}

// Clean up
$queue1->delete();
$queue2->delete();
$service->delete();

echo "Testing completed.\n";