<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Create Laravel application instance for testing
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$duplicates = DB::select('SELECT number, DATE(created_at) as date, COUNT(*) as count FROM queues GROUP BY number, DATE(created_at) HAVING COUNT(*) > 1');

echo "Finding duplicate queue numbers (any service)...\n";
foreach ($duplicates as $duplicate) {
    echo "Number: {$duplicate->number}, Date: {$duplicate->date}, Count: {$duplicate->count}\n";
}