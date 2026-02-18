<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Create Laravel application instance for testing
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing ALL duplicate queue numbers...\n";

// Find ALL duplicate queue numbers across any service
$duplicates = DB::select("
    SELECT number, DATE(created_at) as date, COUNT(*) as count 
    FROM queues 
    GROUP BY number, DATE(created_at) 
    HAVING COUNT(*) > 1
");

foreach ($duplicates as $duplicate) {
    echo "Found duplicate across services: Number={$duplicate->number}, Date={$duplicate->date}, Count={$duplicate->count}\n";
    
    // Get all records with this duplicate number
    $records = DB::select("
        SELECT id, number, created_at, service_id 
        FROM queues 
        WHERE number = ? AND DATE(created_at) = ?
        ORDER BY created_at ASC, id ASC
    ", [$duplicate->number, $duplicate->date]);
    
    // Keep the first record and fix the rest
    for ($i = 1; $i < count($records); $i++) { // Skip the first record (index 0)
        // Get the service prefix for this specific record
        $service = DB::select('SELECT prefix FROM services WHERE id = ?', [$records[$i]->service_id])[0];
        $servicePrefix = $service->prefix;
        
        // Extract the original number and increment it based on position
        $originalNumber = intval(substr($duplicate->number, strpos($duplicate->number, '.') + 1));
        $newNumber = $servicePrefix . '.' . str_pad($originalNumber + $i, 3, '0', STR_PAD_LEFT);
        
        echo "  Updating ID {$records[$i]->id}: {$records[$i]->number} -> {$newNumber}\n";
        
        DB::update("
            UPDATE queues 
            SET number = ? 
            WHERE id = ?
        ", [$newNumber, $records[$i]->id]);
    }
}

echo "Complete duplicate fixing across all services!\n";