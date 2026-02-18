<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Create Laravel application instance for testing
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing duplicate queue numbers...\n";

// Find all duplicate queue numbers
$duplicates = DB::select("
    SELECT number, DATE(created_at) as date, service_id, COUNT(*) as count 
    FROM queues 
    GROUP BY number, DATE(created_at), service_id 
    HAVING COUNT(*) > 1
");

foreach ($duplicates as $duplicate) {
    echo "Found duplicate: Number={$duplicate->number}, Date={$duplicate->date}, Service={$duplicate->service_id}, Count={$duplicate->count}\n";
    
    // Get all records with this duplicate number
    $records = DB::select("
        SELECT id, number, created_at, service_id 
        FROM queues 
        WHERE number = ? AND DATE(created_at) = ? AND service_id = ?
        ORDER BY created_at ASC, id ASC
    ", [$duplicate->number, $duplicate->date, $duplicate->service_id]);
    
    // Keep the first record and fix the rest
    $counter = 2; // Start from 2 since the first one is kept as #1
    $servicePrefix = substr($duplicate->number, 0, strpos($duplicate->number, '.'));
    $originalNumber = intval(substr($duplicate->number, strpos($duplicate->number, '.') + 1));
    
    for ($i = 1; $i < count($records); $i++) { // Skip the first record (index 0)
        $newNumber = $servicePrefix . '.' . str_pad($originalNumber + $i, 3, '0', STR_PAD_LEFT);
        
        echo "  Updating ID {$records[$i]->id}: {$records[$i]->number} -> {$newNumber}\n";
        
        DB::update("
            UPDATE queues 
            SET number = ? 
            WHERE id = ?
        ", [$newNumber, $records[$i]->id]);
    }
}

echo "Duplicate fixing complete!\n";