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
        Schema::table('fets_documents', function (Blueprint $table) {
            // Add composite index for status and transfer_movement queries
            $table->index(['status', 'transfer_movement'], 'idx_status_movement');
            // Add index for user_id queries
            $table->index('user_id', 'idx_user_id');
        });

        // Add indexes to inventory table for faster lookups
        Schema::table('inventory', function (Blueprint $table) {
            // Index for RECEIVER column (frequently queried)
            if (!Schema::hasColumn('inventory', 'receiver_idx')) {
                $table->index('RECEIVER', 'idx_receiver');
            }
            // Index for PROPERTY_NO if not already primary key
            if (!Schema::hasColumn('inventory', 'property_no_idx')) {
                $table->index('PROPERTY_NO', 'idx_property_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fets_documents', function (Blueprint $table) {
            $table->dropIndex('idx_status_movement');
            $table->dropIndex('idx_user_id');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex('idx_receiver');
            $table->dropIndex('idx_property_no');
        });
    }
};
