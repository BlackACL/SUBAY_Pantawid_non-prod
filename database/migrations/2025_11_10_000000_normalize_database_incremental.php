<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration normalizes the existing database by:
     * 1. Adding unique constraint to inventory.PROPERTY_NO (needed for FK)
     * 2. Creating the fets_items junction table (fixes 1NF violation)
     */
    public function up(): void
    {
        // ============================================
        // 1. Add unique constraint to inventory PROPERTY_NO
        // ============================================
        if (Schema::hasTable('inventory')) {
            $dbConnection = Schema::getConnection();
            $indexes = $dbConnection->select("SHOW INDEXES FROM inventory WHERE Column_name = 'PROPERTY_NO' AND Non_unique = 0");
            
            if (empty($indexes)) {
                echo "Adding unique constraint to inventory.PROPERTY_NO..." . PHP_EOL;
                Schema::table('inventory', function (Blueprint $table) {
                    $table->unique('PROPERTY_NO');
                });
            }
        }

        // ============================================
        // 2. Create FETS_ITEMS table (NEW - Fixes 1NF Violation)
        // ============================================
        if (!Schema::hasTable('fets_items')) {
            echo "Creating fets_items table..." . PHP_EOL;
            Schema::create('fets_items', function (Blueprint $table) {
                $table->id();
                
                // Relationships
                $table->unsignedBigInteger('fets_document_id')->index();
                $table->string('property_no', 30)->index(); // One property per row (1NF compliant)
                
                // Item Status in FETS (optional)
                $table->enum('item_status', [
                    'pending',
                    'verified',
                    'approved',
                    'rejected'
                ])->default('pending');
                
                $table->text('item_remarks')->nullable(); // Remarks specific to this item
                
                $table->timestamps();

                // Foreign keys
                $table->foreign('fets_document_id')->references('id')->on('fets_documents')->onDelete('cascade');
                // Reference PROPERTY_NO (uppercase) since existing table uses uppercase columns
                $table->foreign('property_no')->references('PROPERTY_NO')->on('inventory')->onDelete('cascade');
                
                // Unique constraint: Same property can't be in the same FETS document twice
                $table->unique(['fets_document_id', 'property_no'], 'unique_fets_property');
                
                // Composite index for queries
                $table->index(['fets_document_id', 'item_status']);
            });
        }

        echo "Normalization migration completed successfully!" . PHP_EOL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop fets_items table
        Schema::dropIfExists('fets_items');
        
        // Remove unique constraint from inventory (if added)
        if (Schema::hasTable('inventory')) {
            Schema::table('inventory', function (Blueprint $table) {
                $table->dropUnique(['PROPERTY_NO']);
            });
        }
    }
};
