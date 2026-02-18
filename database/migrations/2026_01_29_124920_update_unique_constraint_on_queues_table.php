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
        // Simply drop the old constraint and add the new one
        // We'll use a more direct approach that's compatible with Laravel 11
        Schema::table('queues', function (Blueprint $table) {
            // Drop the existing unique constraint if it exists
            // The constraint was created with name 'queues_number_date_unique' in the previous migration
            try {
                $table->dropUnique('queues_number_date_unique');
            } catch (\Exception $e) {
                // If the constraint doesn't exist, continue
            }

            // Add the new unique constraint that includes service_id
            $table->unique(['number', 'service_id', 'queue_date'], 'queues_number_service_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // Drop the new constraint
            $table->dropUnique('queues_number_service_date_unique');

            // Add back the old constraint
            $table->unique(['number', 'queue_date']);
        });
    }
};
