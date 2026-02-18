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
        // Add indexes to queues table for better query performance
        Schema::table('queues', function (Blueprint $table) {
            // Add index on status for filtering by status
            $table->index(['status'], 'idx_queues_status');

            // Add composite index for service_id and status for common queries
            $table->index(['service_id', 'status'], 'idx_queues_service_status');

            // Add index on created_at for date-based queries
            $table->index(['created_at'], 'idx_queues_created_at');

            // Add composite index for service_id, created_at for common date-based queries
            $table->index(['service_id', 'created_at'], 'idx_queues_service_created_at');

            // Add composite index for service_id, status, and created_at for complex queries
            $table->index(['service_id', 'status', 'created_at'], 'idx_queues_service_status_created_at');

            // Add index on queue_date for date-based operations
            $table->index(['queue_date'], 'idx_queues_queue_date');

            // Add composite index for queue_date and status
            $table->index(['queue_date', 'status'], 'idx_queues_queue_date_status');
        });

        // Add indexes to services table if not already present
        Schema::table('services', function (Blueprint $table) {
            // Add index on created_at for ordering and filtering
            $table->index(['created_at'], 'idx_services_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropIndex('idx_queues_status');
            $table->dropIndex('idx_queues_service_status');
            $table->dropIndex('idx_queues_created_at');
            $table->dropIndex('idx_queues_service_created_at');
            $table->dropIndex('idx_queues_service_status_created_at');
            $table->dropIndex('idx_queues_queue_date');
            $table->dropIndex('idx_queues_queue_date_status');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_created_at');
        });
    }
};
