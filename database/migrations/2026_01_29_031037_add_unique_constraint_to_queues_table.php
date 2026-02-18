<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a date column to store the date part of created_at for indexing purposes
        Schema::table('queues', function (Blueprint $table) {
            $table->date('queue_date')->nullable()->after('status'); // Add as nullable first
        });

        // Populate the queue_date column with existing data
        DB::statement('UPDATE queues SET queue_date = DATE(created_at)');

        // Add a unique constraint on the combination of number and queue_date
        Schema::table('queues', function (Blueprint $table) {
            $table->unique(['number', 'queue_date'], 'queues_number_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropUnique('queues_number_date_unique');
            $table->dropColumn('queue_date');
        });
    }
};
