<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add foreign key constraints to queues table with cascade delete
        Schema::table('queues', function (Blueprint $table) {
            // Add foreign key constraint with cascade delete if it doesn't exist
            // We'll attempt to add it and catch any exceptions if it already exists
            try {
                $table->dropForeign(['service_id']); // Remove any existing constraint
            } catch (\Exception $e) {
                // Foreign key doesn't exist, that's OK
            }

            // Add foreign key constraint with cascade delete
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // Drop foreign key constraint
            try {
                $table->dropForeign(['service_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, that's OK
            }
        });
    }
};
